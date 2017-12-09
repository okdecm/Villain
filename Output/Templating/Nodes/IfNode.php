<?php

	namespace Villain\Output\Templating\Nodes;

	class IfNode extends Node
	{
		public $Condition;

		public $Children;

		public function __construct(array $condition, array $children = array())
		{
			$this->Condition = $condition;

			$this->Children = $children;
		}
	}

?>