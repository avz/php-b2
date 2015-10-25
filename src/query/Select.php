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
	private $fields = [];

	public function __construct($table = null)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(\b2\Quote $quote)
	{
		if (!$this->fields) {
			throw new Exception('You must specify fields');
		}

		$sql = 'SELECT ';
		$sql .= $this->fieldToString($quote);
		$sql .= ' FROM ' . $this->needTable()->toString($quote);

		$sql = $this->joinsConcatSql($quote, $sql);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->groupConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

	private function fieldToString(\b2\Quote $quote) {
		$list = [];

		foreach ($this->fields as $alias => $field) {
			if (ctype_digit((string)$alias) || $alias === '*') {
				$list[] = $field->toString($quote);
			} else {
				$list[] = $field->toString($quote) . ' AS ' . $quote->identifier($alias);
			}
		}

		return implode(', ', $list);
	}

	public function field($name, $alias = null) {
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

			if (isset($this->fields[$alias])) {
				throw new Exception('Non unique alias name: ' . $alias);
			}
		}

		if ($name === '*') {
			if ($alias !== null) {
				throw new Exception("Can't set alias to '*'");
			}

			if (isset($this->fields['*'])) {
				throw new Exception("Multiple definition of '*'");
			}

			$alias = '*';

			$e = new PlainSql('*');

		} elseif ($name instanceof Literal) {
			$e = $name;

		} elseif (is_string($name)) {
			$e = new Identifier($name);

		} else {
			throw new Exception('Field name or Literal expected');
		}

		if ($alias !== null) {
			$this->fields[$alias] = $e;
		} else {
			$this->fields[] = $e;
		}

		return $this;
	}

	public function allFields() {
		$this->field('*');
	}

	public function fields(array $fields) {
		foreach ($fields as $alias => $field) {
			if (ctype_digit((string)$alias))
				$this->field($field);
			else
				$this->field($field, $alias);
		}

		return $this;
	}
}
