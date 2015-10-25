<?php
namespace b2\tests\ability;

use b2\Exception;
use b2\literal\Identifier;
use b2\literal\PlainSql;

class SimpleOrderable
{
	use \b2\ability\HasOrder;

	public function toString(\b2\Quote $q)
	{
		return $this->orderToString($q);
	}

	public function isEmpty() {
		return $this->orderIsEmpty();
	}
}

class HasOrderTest extends \b2\tests\Base
{
	public function testToString() {
		$s = new SimpleOrderable;
		$s->orderBy('col');

		$this->assertSame('ORDER BY `col`', $s->toString($this->quoter()));
		$s->orderBy('col2', 'DESC');

		$this->assertSame('ORDER BY `col`, `col2` DESC', $s->toString($this->quoter()));

		$s = new SimpleOrderable;
		$s->orderBy(new PlainSql('hello'), 'DESC');
		$this->assertSame('ORDER BY hello DESC', $s->toString($this->quoter()));

		$s = new SimpleOrderable;
		$s->orderBy(null);
		$this->assertSame('ORDER BY NULL', $s->toString($this->quoter()));
	}

	/**
	 * @expectedException \b2\Exception
	 * @expectedExceptionMessage Direction must be ASC or DESC
	 */
	public function testInvalidDirection() {
		$s = new SimpleOrderable();
		$s->orderBy('hi', 'DASC');
	}

	/**
	 * @expectedException \b2\Exception
	 * @expectedExceptionMessage ORDER BY NULL DESC is not allowed
	 */
	public function testInvalidNullDesc() {
		$s = new SimpleOrderable();
		$s->orderBy(null, 'DESC');
	}

	/**
	 * @expectedException \b2\Exception
	 * @expectedExceptionMessage Only string, null or Literal allowed
	 */
	public function testInvalidField() {
		$s = new SimpleOrderable();
		$s->orderBy(new \stdClass);
	}

	public function testEmpty() {
		$s = new SimpleOrderable();
		$this->assertSame(null, $s->toString($this->quoter()));
	}
}
