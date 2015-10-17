<?php

namespace d2\tests;

class SimpleOrderable
{
	use \d2\Orderable;

	public function toString(\d2\Quote $q)
	{
		return $this->orderToString($q);
	}

	public function isEmpty() {
		return $this->orderIsEmpty();
	}
}

class OrderableTest extends Base
{
	public function testToString() {
		$s = new SimpleOrderable;
		$s->orderBy('col');

		$this->assertEquals('ORDER BY `col`', $s->toString($this->quoter()));
		$s->orderBy('col2', 'DESC');

		$this->assertEquals('ORDER BY `col`, `col2` DESC', $s->toString($this->quoter()));

		$s = new SimpleOrderable;
		$s->orderBy(new \d2\PlainSql('hello'), 'DESC');
		$this->assertEquals('ORDER BY hello DESC', $s->toString($this->quoter()));

		$s = new SimpleOrderable;
		$s->orderBy(null);
		$this->assertEquals('ORDER BY NULL', $s->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Direction must be ASC or DESC
	 */
	public function testInvalidDirection() {
		$s = new SimpleOrderable();
		$s->orderBy('hi', 'DASC');
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage ORDER BY NULL DESC is not allowed
	 */
	public function testInvalidNullDesc() {
		$s = new SimpleOrderable();
		$s->orderBy(null, 'DESC');
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Only string, null or Literal allowed
	 */
	public function testInvalidColumn() {
		$s = new SimpleOrderable();
		$s->orderBy(new \stdClass);
	}
}
