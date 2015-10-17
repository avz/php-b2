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

//
//$quote = new Quote;
//$d2 = new D2;
//$insert = new Insert('sss.aaa');
//$insert->ignore();
//$insert->row(['a' => new BiOperation(new Identifier('field'), '=', new Constant(1000))]);
//$insert->onDuplicateKeyUpdate();
//
//echo $insert->toString($quote), "\n";
//
//$where = new Where();
//$where->addAnd(new BiOperation(new Identifier('column'), '=', new Constant('column')));
//$where->addAnd(new NotIn(new Identifier('column'), [new Constant('v0'), new Constant('v1')]));
//echo $where->toString($quote), "\n";
