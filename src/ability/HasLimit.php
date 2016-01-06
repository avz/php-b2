<?php
namespace b2\ability;

use b2\Exception;
use b2\literal\Identifier;

trait HasLimit
{
	private $limit = null;
	private $offset = null;

	/**
	 *
	 * @param int $limit
	 * @return $this
	 * @throws Exception
	 */
	public function limit($limit)
	{
		if (!is_int($limit) || $limit <= 0)
			throw new Exception('LIMIT must be positive int');

		$this->limit = $limit;

		return $this;
	}

	/**
	 *
	 * @param int $offset
	 * @return $this
	 * @throws Exception
	 */
	public function offset($offset)
	{
		if (!is_int($offset) || $offset <= 0)
			throw new Exception('OFFSET must be positive int');

		$this->offset = $offset;

		return $this;
	}

	protected function limitIsEmpty()
	{
		return $this->limit === null && $this->offset === null;
	}

	private function limitToString(\b2\Quote $quote)
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

	protected function limitConcatSql(\b2\Quote $quote, $sql) {
		if (!$this->limitIsEmpty())
			$sql .= ' ' . $this->limitToString($quote);

		return $sql;
	}
}
