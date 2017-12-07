<?php

	namespace Villain\Output\HTML\Elements;

	use Villain\Output\HTML\Element;

	class ScriptElement extends Element
	{
		public $Source = "";
		public $Type = "";

		public $Charset = "";

		public $IsAsynchronous = false;
		public $IsDeferred = false;

		public function __construct($source = "", $type = "", $charset = "", $isAsynchronous = false, $isDeferred = false)
		{
			parent::__construct("script");
		}
	}

?>