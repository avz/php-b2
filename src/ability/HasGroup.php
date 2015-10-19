<?php
namespace b2\ability;

trait HasGroup
{
	private $groups = [];

	public function groupBy($column, $direction = 'ASC')
	{
		$e = null;

		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new \b2\Exception('Direction must be ASC or DESC');
		}

		if (is_string($column)) {
			$e = new \b2\literal\Identifier($column);

		} elseif ($column instanceof \b2\Literal) {
			$e = $column;
		} else {
			throw new \b2\Exception('Only string or Literal allowed');
		}

		$this->groups[] = [$e, $direction];

		return $this;
	}

	protected function groupIsEmpty()
	{
		return !$this->groups;
	}

	private function groupToString(\b2\Quote $quote)
	{
		$list = [];
		foreach ($this->groups as $o) {
			list($expression, $direction) = $o;

			$list[] = $expression->toString($quote) . ($direction !== 'ASC' ? ' ' . $direction : '');
		}

		if (!$list) {
			return null;
		}

		return 'GROUP BY ' . implode(', ', $list);
	}

	protected function groupConcatSql(\b2\Quote $quote, $sql) {
		if (!$this->groupIsEmpty())
			$sql .= ' ' . $this->groupToString($quote);

		return $sql;
	}
}