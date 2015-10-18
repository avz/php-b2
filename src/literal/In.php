<?php
namespace b2\literal;

class In extends \b2\Literal
{
	private $expression;
	private $cases = [];

	public function __construct(\b2\Literal $expression, array $cases = [])
	{
		$this->expression = $expression;
		$this->cases = $cases;
	}

	public function addCase(\b2\Literal $case)
	{
		$this->cases[] = $case;
	}

	public function toString(\b2\Quote $quote)
	{
		if (!$this->cases)
			throw new \b2\Exception('IN is empty');

		$call = new Call('IN', $this->cases);

		return $this->expression->toString($quote) . ' ' . $call->toString($quote);
	}

}
