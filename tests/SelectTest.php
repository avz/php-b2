<?php
namespace common\d2\tests;

class SelectTest extends Base
{
	public function testToString() {
		$s = new \common\d2\Select('t1');
		$s->column('id');
		$this->assertEquals("SELECT `id` FROM `t1`", $s->toString($this->quoter()));

		$s->where('id > ?', [10]);
		$this->assertEquals("SELECT `id` FROM `t1` WHERE (id > '10')", $s->toString($this->quoter()));

		$s->innerJoin('t2', 't2.id = t1.id');
		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON (t2.id = t1.id) WHERE (id > '10')",
			$s->toString($this->quoter())
		);

		$s->groupBy('grp');
		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON (t2.id = t1.id) WHERE (id > '10')"
				. " GROUP BY `grp`"
			, $s->toString($this->quoter())
		);

		$s->orderBy('ord', 'DESC');

		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON (t2.id = t1.id) WHERE (id > '10')"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
			, $s->toString($this->quoter())
		);

		$s->limit(10);
		$s->offset(20);

		$this->assertEquals(
			"SELECT `id` FROM `t1` INNER JOIN `t2` ON (t2.id = t1.id) WHERE (id > '10')"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
				. " LIMIT 10 OFFSET 20"
			, $s->toString($this->quoter())
		);

		$s->column(new \common\d2\PlainSql('YEAR() - bYear'), 'age');

		$this->assertEquals(
			"SELECT `id`, YEAR() - bYear AS `age` FROM `t1` INNER JOIN `t2` ON (t2.id = t1.id) WHERE (id > '10')"
				. " GROUP BY `grp`"
				. " ORDER BY `ord` DESC"
				. " LIMIT 10 OFFSET 20"
			, $s->toString($this->quoter())
		);


		$s = new \common\d2\Select('user');
		$s->column('*');
		$this->assertEquals("SELECT * FROM `user`", $s->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage You must specify columns
	 */
	public function testEmptyColumns() {
		$s = new \common\d2\Select('hi');
		$s->toString($this->quoter());
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Alias must be a string
	 */
	public function testAliasNotAString() {
		$s = new \common\d2\Select('hi');
		$s->column('c', 10);
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Numerical aliases is not allowed
	 */
	public function testAliasIsNumeric() {
		$s = new \common\d2\Select('hi');
		$s->column('c', '10');
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Alias name '*' is not alowed
	 */
	public function testAliasIsAsterisk() {
		$s = new \common\d2\Select('hi');
		$s->column('c', '*');
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Multiple definition of '*'
	 */
	public function testTooManyAsterisks() {
		$s = new \common\d2\Select('hi');
		$s->column('*');
		$s->column('*');
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Can't set alias to '*'
	 */
	public function testAliasToyAsterisk() {
		$s = new \common\d2\Select('hi');
		$s->column('*', 'jj');
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Non unique alias name: c
	 */
	public function testNonUniqueAlias() {
		$s = new \common\d2\Select('hi');
		$s->column('a', 'c');
		$s->column('b', 'c');
	}
}
