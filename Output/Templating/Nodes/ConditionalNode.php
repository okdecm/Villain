<?php

	namespace Villain\Output\Templating\Nodes;

	class ConditionalNode
	{
		public $Logical;
		public $Left;
		public $Comparison;
		public $Right;

		public function __construct($logical, $left, $comparison, $right)
		{
			$this->Logical = $logical;
			$this->Left = $left;
			$this->Comparison = $comparison;
			$this->Right = $right;
		}
	}

?>