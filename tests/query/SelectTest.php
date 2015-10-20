<?php
namespace b2\tests\query;

use b2\literal\PlainSql;
use b2\query\Select;

class SelectTest extends \b2\tests\Base
{
	public function testToString() {
		$s = new Select('t1');
		$s->column('id');
		$this->assertEquals("SELECT `id` FROM `t1`", $s->toString($this->quoter()));

		$s->where('id > ?', [10]);
		$this->assertEquals("SELECT `id` FROM `t1` WHERE id > '10'", $s->toString($this->quoter()));

		$s->innerJoin('t2', 't2.id = t1.id');
		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON t2.id = t1.id WHERE id > '10'",
			$s->toString($this->quoter())
		);

		$s->groupBy('grp');
		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON t2.id = t1.id WHERE id > '10'"
				. " GROUP BY `grp`"
			, $s->toString($this->quoter())
		);

		$s->orderBy('ord', 'DESC');

		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON t2.id = t1.id WHERE id > '10'"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
			, $s->toString($this->quoter())
		);

		$s->limit(10);
		$s->offset(20);

		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON t2.id = t1.id WHERE id > '10'"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
				. " LIMIT 10 OFFSET 20"
			, $s->toString($this->quoter())
		);

		$s->column(new PlainSql('YEAR() - bYear'), 'age');

		$this->assertEquals(
			"SELECT `id`, YEAR() - bYear AS `age` FROM `t1` INNER JOIN `t2` ON t2.id = t1.id WHERE id > '10'"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
				. " LIMIT 10 OFFSET 20"
			, $s->toString($this->quoter())
		);


		$s = new Select('user');
		$s->column('*');
		$this->assertEquals("SELECT * FROM `user`", $s->toString($this->quoter()));

		$s = new Select('pay');
		$s->columns(['id', 'value' => 'price', 'sku']);
		$this->assertEquals("SELECT `id`, `price` AS `value`, `sku` FROM `pay`", $s->toString($this->quoter()));

		$s = new Select('pay');
		$s->allColumns();
		$this->assertEquals("SELECT * FROM `pay`", $s->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage You must specify columns
	 */
	public function testEmptyColumns() {
		$s = new Select('hi');
		$s->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Alias must be a string
	 */
	public function testAliasNotAString() {
		$s = new Select('hi');
		$s->column('c', 10);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Numerical aliases is not allowed
	 */
	public function testAliasIsNumeric() {
		$s = new Select('hi');
		$s->column('c', '10');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Alias name '*' is not alowed
	 */
	public function testAliasIsAsterisk() {
		$s = new Select('hi');
		$s->column('c', '*');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Multiple definition of '*'
	 */
	public function testTooManyAsterisks() {
		$s = new Select('hi');
		$s->column('*');
		$s->column('*');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Can't set alias to '*'
	 */
	public function testAliasToyAsterisk() {
		$s = new Select('hi');
		$s->column('*', 'jj');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Non unique alias name: c
	 */
	public function testNonUniqueAlias() {
		$s = new Select('hi');
		$s->column('a', 'c');
		$s->column('b', 'c');
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Column name or Literal expected
	 */
	public function testInvalidColumn() {
		$s = new Select('hi');
		$s->column(new \stdClass);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Table is not specified
	 */
	public function testNoTable() {
		$s = new Select();
		$s->column('id');
		$s->toString($this->quoter());
	}
}
