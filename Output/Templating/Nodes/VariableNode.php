<?php

	namespace Villain\Output\Templating\Nodes;

	class VariableNode extends Node
	{
		public $IsNegative; 
		public $Expression;
		public $Modifier;

		public function __construct(string $expression, ?string $modifier = null, bool $isNegative = false)
		{
			$this->IsNegative = $isNegative;
			$this->Expression = $expression;
			$this->Modifier = $modifier;
		}
	}

?>