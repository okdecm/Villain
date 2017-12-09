<?php

	namespace Villain\Output\Templating;

	use Villain\Output\Templating\Nodes\Node;

	class EvaluatorException extends \Exception
	{
		public $Node;

		const CODE_UNDEFINED_VARIABLE = 1;
		const CODE_UNDEFINED_VARIABLE_KEY = 2;
		const CODE_UNKNOWN_VARIABLE_MODIFIER = 3;

		public function __construct(Node $node, string $message, int $code = 0, \Exception $previous = null)
		{
			$this->Node = $node;

			parent::__construct($message, $code, $previous);
		}
	}

?>