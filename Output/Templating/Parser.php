<?php

	namespace Villain\Output\Templating;

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

		private static function GetNegativePattern($name = "Negative")
		{
			return "(?<" . $name . ">!*)";
		}

		private static function GetBooleanPattern($name = "Boolean")
		{

			return "(?i<" . $name . ">true|false)";
		}

		private static function GetNumericalPattern($name = "Digit")
		{
			return "(?<" . $name . ">[\d]+(?:\.[\d]+)*)";
		}

		private static function GetStringPattern($name = "String")
		{
			return "\"(?<" . $name . ">.*)\"";
		}

		private static function GetVariablePattern($expression = "Expression", $modifier = "Modifier")
		{
			return "(?<" . $expression . ">[\w]+(?:\[[\w]+\])?(?:\.+\w+(?:\[[\w]+\])?)*)(?:[\s]*\|[\s]*(?<" . $modifier . ">[\w]+))?";
		}

		// NOTE: Ugly args, but it'll do
		private static function GetConditionalPattern($logical = "Logical", $leftNegative = "LeftNegative", $leftNumeric = "LeftNumeric", $leftString = "LeftString", $leftExpression = "LeftExpression", $leftModifier = "LeftModifier", $comparison = "Comparison", $rightNegative = "RightNegative", $rightNumeric = "RightNumeric", $rightString = "RightString", $rightExpression = "RightExpression", $rightModifier = "RightModifier")
		{
			return "[\s]*(?<" . $logical . ">&&|\|\|)?[\s]*" . self::GetNegativePattern($leftNegative) . "(?:" . self::GetNumericalPattern($leftNumeric) . "|" . self::GetStringPattern($leftString) . "|" . self::GetVariablePattern($leftExpression, $leftModifier) . ")(?:[\s]*(?<" . $comparison . ">==|!=|<|>|<=|>=)[\s]*" . self::GetNegativePattern($rightNegative) . "(?:" . self::GetNumericalPattern($rightNumeric) . "|" . self::GetStringPattern($rightString) . "|" . self::GetVariablePattern($rightExpression, $rightModifier) . "))?";
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

		public static function Parse(&$tokens, $parentExpressionType = null)
		{
			$nodes = array();

			$expressionPatterns = self::GetExpressionPatterns();

			$previousNode = null;

			//die("<pre>" . print_r($tokens, true));

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
							$modifier = @$matches["Modifier"];
							
							$isNegative = !empty(@$matches["Negative"]);

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
								$value = @$matches["Value"];
								$expression = $matches["Expression"];
								$modifier = @$matches["Modifier"];

								$array = new VariableNode($expression, $modifier);

								$children = self::Parse($tokens, $expressionType);

								if(empty($value))
								{
									$value = $key;
									$key = null;
								}

								$node = new ForEachNode($key, $value, $array, $children);
							break;

							case ExpressionType::ET_ELSE_IF:
							case ExpressionType::ET_IF:
								if($expressionType == ExpressionType::ET_ELSE_IF)
								{
									if($parentExpressionType == ExpressionType::ET_IF || $parentExpressionType == ExpressionType::ET_ELSE_IF)
									{
										$state = (ParserState::END_OF_BRANCH | ParserState::PREPEND_TOKEN);

										break;
									}
									else if(!($previousNode instanceof IfNode || $previousNode instanceof ElseIfNode))
									{
										throw new ParserException($token, "Unexpected token \"" . $token->Data . "\"", ParserException::CODE_UNEXPECTED_TOKEN);
									}
								}

								$expression = $matches["Expression"];

								$condition = self::Conditional($expression, $token);

								$children = self::Parse($tokens, $expressionType);

								if($expressionType == ExpressionType::ET_ELSE_IF)
								{
									$node = new ElseIfNode($condition, $children);
								}
								else
								{
									$node = new IfNode($condition, $children);
								}
							break;

							case ExpressionType::ET_ELSE:
								if($parentExpressionType == ExpressionType::ET_IF || $parentExpressionType == ExpressionType::ET_ELSE_IF)
								{
									$state = (ParserState::END_OF_BRANCH | ParserState::PREPEND_TOKEN);

									break;
								}
								else if(!($previousNode instanceof IfNode || $previousNode instanceof ElseIfNode))
								{
									throw new ParserException($token, "Unexpected token \"" . $token->Data . "\" on line " . $token->LineNumber, ParserException::CODE_UNEXPECTED_TOKEN);
								}

								$children = self::Parse($tokens, $expressionType);

								$node = new ElseNode($children);
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

		private static function Conditional($expression, $token = null)
		{
			$nodes = array();

			$pattern = self::GetConditionalPattern();

			if(preg_match("/^" . $pattern . "$/", $expression))
			{
				$matches = array();
				preg_match_all("/" . $pattern . "/", $expression, $matches, PREG_SET_ORDER);
				
				foreach($matches as $match)
				{
					$logical = @$match["Logical"];
					$isLeftNegative = !empty(@$matches["LeftNegative"]);
					$left = @$match["LeftNumeric"];
					$comparison = @$match["Comparison"];
					$isRightNegative = !empty(@$matches["RightNegative"]);
					$right = @$match["RightNumeric"];

					if($left == null)
					{
						$left = @$match["LeftString"];

						if($left == null)
						{
							$left = new VariableNode($match["LeftExpression"], @$match["LeftModifier"], $isLeftNegative);
						}
					}

					if(!($left instanceof VariableNode))
					{
						$left = new DataNode($left, $isLeftNegative);
					}

					if($right == null)
					{
						$right = @$match["RightString"];

						if($right == null)
						{
							$right = new VariableNode($match["RightExpression"], @$match["RightModifier"], $isRightNegative);
						}
					}

					if(!($right instanceof VariableNode))
					{
						$right = new DataNode($right, $isRightNegative);
					}

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
	}

?>