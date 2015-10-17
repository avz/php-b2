<?php
namespace d2\tests;

class WhereTest extends Base
{
	public function testToString() {
		$where = new \d2\Where(new \d2\Identifier('hello'));
		$this->assertEquals('`hello`', $where->toString($this->quoter()));

		$where = new \d2\Where;
		$where->addAnd(new \d2\Identifier('column'));
		$this->assertEquals('`column`', $where->toString($this->quoter()));

		$where->addAnd(new \d2\Identifier('column2'));
		$this->assertEquals('(`column` AND `column2`)', $where->toString($this->quoter()));

		$where->addOr(new \d2\Identifier('column3'));
		$this->assertEquals('((`column` AND `column2`) OR `column3`)', $where->toString($this->quoter()));

		$where = new \d2\Where;
		$where->addOr(new \d2\Constant(10));
		$this->assertEquals("'10'", $where->toString($this->quoter()));
	}

	public function testIsEmpty() {
		$where = new \d2\Where(new \d2\Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());

		$where = new \d2\Where();
		$this->assertEquals(true, $where->isEmpty());

		$where->addAnd(new \d2\Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Empty WHERE
	 */
	public function testEmpty() {
		$where = new \d2\Where;
		$where->toString($this->quoter());
	}
}
