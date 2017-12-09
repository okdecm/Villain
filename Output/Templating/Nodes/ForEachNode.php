<?php

	namespace Villain\Output\Templating\Nodes;

	class ForEachNode extends Node
	{
		public $Key;
		public $Value;
		public $Array;

		public $Children;

		public function __construct(?string $key, string $value, VariableNode $array, array $children = array())
		{
			$this->Key = $key;
			$this->Value = $value;
			$this->Array = $array;

			$this->Children = $children;
		}
	}

?>