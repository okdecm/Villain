<?php

	namespace Villain\Output\Templating\Nodes;

	class ElseNode extends Node
	{
		public $Children;

		public function __construct(array $children)
		{
			$this->Children = $children;
		}
	}

?>