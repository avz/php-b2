<?php
namespace d2\literal;

class Call extends \d2\Literal
{
	public $functionName;
	public $args = [];

	public function __construct($functionName, array $args = [])
	{
		$this->functionName = $functionName;
		$this->args = $args;
	}

	public function toString(\d2\Quote $quote)
	{
		$args = [];
		foreach ($this->args as $arg) {
			if ($arg instanceof \d2\Literal)
				$args[] = $arg->toString($quote);
			else
				$args[] = $quote->value($arg);
		}

		return $this->functionName . '(' . implode(', ', $args) . ')';
	}

}
