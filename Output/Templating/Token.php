<?php

	namespace Villain\Output\Templating;

	class Token
	{
		public $Type;
		public $Data;
		public $LineNumber;

		public function __construct(int $type, string $data, int $lineNumber)
		{
			$this->Type = $type;
			$this->Data = $data;
			$this->LineNumber = $lineNumber;
		}
	}

?>