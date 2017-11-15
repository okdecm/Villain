<?php

	namespace Villain\Output\Templating\Nodes;

	class IfNode
	{
		public $Condition;

		public $Children;

		public function __construct($condition, $children = array())
		{
			$this->Condition = $condition;

			$this->Children = $children;
		}
	}

?>