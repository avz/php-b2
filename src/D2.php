<?php
namespace d2;

class D2
{

	/**
	 * @param string|Literal $table
	 * @return \d2\Select
	 */
	public function select($table)
	{
		return new \d2\query\Select($table);
	}

	/**
	 * @param string|Literal $table
	 * @return \d2\Update
	 */
	public function update($table)
	{
		$u = new \d2\query\Update($table);

		return $u;
	}

	/**
	 * @param string|Literal $table
	 * @return \d2\Delete
	 */
	public function delete($table)
	{
		$d = new \d2\query\Delete($table);

		return $d;
	}

	/**
	 * @param string|Literal $table
	 * @param array $rows
	 * @return \d2\Insert
	 */
	public function insert($table, array $rows = null)
	{
		$insert = new \d2\query\Insert($table);

		if ($rows)
			$insert->values($rows);

		return $insert;
	}
}
