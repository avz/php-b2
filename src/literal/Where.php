<?php
namespace d2\literal;

use d2\Literal;
use d2\Exception;

class Where extends Literal
{
	private $expression = null;

	public function __construct(Literal $expression = null)
	{
		$this->expression = $expression;
	}

	public function addAnd(Literal $expression)
	{
		return $this->addToEnd('AND', $expression);
	}

	public function addOr(Literal $expression)
	{
		return $this->addToEnd('OR', $expression);
	}

	private function addToEnd($operator, Literal $expression)
	{
		if ($this->expression) {
			$this->expression = new BiOperation($this->expression, $operator, $expression);
		} else {
			$this->expression = $expression;
		}
	}

	public function isEmpty()
	{
		return !$this->expression;
	}

	public function toString(\d2\Quote $quote)
	{
		if (!$this->expression)
			throw new Exception('Empty WHERE');

		return $this->expression->toString($quote);
	}

	public function getExpression() {
		return $this->expression;
	}
}
