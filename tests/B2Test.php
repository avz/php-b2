<?php
namespace b2\tests;

class B2Test extends \b2\tests\Base
{
	public function testSelectWhere() {
		$b2 = new \b2\B2;

		$expected = new \b2\query\Select('user');
		$actual = $b2->select('user');

		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Select('user');
		$expected->where('a = 10');

		$actual = $b2->select('user', 'a = 10');

		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Select('user');
		$expected->where('a = ?', [10]);

		$actual = $b2->select('user', 'a = ?', [10]);

		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Select('user');
		$expected->where(['a' => 'b']);

		$actual = $b2->select('user', ['a' => 'b']);

		$this->assertEquals($expected, $actual);
	}
}
