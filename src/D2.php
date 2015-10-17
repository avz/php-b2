<?php
namespace d2;

class D2
{

	public function select($table)
	{
		return new Select($table);
	}

	public function update($table)
	{
		$u = new Update($table);

		return $u;
	}

	public function delete($table)
	{
		$d = new Delete($table);

		return $d;
	}

	public function insert($table, array $rows = null)
	{
		$insert = new Insert($table);

		if ($rows)
			$insert->values($rows);

		return $insert;
	}
}
