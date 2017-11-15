<?php

	namespace Villain\Output\Templating\Nodes;

	class DataNode
	{
		public $IsNegative;
		public $Data;

		public function __construct($data, $isNegative = false)
		{
			$this->IsNegative = $isNegative;
			$this->Data = $data;
		}
	}

?>