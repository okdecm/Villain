<?php

	namespace Villain\Output\Templating;

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

	class Parser
	{
		// NOTE: These pattern functions are quite ugly, but ah well

		private static function GetNegativePattern(string $name = "Negative")
		{
			return "(?<" . $name . ">!*)";
		}

		private static function GetBooleanPattern(string $name = "Boolean")
		{

			return "(?<" . $name . ">true|false)";
		}

		private static function GetNumericalPattern(string $name = "Digit")
		{
			return "(?<" . $name . ">[\d]+(?:\.[\d]+)*)";
		}

		private static function GetStringPattern(string $name = "String")
		{
			return "\"(?<" . $name . ">.*)\"";
		}

		private static function GetVariablePattern(string $expression = "Expression", string $modifier = "Modifier")
		{
			return "(?<" . $expression . ">[\w]+(?:\[[\w]+\])?(?:\.+\w+(?:\[[\w]+\])?)*)(?:[\s]*\|[\s]*(?<" . $modifier . ">[\w]+))?";
		}

		// NOTE: Ugly args, but it'll do
		private static function GetConditionalPattern(array $options = array())
		{
			$options = array_merge(
				array(
					"Logical" => "Logical",
					"LeftNegative" => "LeftNegative",
					"LeftBoolean" => "LeftBoolean",
					"LeftNumeric" => "LeftNumeric",
					"LeftString" => "LeftString",
					"LeftExpression" => "LeftExpression",
					"LeftModifier" => "LeftModifier",
					"Comparison" => "Comparison",
					"RightNegative" => "RightNegative",
					"RightBoolean" => "RightBoolean",
					"RightNumeric" => "RightNumeric",
					"RightString" => "RightString",
					"RightExpression" => "RightExpression",
					"RightModifier" => "RightModifier"
				),
				$options
			);

			$leftPattern = self::GetValuePattern(
				array(
					"Negative" => $options["LeftNegative"],
					"Boolean" => $options["LeftBoolean"],
					"Numeric" => $options["LeftNumeric"],
					"String" => $options["LeftString"],
					"Expression" => $options["LeftExpression"],
					"Modifier" => $options["LeftModifier"]
				)
			);

			$rightPattern = self::GetValuePattern(
				array(
					"Negative" => $options["RightNegative"],
					"Boolean" => $options["RightBoolean"],
					"Numeric" => $options["RightNumeric"],
					"String" => $options["RightString"],
					"Expression" => $options["RightExpression"],
					"Modifier" => $options["RightModifier"]
				)
			);

			//die(var_dump($leftPattern));

			//die(var_dump("[\s]*(?<" . $options["Logical"] . ">&&|\|\|)?[\s]*" . $leftPattern));

			return "[\s]*(?<" . $options["Logical"] . ">&&|\|\|)?[\s]*" . $leftPattern . "(?:[\s]*(?<" . $options["Comparison"] . ">==|!=|<|>|<=|>=)[\s]*" . $rightPattern . ")?";
		}

		private static function GetValuePattern(array $options = array())
		{
			$options = array_merge(
				array(
					"Negative" => "Negative",
					"Boolean" => "Boolean",
					"Numeric" => "Numeric",
					"String" => "String",
					"Expression" => "Expression",
					"Modifier" => "Modifier"
				),
				$options
			);

			return "(?:" . self::GetNegativePattern($options["Negative"]) . "(?:" . self::GetBooleanPattern($options["Boolean"]) . "|" . self::GetNumericalPattern($options["Numeric"]) . "|" . self::GetStringPattern($options["String"]) . "|" . self::GetVariablePattern($options["Expression"], $options["Modifier"]) . "))";
		}

		private static function GetExpressionPatterns()
		{
			return array(
				ExpressionType::ET_FOR_EACH => "foreach[\s]+(?<Key>[\w]+)(?:[\s]*,[\s]*(?<Value>[\w]+))?[\s]+in[\s]+" . self::GetVariablePattern(),
				ExpressionType::ET_END_FOR_EACH => "endforeach",
				ExpressionType::ET_IF => "if[\s]+(?<Expression>.*)",
				ExpressionType::ET_ELSE_IF => "else[\s]+if[\s]+(?<Expression>.*)",
				ExpressionType::ET_ELSE => "else",
				ExpressionType::ET_END_IF => "endif"
			);
		}

		public static function Parse(array &$tokens, ?int $parentExpressionType = null)
		{
			$nodes = array();

			$expressionPatterns = self::GetExpressionPatterns();

			$previousNode = null;

			while(!empty($tokens))
			{
				$token = array_shift($tokens);

				$node = null;
				$state = ParserState::NORMAL;

				switch($token->Type)
				{
					case TokenType::DATA:
						$node = new DataNode($token->Data);
					break;

					case TokenType::VARIABLE:
						$matches = array();

						if(preg_match("/^" . self::GetNegativePattern() . self::GetVariablePattern() . "$/", $token->Data, $matches))
						{
							$expression = $matches["Expression"];
							$modifier = null;
							
							$isNegative = (isset($matches["Negative"]) && !empty($matches["Negative"]));

							if(isset($matches["Modifier"]) && !empty($matches["Modifier"]))
							{
								$modifier = $matches["Modifier"];
							}

							$node = new VariableNode($expression, $modifier, $isNegative);
						}
						else
						{
							throw new ParserException($token, "Invalid syntax \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_INVALID_SYNTAX);
						}
					break;

					case TokenType::EXPRESSION:
						$expressionType = ExpressionType::ET_UNKNOWN;

						$matches = array();

						foreach($expressionPatterns as $type => $pattern)
						{
							if(preg_match("/^" . $pattern . "$/", $token->Data, $matches))
							{
								$expressionType = $type;

								break;
							}
						}

						switch($expressionType)
						{
							case ExpressionType::ET_FOR_EACH:
								$key = $matches["Key"];
								$value = null;
								$expression = $matches["Expression"];
								$modifier = null;

								if(isset($matches["Value"]) && !empty($matches["Value"]))
								{
									$value = $matches["Value"];
								}

								if(isset($matches["Modifier"]) && !empty($matches["Modifier"]))
								{
									$modifier = $matches["Modifier"];
								}

								$array = new VariableNode($expression, $modifier);

								$children = self::Parse($tokens, $expressionType);

								if(empty($value))
								{
									$value = $key;
									$key = null;
								}

								$node = new ForEachNode($key, $value, $array, $children);
							break;

							case ExpressionType::ET_ELSE:
							case ExpressionType::ET_ELSE_IF:
							case ExpressionType::ET_IF:
								if($expressionType == ExpressionType::ET_ELSE_IF || $expressionType == ExpressionType::ET_ELSE)
								{
									if($parentExpressionType == ExpressionType::ET_IF || $parentExpressionType == ExpressionType::ET_ELSE_IF)
									{
										$state = (ParserState::END_OF_BRANCH | ParserState::PREPEND_TOKEN);

										break;
									}
									else if(!($previousNode instanceof IfNode || $previousNode instanceof ElseIfNode))
									{
										throw new ParserException($token, "Unexpected token \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_UNEXPECTED_TOKEN);
									}
								}

								$children = self::Parse($tokens, $expressionType);

								if($expressionType == ExpressionType::ET_ELSE)
								{
									$node = new ElseNode($children);
								}
								else
								{
									$expression = $matches["Expression"];
	
									$condition = self::Conditional($expression, $token);
	
									if($expressionType == ExpressionType::ET_ELSE_IF)
									{
										$node = new ElseIfNode($condition, $children);
									}
									else
									{
										$node = new IfNode($condition, $children);
									}
								}
							break;

							case ExpressionType::ET_END_FOR_EACH:
								switch($parentExpressionType)
								{
									case ExpressionType::ET_FOR_EACH:
										$state = ParserState::END_OF_BRANCH;

										$node = new EndForEachNode();
									break;

									default:
										throw new ParserException($token, "Unexpected token \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_UNEXPECTED_TOKEN);
									break;
								}
							break;

							case ExpressionType::ET_END_IF:
								switch($parentExpressionType)
								{
									case ExpressionType::ET_ELSE:
									case ExpressionType::ET_ELSE_IF:
									case ExpressionType::ET_IF:
										$state = ParserState::END_OF_BRANCH;

										$node = new EndIfNode();
									break;

									default:
										throw new ParserException($token, "Unexpected token \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_UNEXPECTED_TOKEN);
									break;
								}
							break;

							default:
								throw new ParserException($token, "Unknown expression \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_UNKNOWN_EXPRESSION);
							break;
						}
					break;
				}

				if($node != null)
				{
					$nodes[] = $node;
				}

				$previousNode = $node;

				if($state & ParserState::PREPEND_TOKEN)
				{
					array_unshift($tokens, $token);
				}

				if($state & ParserState::END_OF_BRANCH)
				{
					break;
				}
			}

			return $nodes;
		}

		private static function Conditional(string $expression, ?Token $token = null)
		{
			$nodes = array();

			$pattern = self::GetConditionalPattern();

			if(preg_match("/^" . $pattern . "$/", $expression))
			{
				$matches = array();
				preg_match_all("/" . $pattern . "/", $expression, $matches, PREG_SET_ORDER);
				
				foreach($matches as $match)
				{
					$logical = null;
					$comparison = null;

					if(isset($match["Logical"]) && !empty($match["Logical"]))
					{
						$logical = $match["Logical"];
					}

					if(isset($match["Comparison"]) && !empty($match["Comparison"]))
					{
						$comparison = $match["Comparison"];
					}
					
					$left = self::Value(
						$match,
						array(
							"Negative" => "LeftNegative",
							"Boolean" => "LeftBoolean",
							"Numeric" => "LeftNumeric",
							"String" => "LeftString",
							"Expression" => "LeftExpression",
							"Modifier" => "LeftModifier"
						)
					);

					$right = self::Value(
						$match,
						array(
							"Negative" => "RightNegative",
							"Boolean" => "RightBoolean",
							"Numeric" => "RightNumeric",
							"String" => "RightString",
							"Expression" => "RightExpression",
							"Modifier" => "RightModifier"
						)
					);

					$nodes[] = new ConditionalNode($logical, $left, $comparison, $right);
				}
			}
			else
			{
				$message = "Invalid syntax \"" . $expression . "\"";

				if($token != null)
				{
					$message .= " on line " . $token->LineNumber;
				}

				throw new ParserException($token, $message, ParserException::CODE_INVALID_SYNTAX);
			}

			return $nodes;
		}

		// NOTE: Will probably clean this up at some point, probably remove it due to being a very specific function
		private static function Value(array $matches, array $options = array())
		{
			$options = array_merge(
				array(
					"Negative" => "Negative",
					"Boolean" => "Boolean",
					"Numeric" => "Numeric",
					"String" => "String",
					"Expression" => "Expression",
					"Modifier" => "Modifier"
				),
				$options
			);

			$isNegative = (isset($matches[$options["Negative"]]) && !empty($matches[$options["Negative"]]));

			if(isset($matches[$options["Boolean"]]) && !empty($matches[$options["Boolean"]]))
			{
				return new DataNode(filter_var($matches[$options["Boolean"]], FILTER_VALIDATE_BOOLEAN), $isNegative);
			}

			if(isset($matches[$options["Numeric"]]) && !empty($matches[$options["Numeric"]]))
			{
				return new DataNode((double)$matches[$options["Numeric"]], $isNegative);
			}

			if(isset($matches[$options["String"]]) && !empty($matches[$options["String"]]))
			{
				return new DataNode((string)$matches[$options["String"]], $isNegative);
			}

			if(isset($matches[$options["Expression"]]) && !empty($matches[$options["Expression"]]))
			{
				$modifier = null;

				if(isset($matches[$options["Modifier"]]) && !empty($matches[$options["Modifier"]]))
				{
					$modifier = $matches[$options["Modifier"]];
				}

				return new VariableNode($matches[$options["Expression"]], $modifier, $isNegative);
			}

			return null;
		}
	}

?>