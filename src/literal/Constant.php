<?php
namespace b2\literal;

class Constant extends \b2\Literal
{
	public $constant;

	public function __construct($constant)
	{
		if (is_object($constant))
			throw new \b2\Exception('Object is not allowed');

		$this->constant = $constant;
	}

	public function toString(\b2\Quote $quote)
	{
		return $quote->value($this->constant);
	}

}
