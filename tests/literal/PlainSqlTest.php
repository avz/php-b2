<?php
namespace b2\tests\literal;

use b2\literal\PlainSql;
use b2\literal\Identifier;
use b2\literal\Constant;

class PlainSqlTest extends \b2\tests\Base
{
	public function testToString() {
		$sql = "hello\"world `any characters.'\0\r\naa";

		$ps = new PlainSql($sql);

		$this->assertSame($sql, $ps->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage SQL must be a string
	 */
	public function testInvalidArgument() {
		new PlainSql(10);
	}

	public function testBinds() {
		$ps = new PlainSql('hello', []);
		$this->assertSame('hello', $ps->toString($this->quoter()));

		$ps = new PlainSql('hello > ?', [new Constant(1)]);
		$this->assertSame("hello > '1'", $ps->toString($this->quoter()));

		$ps = new PlainSql(
			'first=:first second=? third=:third',
			[
				':third' => new Constant('third'),
				new Identifier('second'),
				':first' => new Identifier('first'),
			]
		);
		$this->assertSame("first=`first` second=`second` third='third'", $ps->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Bind key 0 was not found
	 */
	public function testTooManyBinds() {
		$ps = new PlainSql('hello :world ?', [':world' => new Constant(1)]);
		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Too many binds: :hello
	 */
	public function testTooManyBinds2() {
		$ps = new PlainSql(
			'hello :world',
			[
				':world' => new Constant(1),
				':hello' => new Constant(2)
			]
		);

		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Bind key :hello was not found
	 */
	public function testNotEnoughBinds() {
		$ps = new PlainSql('hello :world :hello', [':world' => new Constant(1)]);
		$ps->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Bind must be a Literal
	 */
	public function testInvalidBind() {
		$ps = new PlainSql('hello', [10]);
	}
}
