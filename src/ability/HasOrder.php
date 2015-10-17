<?php
namespace d2\ability;

use d2\Exception;
use d2\literal\Identifier;
use d2\literal\Constant;
use d2\Literal;

trait HasOrder
{
	private $orders = [];

	public function orderBy($column, $direction = 'ASC')
	{
		$e = null;

		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new Exception('Direction must be ASC or DESC');
		}

		if (is_string($column)) {
			$e = new Identifier($column);
		} elseif ($column instanceof Literal) {
			$e = $column;
		} elseif ($column === null) {
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

	private function orderToString(\d2\Quote $quote)
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

	protected function orderConcatSql(\d2\Quote $quote, $sql) {
		if (!$this->orderIsEmpty())
			$sql .= ' ' . $this->orderToString($quote);

		return $sql;
	}
}
