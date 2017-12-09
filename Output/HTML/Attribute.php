<?php

	namespace Villain\Output\HTML;

	use Villain\Output\Templating\Template;

	class Attribute extends Template
	{
		public $Key;
		public $Value;

		public function __construct(string $key, string $value = "")
		{
			$this->Key = $key;
			$this->Value = $value;

			parent::__construct();

			parent::LoadFile(__DIR__ . "/Templates/Attribute.tpl");
		}

		public function Render()
		{
			parent::AppendData(
				array(
					"key" => $this->Key,
					"value" => $this->Value
				)
			);

			return parent::Render();
		}
	}

?>