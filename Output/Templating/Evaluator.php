<?php

	namespace Villain\Output\Templating;

	use Villain\Data\StringEx;

	// Base
	use Villain\Output\Templating\Nodes\Node;

	// Generic
	use Villain\Output\Templating\Nodes\DataNode;
	use Villain\Output\Templating\Nodes\VariableNode;

	// ForEach
	use Villain\Output\Templating\Nodes\ForEachNode;
	use Villain\Output\Templating\Nodes\EndForEachNode;
	
	// If
	use Villain\Output\Templating\Nodes\IfNode;
	use Villain\Output\Templating\Nodes\ElseIfNode;
	use Villain\Output\Templating\Nodes\ElseNode;
	use Villain\Output\Templating\Nodes\EndIfNode;

	// Additional
	use Villain\Output\Templating\Nodes\ConditionalNode;

	class Evaluator
	{
		public static function Evaluate(array $nodes, array $context = array())
		{
			$data = "";

			$conditionMet = false;

			foreach($nodes as $node)
			{
				$nodeData = self::EvaluateSingular($node, $context, $conditionMet);

				if(StringEx::CanCastToString($nodeData))
				{
					$data .= (string)$nodeData;
				}
			}

			return $data;
		}

		public static function EvaluateSingular(Node $node, array $context = array(), bool &$conditionMet = false)
		{
			$data = "";

			switch(true)
			{
				case $node instanceof DataNode:
					if($node->IsNegative)
					{
						$data = !$node->Data;
					}
					else
					{
						$data = $node->Data;
					}
				break;

				case $node instanceof VariableNode:
					$expressionParts = explode(".", $node->Expression);
		
					$variable = $context;
		
					$scope = "";

					foreach($expressionParts as $expressionPart)
					{
						$key = null;
		
						$matches = array();
		
						if(preg_match("/(\w+)\[(\w+)\]/", $expressionPart, $matches))
						{
							$expressionPart = $matches[1];
							$key = $matches[2];
						}

						if(isset($variable[$expressionPart]))
						{
							$scope .= $expressionPart;

							$variable = $variable[$expressionPart];
		
							if($key != null)
							{
								if(isset($variable[$key]))
								{
									$scope .= "[" . $key . "]";

									$variable = $variable[$key];
								}
								else
								{
									throw new EvaluatorException($node, "Undefined key \"" . $key . "\" in \"" . $scope . "\"", EvaluatorException::CODE_UNDEFINED_VARIABLE_KEY);
								}
							}
						}
						else
						{
							$message = "Undefined variable \"" . $expressionPart . "\"";

							if(!empty($scope))
							{
								$message .= " in \"" . $scope . "\"";
							}

							throw new EvaluatorException($node, $message, EvaluatorException::CODE_UNDEFINED_VARIABLE);
						}
					}

					if(!empty($node->Modifier))
					{
						switch($node->Modifier)
						{
							case "uppercase":
								$variable = strtoupper($variable);
							break;
		
							case "lowercase":
								$variable = strtolower($variable);
							break;

							case "length":
							case "count":
								if(is_string($variable))
								{
									$variable = strlen($variable);
								}
								else
								{
									$variable = count($variable);
								}
							break;

							default:
								throw new EvaluatorException($node, "Unknown modifier \"" . $node->Modifier . "\" for \"" . $node->Expression . "\"", EvaluatorException::CODE_UNKNOWN_VARIABLE_MODIFIER);
							break;
						}
					}

					$data = $variable;
				break;

				case $node instanceof ForEachNode:
					$array = self::EvaluateSingular($node->Array, $context);

					foreach($array as $key => $value)
					{
						$data .= self::Evaluate(
							$node->Children,
							array_merge(
								$context,
								array(
									$node->Key => $key,
									$node->Value => $value
								)
							)
						);
					}
				break;

				case $node instanceof ElseIfNode:
				case $node instanceof IfNode:
					if($node instanceof IfNode)
					{
						$conditionMet = false;
					}

					if($conditionMet) break;

					$value = false;

					$node->Condition[0]->Logical = "||";

					foreach($node->Condition as $condition)
					{
						$nextValue = false;

						if(empty($condition->Right))
						{
							$nextValue = self::EvaluateSingular($condition->Left, $context);
						}
						else
						{
							$left = self::EvaluateSingular($condition->Left, $context);
							$right = self::EvaluateSingular($condition->Right, $context);

							switch($condition->Comparison)
							{
								case "==":
									$nextValue = ($left == $right);
								break;

								case "!=":
									$nextValue = ($left != $right);
								break;

								case "<":
									$nextValue = ($left < $right);
								break;

								case ">":
									$nextValue = ($left > $right);
								break;

								case "<=":
									$nextValue = ($left <= $right);
								break;

								case ">=":
									$nextValue = ($left >= $right);
								break;
							}
						}

						switch($condition->Logical)
						{
							case "&&":
								if(!$value) break;

								$value = ($value && $nextValue);
							break;

							case "||":
								if($value) break;

								$value = ($value || $nextValue);
							break;
						}
					}

					if($value)
					{
						$conditionMet = true;

						$data = self::Evaluate($node->Children, $context);
					}
				break;

				case $node instanceof ElseNode:
					if($conditionMet) break;

					$data = self::Evaluate($node->Children, $context);
				break;

				case $node instanceof EndIfNode:
					$conditionMet = false;
				break;
			}

			return $data;
		}
	}

?>