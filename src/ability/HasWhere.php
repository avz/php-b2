<?php
namespace d2\ability;

trait HasWhere
{
	/**
	 *
	 * @var Where
	 */
	private $where;

	public function where($fieldNameOrPrepared, $valueOrBinds = null/* ... */)
	{
		$where = clone $this->where;

		$expressions = WhereUpdateCommon::extractExpressionsFromArgs(func_get_args());

		foreach ($expressions as $expression) {
			$where->addAnd($expression);
		}

		$this->where = $where;

		return $this;
	}

	protected function whereIsEmpty()
	{
		return $this->where->isEmpty();
	}

	private function whereToString(\d2\Quote $quote)
	{
		return $this->where->toString($quote);
	}

	protected function whereConcatSql(\d2\Quote $quote, $sql) {
		if (!$this->whereIsEmpty())
			$sql .= ' WHERE ' . $this->whereToString($quote);

		return $sql;
	}
}
