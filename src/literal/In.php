<?php
namespace d2\literal;

class In extends \d2\Literal
{
	private $expression;
	private $cases = [];

	public function __construct(\d2\Literal $expression, array $cases = [])
	{
		$this->expression = $expression;
		$this->cases = $cases;
	}

	public function addCase(\d2\Literal $case)
	{
		$this->cases[] = $case;
	}

	public function toString(\d2\Quote $quote)
	{
		if (!$this->cases)
			throw new \d2\Exception('IN is empty');

		$call = new Call('IN', $this->cases);

		return $this->expression->toString($quote) . ' ' . $call->toString($quote);
	}

}
