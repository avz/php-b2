<?php
namespace b2\tests\ability;

class SimpleJoinable
{
	use \b2\ability\HasJoin;

	public function toString(\b2\Quote $q)
	{
		return $this->joinsToString($q);
	}

	public function isEmpty() {
		return $this->joinsIsEmpty();
	}
}

class HasJoinTest extends \b2\tests\Base
{
	public function testToString() {
		$j = new SimpleJoinable;
		$j->leftJoin('hi', 'field > fielb2');
		$this->assertEquals("LEFT JOIN `hi` ON field > fielb2", $j->toString($this->quoter()));

		$j = new SimpleJoinable;
		$j->leftJoin('hi', 'field > ?', [10]);
		$this->assertEquals("LEFT JOIN `hi` ON field > '10'", $j->toString($this->quoter()));

		$j->innerJoin('hi2', 'fielb2 > ?', [20]);
		$this->assertEquals("LEFT JOIN `hi` ON field > '10' INNER JOIN `hi2` ON fielb2 > '20'", $j->toString($this->quoter()));

		$j = new SimpleJoinable;
		$j->innerJoin(new \b2\literal\PlainSql('some table'), ['a' => 'b', 'c' => 'd']);
		$this->assertEquals("INNER JOIN some table ON (`a` = 'b') AND (`c` = 'd')", $j->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Table name or Literal expected
	 */
	public function testInvalidDirection() {
		$j = new SimpleJoinable();
		$j->innerJoin(10, 'aaa > b');
	}
}
