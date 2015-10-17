<?php
namespace d2\literal;

class Constant extends \d2\Literal
{
	public $constant;

	public function __construct($constant)
	{
		if (is_object($constant))
			throw new \d2\Exception('Object is not allowed');

		$this->constant = $constant;
	}

	public function toString(\d2\Quote $quote)
	{
		return $quote->value($this->constant);
	}

}
