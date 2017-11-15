<?php

	namespace Villain\Output\Templating\Nodes;

	class ElseNode
	{
		public $Children;

		public function __construct($children)
		{
			$this->Children = $children;
		}
	}

?>