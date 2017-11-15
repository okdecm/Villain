<?php

	namespace Villain\Output\Templating\Nodes;

	class VariableNode
	{
		public $IsNegative; 
		public $Expression;
		public $Modifier;

		public function __construct($expression, $modifier = null, $isNegative = false)
		{
			$this->IsNegative = $isNegative;
			$this->Expression = $expression;
			$this->Modifier = $modifier;
		}
	}

?>