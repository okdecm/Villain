<?php

	namespace Villain\Output\Templating;

	class ParserException extends \Exception
	{
		public $Token;

		const CODE_INVALID_SYNTAX = 1;
		const CODE_UNKNOWN_EXPRESSION = 2;
		const CODE_UNEXPECTED_TOKEN = 3;

		public function __construct(Token $token, string $message, int $code = 0, \Exception $previous = null)
		{
			$this->Token = $token;

			parent::__construct($message, $code, $previous);
		}
	}

?>