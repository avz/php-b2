<?php
namespace d2\tests\ability;

class SimpleLimitable
{
	use \d2\ability\HasLimit;

	public function toString(\d2\Quote $q)
	{
		return $this->limitToString($q);
	}

	public function isEmpty() {
		return $this->limitIsEmpty();
	}
}

class HasLimitTest extends \d2\tests\Base
{
	public function testToString() {
		$l = new SimpleLimitable;

		$l->limit(10);

		$this->assertEquals('LIMIT 10', $l->toString($this->quoter()));
		$l->offset(20);
		$this->assertEquals('LIMIT 10 OFFSET 20', $l->toString($this->quoter()));
	}

	public function testEmpty() {
		$l = new SimpleLimitable;
		$this->assertEquals(true, $l->isEmpty());
		$l->limit(10);
		$this->assertEquals(false, $l->isEmpty());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage OFFSET without LIMIT
	 */
	public function testOffsetWithoutLimit() {
		$l = new SimpleLimitable;
		$l->offset(1000);

		$l->toString($this->quoter());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage LIMIT must be positive int
	 */
	public function testLimitInvalid() {
		$l = new SimpleLimitable;
		$l->limit('hello');
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage OFFSET must be positive int
	 */
	public function testOffsetInvalid() {
		$l = new SimpleLimitable;
		$l->offset('hello');
	}
}
