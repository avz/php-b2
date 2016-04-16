<?php
namespace b2\literal;

class AnyList extends \b2\Literal
{
	public $literals;

	public function __construct(array $constants)
	{
		$this->literals = $constants;
	}

	public function toString(\b2\Quote $quote)
	{
		return $quote->values($this->literals);
	}
}
