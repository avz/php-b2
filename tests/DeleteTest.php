<?php
namespace common\d2\tests;

class DeleteTest extends Base
{
	public function testToString() {
		$d = new \common\d2\Delete('table');
		$this->assertEquals('DELETE FROM `table`', $d->toString($this->quoter()));
		$d->where('id IS NULL');
		$this->assertEquals('DELETE FROM `table` WHERE (id IS NULL)', $d->toString($this->quoter()));

		$d = new \common\d2\Delete(new \common\d2\PlainSql('some strange sql'));
		$this->assertEquals('DELETE FROM some strange sql', $d->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Only strings and Literals allowed in table name
	 */
	public function testInvalidTable() {
		$d = new \common\d2\Delete(10);
	}
}
