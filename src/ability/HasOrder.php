<?php
namespace b2\ability;

use b2\Exception;
use b2\literal\Identifier;
use b2\literal\Constant;
use b2\Literal;

trait HasOrder
{
	private $orders = [];

	public function orderBy($field, $direction = 'ASC')
	{
		$e = null;

		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new Exception('Direction must be ASC or DESC');
		}

		if (is_string($field)) {
			$e = new Identifier($field);
		} elseif ($field instanceof Literal) {
			$e = $field;
		} elseif ($field === null) {
			$e = new Constant(null);

			if ($direction !== 'ASC')
				throw new Exception('ORDER BY NULL DESC is not allowed');

		} else {
			throw new Exception('Only string, null or Literal allowed');
		}

		$this->orders[] = [$e, $direction];

		return $this;
	}

	protected function orderIsEmpty()
	{
		return !$this->orders;
	}

	private function orderToString(\b2\Quote $quote)
	{
		$list = [];
		foreach ($this->orders as $o) {
			list($expression, $direction) = $o;

			$list[] = $expression->toString($quote) . ($direction !== 'ASC' ? ' ' . $direction : '');
		}

		if (!$list) {
			return null;
		}

		return 'ORDER BY ' . implode(', ', $list);
	}

	protected function orderConcatSql(\b2\Quote $quote, $sql) {
		if (!$this->orderIsEmpty())
			$sql .= ' ' . $this->orderToString($quote);

		return $sql;
	}
}
