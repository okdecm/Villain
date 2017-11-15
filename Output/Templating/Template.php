<?php

	namespace Villain\Output\Templating;

	class Template
	{
		private $_content;
		private $_data = array();

		private $_lexer;
		private $_parser;
		private $_evaluator;

		// NOTE: Could just use an interface- however this isn't nessecary for now
		// NOTE: This injection is useful as the Lexer has an $options property which determines the tags used
		public function __construct($content = "", Lexer $lexer = null, Parser $parser = null, Evaluator $evaluator = null)
		{
			$this->_content = $content;

			$this->SetLexer($lexer);
			$this->SetParser($parser);
			$this->SetEvaluator($evaluator);
		}

		public function __get($name)
		{
			if(isset($this->_data[$name]))
			{
				return $this->_data[$name];
			}

			return null;
		}

		public function __set($name, $value)
		{
			$this->_data[$name] = $value;
		}

		public function SetLexer(Lexer $lexer = null)
		{
			$this->_lexer = $lexer;

			if($this->_lexer == null)
			{
				$this->_lexer = new Lexer();
			}
		}

		public function SetParser(Parser $parser = null)
		{
			$this->_parser = $parser;

			if($this->_parser == null)
			{
				$this->_parser = new Parser();
			}
		}

		public function SetEvaluator(Evaluator $evaluator = null)
		{
			$this->_evaluator = $evaluator;

			if($this->_evaluator == null)
			{
				$this->_evaluator = new Evaluator();
			}
		}

		public function LoadFile($filePath = null)
		{
			// NOTE: Let the caller handle the exception
			//if(is_string($filePath) && file_exists($filePath) && is_file($filePath))
			//{
				$this->_content = file_get_contents($filePath);
			//}
		}

		public function AppendData($data)
		{
			$this->_data = array_merge(
				$this->_data,
				$data
			);
		}

		public function Render()
		{
			$tokens = $this->_lexer->Lex($this->_content);
			$nodes = $this->_parser->Parse($tokens);

			return $this->_evaluator->Evaluate($nodes, $this->_data);
		}
	}

?>