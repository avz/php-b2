<?php
namespace d2\query;

use d2\literal\Call;
use d2\literal\Identifier;
use d2\literal\Constant;

use d2\Exception;
use d2\Literal;

class Insert extends \d2\Query
{
	private $keys = [];
	private $rows = [];
	private $ignore = false;
	private $replace = false;
	private $onDuplicateKeyUpdates = [];
	private $onDuplicateKeyUpdateAll = false;

	public function ignore()
	{
		$this->ignore = true;

		return $this;
	}

	public function replace()
	{
		$this->replace = true;

		return $this;
	}

	public function row(array $row)
	{
		if (!$row)
			throw new Exception('Empty row');

		ksort($row);

		$keys = array_keys($row);

		if (!$this->keys) {
			$this->keys = $keys;
		} else {
			if ($keys !== $this->keys)
				throw new Exception("All rows in single query must have identical fields");
		}

		$this->rows[] = array_values($row);

		return $this;
	}

	public function values(array $rows)
	{
		foreach ($rows as $row)
			$this->row($row);

		return $this;
	}

	public function onDuplicateKeyUpdate($any = null)
	{
		if (is_null($any)) {
			// update all of columns with `column` = VALUES(`column`)
			$this->onDuplicateKeyUpdateAll = true;
		} else {
			if (!is_array($any))
				$any = [$any];

			// list of columns to `column` = VALUES(`column`)
			foreach ($any as $k => $v) {
				if (is_numeric($k)) {
					$this->onDuplicateKeyUpdates[$v] = new Call(
						'VALUES',
						[$v instanceof Literal ? $v : new Identifier($v)]
					);
				} else {
					$this->onDuplicateKeyUpdates[$k] = $v instanceof Literal ? $v : new Constant($v);
				}
			}
		}

		return $this;
	}

	public function toString(\d2\Quote $quote)
	{
		if (!$this->rows) {
			throw new Exception('Empty INSERT');
		}

		$sql = '';
		if ($this->replace)
			$sql = 'REPLACE';
		else
			$sql = 'INSERT';

		if ($this->ignore)
			$sql .= ' IGNORE';

		$sql .= ' INTO ' . $this->table->toString($quote);

		$sql .= '(' . $quote->identifier($this->keys) . ')';

		$sql .= ' VALUES ';

		$strings = [];
		foreach ($this->rows as $row) {
			/*
			 * Значения в строках уже отсортированы по названием филдов,
			 * так что тут больше ничего делать не надо
			 */
			$vals = [];
			foreach ($row as $f) {
				if ($f instanceof \d2\Literal)
					$vals[] = $f->toString($quote);
				else
					$vals[] = $quote->value($f);
			}

			$strings[] = '(' . implode(', ', $vals) . ')';
		}

		$sql .= implode(', ', $strings);

		if ($this->onDuplicateKeyUpdateAll || $this->onDuplicateKeyUpdates) {
			$sql .= ' ON DUPLICATE KEY UPDATE';

			$onDup = $this->onDuplicateKeyUpdates;

			if ($this->onDuplicateKeyUpdateAll) {
				foreach ($this->keys as $column) {
					if (isset($onDup[$column]))
						continue; // override

					$onDup[$column] = new Call('VALUES', [new Identifier($column)]);
				}
			}

			ksort($onDup);

			$strings = [];

			foreach ($onDup as $field => $value) {
				$strings[] = $quote->identifier($field) . ' = ' . $value->toString($quote);
			}

			$sql .= ' ' . implode(', ', $strings);
		}

		return $sql;
	}

}
