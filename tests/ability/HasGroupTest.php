<?php
namespace b2\tests\ability;

class SimpleGroupable
{
	use \b2\ability\HasGroup;

	public function toString(\b2\Quote $q)
	{
		return $this->groupToString($q);
	}

	public function isEmpty() {
		return $this->orderIsEmpty();
	}
}

class HasGroupTest extends \b2\tests\Base
{
	public function testToString() {
		$s = new SimpleGroupable;
		$s->groupBy('col');

		$this->assertSame('GROUP BY `col`', $s->toString($this->quoter()));
		$s->groupBy('col2', 'DESC');

		$this->assertSame('GROUP BY `col`, `col2` DESC', $s->toString($this->quoter()));

		$s = new SimpleGroupable;
		$s->groupBy(new \b2\literal\PlainSql('hello'), 'DESC');
		$this->assertSame('GROUP BY hello DESC', $s->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Direction must be ASC or DESC
	 */
	public function testInvalidDirection() {
		$s = new SimpleGroupable();
		$s->groupBy('hi', 'DASC');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Only string or Literal allowed
	 */
	public function testInvalidNullDesc() {
		$s = new SimpleGroupable();
		$s->groupBy(null);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Only string or Literal allowed
	 */
	public function testInvalidField() {
		$s = new SimpleGroupable();
		$s->groupBy(new \stdClass);
	}

	public function testEmpty() {
		$s = new SimpleGroupable;
		$this->assertSame(null, $s->toString($this->quoter()));
	}
}
