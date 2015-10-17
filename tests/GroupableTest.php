<?php

namespace common\d2\tests;

class SimpleGroupable
{
	use \common\d2\Groupable;

	public function toString(\common\d2\Quote $q)
	{
		return $this->groupToString($q);
	}

	public function isEmpty() {
		return $this->orderIsEmpty();
	}
}

class GroupableTest extends Base
{
	public function testToString() {
		$s = new SimpleGroupable;
		$s->groupBy('col');

		$this->assertEquals('GROUP BY `col`', $s->toString($this->quoter()));
		$s->groupBy('col2', 'DESC');

		$this->assertEquals('GROUP BY `col`, `col2` DESC', $s->toString($this->quoter()));

		$s = new SimpleGroupable;
		$s->groupBy(new \common\d2\PlainSql('hello'), 'DESC');
		$this->assertEquals('GROUP BY hello DESC', $s->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Direction must be ASC or DESC
	 */
	public function testInvalidDirection() {
		$s = new SimpleGroupable();
		$s->groupBy('hi', 'DASC');
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Only string  or Literal allowed
	 */
	public function testInvalidNullDesc() {
		$s = new SimpleGroupable();
		$s->groupBy(null);
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Only string  or Literal allowed
	 */
	public function testInvalidColumn() {
		$s = new SimpleGroupable();
		$s->groupBy(new \stdClass);
	}
}
