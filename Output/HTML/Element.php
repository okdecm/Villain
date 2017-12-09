<?php

	namespace Villain\Output\HTML;

	use Villain\Output\Templating\Template;

	class Element extends Template
	{
		private $_name;

		public $Content = "";
		public $IsSelfClosing = false;

		protected $_attributes = array();

		public function __construct(string $name, string $content = "", bool $isSelfClosing = false)
		{
			$this->_name = $name;

			$this->Content = $content;
			$this->IsSelfClosing = $isSelfClosing;

			parent::__construct();

			parent::LoadFile(__DIR__ . "/Templates/Element.tpl");
		}

		public function AddAttributes(array $attributes)
		{
			foreach($attributes as $attribute)
			{
				$this->AddAttribute($attribute);
			}
		}

		public function AddAttribute(Attribute $attribute)
		{
			$this->_attributes[] = $attribute;
		}

		protected function GenerateAttributes()
		{
			$attributes = "";

			foreach($this->_attributes as $attribute)
			{
				$attributes .= $attribute->Render();
			}

			return $attributes;
		}

		public function Render()
		{
			parent::AppendData(
				array(
					"name" => $this->_name,
					"attributes" => $this->GenerateAttributes(),
					"is_self_closing" => $this->IsSelfClosing,
					"content" => $this->Content
				)
			);

			return parent::Render();
		}
	}

?>