<?php
namespace d2\literal;

class Identifier extends \d2\Literal
{
	public $identifier;

	public function __construct($identifier)
	{
		if (!is_string($identifier))
			throw new \d2\Exception('Identifier must be a string');

		$this->identifier = $identifier;
	}

	public function toString(\d2\Quote $quote)
	{
		return $quote->identifier($this->identifier);
	}

}
