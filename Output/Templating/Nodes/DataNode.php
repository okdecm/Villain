<?php

	namespace Villain\Output\Templating\Nodes;

	class DataNode extends Node
	{
		public $IsNegative;
		public $Data;

		public function __construct(string $data, bool $isNegative = false)
		{
			$this->IsNegative = $isNegative;
			$this->Data = $data;
		}
	}

?>