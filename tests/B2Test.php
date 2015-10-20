<?php
namespace b2\tests;

class B2Test extends \b2\tests\Base
{
	public function testSelect() {
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

	public function testUpdate() {
		$b2 = new \b2\B2;

		$expected = new \b2\query\Update('user');
		$expected->where(['a' => 'b']);
		$actual = $b2->update('user', ['a' => 'b']);
		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Update('user');
		$expected->where('hello = "world"');
		$actual = $b2->update('user', 'hello = "world"');
		$this->assertEquals($expected, $actual);
	}

	public function testDelete() {
		$b2 = new \b2\B2;

		$expected = new \b2\query\Delete('user');
		$expected->where(['a' => 'b']);
		$actual = $b2->delete('user', ['a' => 'b']);
		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Delete('user');
		$expected->where('hello = "world"');
		$actual = $b2->delete('user', 'hello = "world"');
		$this->assertEquals($expected, $actual);
	}

	public function testInsert() {
		$b2 = new \b2\B2;

		$expected = new \b2\query\Insert('user');
		$actual = $b2->insert('user');
		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Insert('user');
		$expected->row(['a' => 'b']);

		$actual = $b2->insert('user', [['a' => 'b']]);
		$this->assertEquals($expected, $actual);

		$expected = new \b2\query\Insert();
		$expected->table('user');
		$expected->rows([['a' => 'b'], ['a' => 'd']]);
		$actual = $b2->insert('user', [['a' => 'b'], ['a' => 'd']]);
		$this->assertEquals($expected, $actual);
	}
}
