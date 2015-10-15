<?php
namespace common\d2\tests;

class PlainSqlTest extends Base
{
	public function testToString() {
		$sql = "hello\"world `any characters.'\0\r\naa";

		$ps = new \common\d2\PlainSql($sql);

		$this->assertEquals($sql, $ps->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage SQL must be a string
	 */
	public function testInvalidArgument() {
		new \common\d2\PlainSql(10);
	}
}
