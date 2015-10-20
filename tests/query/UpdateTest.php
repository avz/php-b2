<?php
namespace b2\tests\query;

use b2\query\Update;

class UpdateTest extends \b2\tests\Base
{
	public function testToString() {
		$u = new Update('t1');
		$u->set('hi', 'val');
		$this->assertEquals("UPDATE `t1` SET `hi` = 'val'", $u->toString($this->quoter()));
		$u->set('hi2', 'val2');
		$this->assertEquals("UPDATE `t1` SET `hi` = 'val', `hi2` = 'val2'", $u->toString($this->quoter()));

		$u->limit(10);
		$this->assertEquals("UPDATE `t1` SET `hi` = 'val', `hi2` = 'val2' LIMIT 10", $u->toString($this->quoter()));

		$u->where('id = 1');
		$this->assertEquals(
			"UPDATE `t1` SET `hi` = 'val', `hi2` = 'val2' WHERE id = 1 LIMIT 10",
			$u->toString($this->quoter())
		);

		$u->orderBy('id');

		$this->assertEquals(
			"UPDATE `t1` SET `hi` = 'val', `hi2` = 'val2' WHERE id = 1 ORDER BY `id` LIMIT 10",
			$u->toString($this->quoter())
		);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Empty set
	 */
	public function testEmptySet() {
		$u = new Update('hi');
		$u->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Table is not specified
	 */
	public function testNoTable() {
		$u = new Update();
		$u->set('a = b');
		$u->toString($this->quoter());
	}
}
