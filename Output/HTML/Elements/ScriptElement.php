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

		public function __construct(string $source = "", string $type = "", string $charset = "", bool $isAsynchronous = false, bool $isDeferred = false)
		{
			parent::__construct("script");
		}
	}

?>