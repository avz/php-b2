<?php
namespace d2\ability;

use d2\Exception;
use d2\literal\Identifier;

trait HasLimit
{
	private $limit = null;
	private $offset = null;

	public function limit($limit)
	{
		if (!is_int($limit) || $limit <= 0)
			throw new Exception('LIMIT must be positive int');

		$this->limit = $limit;
	}

	public function offset($offset)
	{
		if (!is_int($offset) || $offset <= 0)
			throw new Exception('OFFSET must be positive int');

		$this->offset = $offset;
	}

	protected function limitIsEmpty()
	{
		return $this->limit === null && $this->offset === null;
	}

	private function limitToString(\d2\Quote $quote)
	{
		if ($this->offset !== null && $this->limit === null)
			throw new Exception('OFFSET without LIMIT');

		$sql = null;

		if ($this->limit === null)
			return $sql;

		if ($this->offset !== null) {
			$sql = sprintf("LIMIT %u OFFSET %u", $this->limit, $this->offset);
		} else {
			$sql = sprintf("LIMIT %u", $this->limit);
		}

		return $sql;
	}

	protected function limitConcatSql(\d2\Quote $quote, $sql) {
		if (!$this->limitIsEmpty())
			$sql .= ' ' . $this->limitToString($quote);

		return $sql;
	}
}
