<?php
namespace b2;

class B2
{

	/**
	 * @param string|Literal $table
	 * @return \b2\Select
	 */
	public function select($table)
	{
		return new \b2\query\Select($table);
	}

	/**
	 * @param string|Literal $table
	 * @return \b2\Update
	 */
	public function update($table)
	{
		$u = new \b2\query\Update($table);

		return $u;
	}

	/**
	 * @param string|Literal $table
	 * @return \b2\Delete
	 */
	public function delete($table)
	{
		$d = new \b2\query\Delete($table);

		return $d;
	}

	/**
	 * @param string|Literal $table
	 * @param array $rows
	 * @return \b2\Insert
	 */
	public function insert($table, array $rows = null)
	{
		$insert = new \b2\query\Insert($table);

		if ($rows)
			$insert->values($rows);

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
