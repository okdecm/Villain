<?php

	namespace Villain\Output\HTML;

	use Villain\Output\Templating\Template;

	class Container extends Template
	{
		public $Language = "en";
		public $Title = "Untitled";
		public $BodyClass = "";

		public $Body = "";

		protected $_elements = array();

		public function __construct()
		{
			parent::__construct();

			parent::LoadFile(__DIR__ . "/Templates/Container.tpl");
		}

		public function AddElements(array $elements)
		{
			foreach($elements as $element)
			{
				$this->AddElement($element);
			}
		}

		public function AddElement(Element $element)
		{
			$this->_elements[] = $element;
		}

		protected function GenerateHead()
		{
			$head = "";

			foreach($this->_elements as $element)
			{
				$head .= $element->Render();
			}

			return $head;
		}

		public function Render()
		{
			parent::AppendData(
				array(
					"language" => $this->Language,
					"title" => $this->Title,
					"body_class" => $this->BodyClass,
					"head" => $this->GenerateHead(),
					"body" => $this->Body
				)
			);

			return parent::Render();
		}
	}

?>