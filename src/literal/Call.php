<?php
namespace b2\literal;

class Call extends \b2\Literal
{
	public $functionName;

	/**
	 *
	 * @var \b2\Literal[]
	 */
	public $args = [];

	/**
	 *
	 * @param type $functionName
	 * @param \b2\Literal[] $args
	 */
	public function __construct($functionName, array $args = [])
	{
		if (!is_string($functionName)) {
			throw new \b2\Exception('Function name must be a string');
		}

		$this->functionName = $functionName;

		foreach ($args as $arg) {
			if (!($arg instanceof \b2\Literal)) {
				throw new \b2\Exception('Literals expected in arguments');
			}
		}

		$this->args = $args;
	}

	public function toString(\b2\Quote $quote)
	{
		$args = [];
		foreach ($this->args as $arg) {
			$args[] = $arg->toString($quote);
		}

		return $this->functionName . '(' . implode(', ', $args) . ')';
	}

}
