<?php
namespace d2\query;

use d2\literal\Where;
use d2\literal\Identifier;
use d2\literal\PlainSql;
use d2\Literal;

use d2\Exception;

class Select extends \d2\Query
{
	use \d2\ability\HasWhere;
	use \d2\ability\HasJoin;
	use \d2\ability\HasGroup;
	use \d2\ability\HasOrder;
	use \d2\ability\HasLimit;

	/**
	 *
	 * @var d2\literal
	 */
	private $columns = [];

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(\d2\Quote $quote)
	{
		if (!$this->columns) {
			throw new Exception('You must specify columns');
		}

		$sql = 'SELECT ';
		$sql .= $this->columnsToString($quote);
		$sql .= ' FROM ' . $this->table->toString($quote);

		$sql = $this->joinsConcatSql($quote, $sql);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->groupConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

	private function columnsToString(\d2\Quote $quote) {
		$list = [];

		foreach ($this->columns as $alias => $column) {
			if (ctype_digit((string)$alias) || $alias === '*') {
				$list[] = $column->toString($quote);
			} else {
				$list[] = $column->toString($quote) . ' AS ' . $quote->identifier($alias);
			}
		}

		return implode(', ', $list);
	}

	public function column($column, $alias = null) {
		$e = null;

		if ($alias !== null) {
			if (!is_string($alias)) {
				throw new Exception('Alias must be a string');
			}

			if ($alias === '*') {
				throw new Exception("Alias name '*' is not alowed");
			}

			if (ctype_digit((string)$alias)) {
				throw new Exception('Numerical aliases is not allowed');
			}

			if (isset($this->columns[$alias])) {
				throw new Exception('Non unique alias name: ' . $alias);
			}
		}

		if ($column === '*') {
			if ($alias !== null) {
				throw new Exception("Can't set alias to '*'");
			}

			if (isset($this->columns['*'])) {
				throw new Exception("Multiple definition of '*'");
			}

			$alias = '*';

			$e = new PlainSql('*');

		} elseif ($column instanceof Literal) {
			$e = $column;

		} elseif (is_string($column)) {
			$e = new Identifier($column);

		} else {
			throw new Exception('Column name or Literal expected');
		}

		if ($alias !== null) {
			$this->columns[$alias] = $e;
		} else {
			$this->columns[] = $e;
		}

		return $this;
	}
}
