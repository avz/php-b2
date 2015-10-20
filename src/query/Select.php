<?php
namespace b2\query;

use b2\literal\Where;
use b2\literal\Identifier;
use b2\literal\PlainSql;
use b2\Literal;

use b2\Exception;

class Select extends \b2\Query
{
	use \b2\ability\HasWhere;
	use \b2\ability\HasJoin;
	use \b2\ability\HasGroup;
	use \b2\ability\HasOrder;
	use \b2\ability\HasLimit;

	/**
	 *
	 * @var b2\literal
	 */
	private $columns = [];

	public function __construct($table = null)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(\b2\Quote $quote)
	{
		if (!$this->columns) {
			throw new Exception('You must specify columns');
		}

		$sql = 'SELECT ';
		$sql .= $this->columnsToString($quote);
		$sql .= ' FROM ' . $this->needTable()->toString($quote);

		$sql = $this->joinsConcatSql($quote, $sql);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->groupConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

	private function columnsToString(\b2\Quote $quote) {
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

	public function allColumns() {
		$this->column('*');
	}

	public function columns(array $columns) {
		foreach ($columns as $alias => $col) {
			if (ctype_digit((string)$alias))
				$this->column($col);
			else
				$this->column($col, $alias);
		}

		return $this;
	}
}
