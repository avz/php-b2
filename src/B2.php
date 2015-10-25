<?php
namespace b2;

class B2
{
	/**
	 *
	 * @return \b2\query\Select
	 */
	protected function createSelect()
	{
		return new query\Select;
	}

	/**
	 *
	 * @return \b2\query\Update
	 */
	protected function createUpdate()
	{
		return new query\Update;
	}

	/**
	 *
	 * @return \b2\query\Insert
	 */
	protected function createInsert()
	{
		return new query\Insert;
	}

	/**
	 *
	 * @return \b2\query\Delete
	 */
	protected function createDelete()
	{
		return new query\Delete;
	}

	private function extractWhereDef(Query $query, $args)
	{
		if (sizeof($args) === 1)
			$query->where($args[0]);
		else
			$query->where($args[0], $args[1]);
	}

	/**
	 * @param string|Literal $table
	 * @return \b2\Select
	 */
	public function select($table /*, WHEREDEF */)
	{
		$select = $this->createSelect()->table($table);

		if (func_num_args() > 1)
			$this->extractWhereDef($select, array_slice(func_get_args(), 1));

		return $select;
	}

	/**
	 * @param string|Literal $table
	 * @return \b2\Update
	 */
	public function update($table /*, WHEREDEF */)
	{
		$u = $this->createUpdate()->table($table);

		if (func_num_args() > 1)
			$this->extractWhereDef($u, array_slice(func_get_args(), 1));

		return $u;
	}

	/**
	 * @param string|Literal $table
	 * @return \b2\Delete
	 */
	public function delete($table /*, WHEREDEF */)
	{
		$d = $this->createDelete()->table($table);

		if (func_num_args() > 1)
			$this->extractWhereDef($d, array_slice(func_get_args(), 1));

		return $d;
	}

	/**
	 * @param string|Literal $table
	 * @param array $rows
	 * @return \b2\Insert
	 */
	public function insert($table, array $rows = null)
	{
		$insert = $this->createInsert()->table($table);

		if ($rows)
			$insert->rows($rows);

		return $insert;
	}

	public function constant($value) {
		return new literal\Constant($value);
	}

	public function table($name) {
		return new literal\Identifier($name);
	}

	public function field($name) {
		return new literal\Identifier($name);
	}

	public function sql($sql, array $binds = []) {
		foreach ($binds as $k => $v) {
			if (!($v instanceof Literal))
				$binds[$k] = $this->constant($v);
		}

		return new literal\PlainSql($sql, $binds);
	}
}
