<?php
namespace d2\tests;

class UpdateTest extends Base
{
	public function testToString() {
		$u = new \d2\Update('t1');
		$u->set('hi', 'val');
		$this->assertEquals("UPDATE `t1` SET (`hi` = 'val')", $u->toString($this->quoter()));
		$u->set('hi2', 'val2');
		$this->assertEquals("UPDATE `t1` SET (`hi` = 'val'), (`hi2` = 'val2')", $u->toString($this->quoter()));

		$u->limit(10);
		$this->assertEquals("UPDATE `t1` SET (`hi` = 'val'), (`hi2` = 'val2') LIMIT 10", $u->toString($this->quoter()));

		$u->where('id = 1');
		$this->assertEquals(
			"UPDATE `t1` SET (`hi` = 'val'), (`hi2` = 'val2') WHERE (id = 1) LIMIT 10",
			$u->toString($this->quoter())
		);

		$u->orderBy('id');

		$this->assertEquals(
			"UPDATE `t1` SET (`hi` = 'val'), (`hi2` = 'val2') WHERE (id = 1) ORDER BY `id` LIMIT 10",
			$u->toString($this->quoter())
		);
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Empty set
	 */
	public function testEmptySet() {
		$u = new \d2\Update('hi');
		$u->toString($this->quoter());
	}
}
