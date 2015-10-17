<?php
namespace d2\tests;

class PlainSqlTest extends Base
{
	public function testToString() {
		$sql = "hello\"world `any characters.'\0\r\naa";

		$ps = new \d2\PlainSql($sql);

		$this->assertEquals($sql, $ps->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage SQL must be a string
	 */
	public function testInvalidArgument() {
		new \d2\PlainSql(10);
	}

	public function testBinds() {
		$ps = new \d2\PlainSql('hello', []);
		$this->assertEquals('hello', $ps->toString($this->quoter()));

		$ps = new \d2\PlainSql('hello > ?', [new \d2\Constant(1)]);
		$this->assertEquals("hello > '1'", $ps->toString($this->quoter()));

		$ps = new \d2\PlainSql(
			'first=:first second=? third=:third',
			[
				':third' => new \d2\Constant('third'),
				new \d2\Identifier('second'),
				':first' => new \d2\Identifier('first'),
			]
		);
		$this->assertEquals("first=`first` second=`second` third='third'", $ps->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Bind key 0 was not found
	 */
	public function testTooManyBinds() {
		$ps = new \d2\PlainSql('hello :world ?', [':world' => new \d2\Constant(1)]);
		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Too many binds: :hello
	 */
	public function testTooManyBinds2() {
		$ps = new \d2\PlainSql(
			'hello :world',
			[
				':world' => new \d2\Constant(1),
				':hello' => new \d2\Constant(2)
			]
		);

		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Bind key :hello was not found
	 */
	public function testNotEnoughBinds() {
		$ps = new \d2\PlainSql('hello :world :hello', [':world' => new \d2\Constant(1)]);
		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Bind must be a Literal
	 */
	public function testInvalidBind() {
		$ps = new \d2\PlainSql('hello', [10]);
	}
}
