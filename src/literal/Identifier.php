<?php
namespace b2\literal;

class Identifier extends \b2\Literal
{
	public $identifier;

	public function __construct($identifier)
	{
		if (!is_string($identifier))
			throw new \b2\Exception('Identifier must be a string');

		$this->identifier = $identifier;
	}

	public function toString(\b2\Quote $quote)
	{
		return $quote->identifier($this->identifier);
	}

}
