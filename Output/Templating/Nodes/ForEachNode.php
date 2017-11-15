<?php

	namespace Villain\Output\Templating\Nodes;

	class ForEachNode
	{
		public $Key;
		public $Value;
		public $Array;

		public $Children;

		public function __construct($key, $value, $array, $children)
		{
			$this->Key = $key;
			$this->Value = $value;
			$this->Array = $array;

			$this->Children = $children;
		}
	}

?>