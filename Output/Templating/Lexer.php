<?php

	namespace Villain\Output\Templating;

	class Lexer
	{
		public static function Lex($data, array $options = array())
		{
			$options = array_merge(
				array(
					"tags" => array(
						"variable" => array(
							"{{",
							"}}"
						),
						"expression" => array(
							"{%",
							"%}"
						)
					)
				),
				$options
			);

			$tokens = array();
			$currentLineNumber = 1;

			//$matches = array();
			preg_match_all("/" . preg_quote($options["tags"]["variable"][0], "/") . "|" . preg_quote($options["tags"]["expression"][0], "/") . "/s", $data, $matches, PREG_OFFSET_CAPTURE);

			$matches = $matches[0];

			if(!empty($matches))
			{
				$nextMatch = array_shift($matches);

				$position = 0;
				$length = strlen($data);

				while($position < $length)
				{
					$tokenType = TokenType::DATA;

					if($nextMatch != null && $position == $nextMatch[1])
					{
						switch($nextMatch[0])
						{
							case $options["tags"]["variable"][0]:
								$tokenType = TokenType::VARIABLE;

								$endingTag = $options["tags"]["variable"][1];
							break;

							case $options["tags"]["expression"][0]:
								$tokenType = TokenType::EXPRESSION;

								$endingTag = $options["tags"]["expression"][1];
							break;
						}

						$tokenLength = (strpos($data, $endingTag, $position) - $position) + strlen($endingTag);

						$nextMatch = array_shift($matches);
					}
					else
					{
						if($nextMatch != null)
						{
							$tokenLength = ($nextMatch[1] - $position);
						}
						else
						{
							$tokenLength = ($length - $position);
						}
					}

					$tokenData = substr($data, $position, $tokenLength);

					switch($tokenType)
					{
						case TokenType::VARIABLE:
							$tokenData = trim(substr(substr($tokenData, strlen($options["tags"]["variable"][0])), 0, (strlen($options["tags"]["variable"][1]) * -1)));
						break;

						case TokenType::EXPRESSION:
							$tokenData = trim(substr(substr($tokenData, strlen($options["tags"]["expression"][0])), 0, (strlen($options["tags"]["expression"][1]) * -1)));
						break;
					}

					$tokens[] = new Token($tokenType, $tokenData, $currentLineNumber);
					
					$currentLineNumber += substr_count($tokenData, "\n");

					$position += $tokenLength;
				}
			}
			else
			{
				$tokens[] = new Token(TokenType::DATA, $data, $currentLineNumber);
			}

			return $tokens;
		}
	}

?>