<?php

	namespace Villain\Output\Templating\Nodes;

	class ConditionalNode extends Node
	{
		public $Logical;
		public $Left;
		public $Comparison;
		public $Right;

		public function __construct(?string $logical, VariableNode $left, ?string $comparison, ?VariableNode $right)
		{
			$this->Logical = $logical;
			$this->Left = $left;
			$this->Comparison = $comparison;
			$this->Right = $right;
		}
	}

?>