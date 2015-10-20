<?php
namespace b2\tests\query;

use \b2\query\Delete;
use \b2\literal\PlainSql;

class DeleteTest extends \b2\tests\Base
{
	public function testToString() {
		$d = new Delete('table');
		$this->assertEquals('DELETE FROM `table`', $d->toString($this->quoter()));
		$d->where('id IS NULL');
		$this->assertEquals('DELETE FROM `table` WHERE id IS NULL', $d->toString($this->quoter()));

		$d = new Delete(new PlainSql('some strange sql'));
		$this->assertEquals('DELETE FROM some strange sql', $d->toString($this->quoter()));

		$d->limit(10);
		$this->assertEquals('DELETE FROM some strange sql LIMIT 10', $d->toString($this->quoter()));

		$d->orderBy('column');
		$this->assertEquals('DELETE FROM some strange sql ORDER BY `column` LIMIT 10', $d->toString($this->quoter()));

		$d = new Delete('table');
		$d->orderBy('c');
		$this->assertEquals('DELETE FROM `table` ORDER BY `c`', $d->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Only strings and Literals allowed in table name
	 */
	public function testInvalidTable() {
		$d = new Delete(10);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Table is not specified
	 */
	public function testNoTable() {
		$d = new Delete();
		$d->toString($this->quoter());
	}
}
