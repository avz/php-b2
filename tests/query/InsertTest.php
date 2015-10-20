<?php
namespace b2\tests\query;

use b2\query\Insert;
use b2\literal\PlainSql;

class InsertTest extends \b2\tests\Base
{
	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Empty INSERT
	 */
	public function testEmpty() {
		$insert = new Insert('ttt');
		$insert->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage All rows in single query must have identical fields
	 */
	public function testMismatch() {
		$insert = new Insert('ttt');
		$insert->row(['hello' => 'world']);
		$insert->row(['hello1' => 'hello']);
	}

	public function testColumnsSort() {
		$insert = new Insert('ttt');
		$insert->row(['b' => 2, 'a' => 1, 'c' => 3]);
		$insert->row(['c' => 3, 'a' => 1, 'b' => 2]);
		$insert->row(['b' => 2, 'c' => 3, 'a' => 1]);
		$this->assertEquals(
			"INSERT INTO `ttt`(`a`, `b`, `c`) VALUES ('1', '2', '3'), ('1', '2', '3'), ('1', '2', '3')",
			$insert->toString($this->quoter())
		);
	}

	public function testOneRow() {
		$insert = new Insert('ttt');
		$insert->row(['hello' => 'world']);

		$this->assertEquals("INSERT INTO `ttt`(`hello`) VALUES ('world')", $insert->toString($this->quoter()));
	}

	public function testMultiRows() {
		$insert = new Insert('ttt');
		$insert->row(['hello' => 'world']);
		$insert->row(['hello' => 'hello']);

		$this->assertEquals("INSERT INTO `ttt`(`hello`) VALUES ('world'), ('hello')", $insert->toString($this->quoter()));

		$insert->row(['hello' => 'foo']);

		$this->assertEquals("INSERT INTO `ttt`(`hello`) VALUES ('world'), ('hello'), ('foo')", $insert->toString($this->quoter()));

		$insert = new Insert('aaa');
		$insert->values([['hello' => 'world'], ['hello' => 'hello']]);

		$this->assertEquals("INSERT INTO `aaa`(`hello`) VALUES ('world'), ('hello')", $insert->toString($this->quoter()));

		$insert->row(['hello' => 'foo']);
		$this->assertEquals("INSERT INTO `aaa`(`hello`) VALUES ('world'), ('hello'), ('foo')", $insert->toString($this->quoter()));
	}

	public function testIgnore() {
		$insert = new Insert('ttt');
		$insert->row(['hello' => 'world']);
		$insert->ignore();

		$this->assertEquals("INSERT IGNORE INTO `ttt`(`hello`) VALUES ('world')", $insert->toString($this->quoter()));
	}

	public function testReplace() {
		$insert = new Insert('ttt');
		$insert->row(['hello' => 'world']);
		$insert->replace();

		$this->assertEquals("REPLACE INTO `ttt`(`hello`) VALUES ('world')", $insert->toString($this->quoter()));
	}

	public function testOnDuplicateUpdate() {
		$insert = new Insert('t');
		$insert->row(['a' => 'b', 'c' => 'd', 'e' => 'f']);
		$insert->onDuplicateKeyUpdate();
		$this->assertEquals(
			"INSERT INTO `t`(`a`, `c`, `e`) VALUES ('b', 'd', 'f') ON DUPLICATE KEY UPDATE `a` = VALUES(`a`), `c` = VALUES(`c`), `e` = VALUES(`e`)",
			$insert->toString($this->quoter())
		);

		$insert->onDuplicateKeyUpdate('a');
		$this->assertEquals(
			"INSERT INTO `t`(`a`, `c`, `e`) VALUES ('b', 'd', 'f') ON DUPLICATE KEY UPDATE `a` = VALUES(`a`), `c` = VALUES(`c`), `e` = VALUES(`e`)",
			$insert->toString($this->quoter())
		);

		$insert->onDuplicateKeyUpdate(['a' => 'b']);
		$this->assertEquals(
			"INSERT INTO `t`(`a`, `c`, `e`) VALUES ('b', 'd', 'f') ON DUPLICATE KEY UPDATE `a` = 'b', `c` = VALUES(`c`), `e` = VALUES(`e`)",
			$insert->toString($this->quoter())
		);

		$insert->onDuplicateKeyUpdate(['a' => new PlainSql('1 + 1')]);

		$this->assertEquals(
			"INSERT INTO `t`(`a`, `c`, `e`) VALUES ('b', 'd', 'f') ON DUPLICATE KEY UPDATE `a` = 1 + 1, `c` = VALUES(`c`), `e` = VALUES(`e`)",
			$insert->toString($this->quoter())
		);


		$insert = new Insert('b');
		$insert->onDuplicateKeyUpdate('e');
		$insert->row(['e' => 'f']);

		$this->assertEquals(
			"INSERT INTO `b`(`e`) VALUES ('f') ON DUPLICATE KEY UPDATE `e` = VALUES(`e`)",
			$insert->toString($this->quoter())
		);
	}

	public function testComplexInValue() {
		$insert = new Insert('t');
		$insert->row(['a' => new PlainSql('1000 + 100')]);
		$this->assertEquals(
			'INSERT INTO `t`(`a`) VALUES (1000 + 100)',
			$insert->toString($this->quoter())
		);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Table is not specified
	 */
	public function testNoTable() {
		$d = new Insert();
		$d->row(['a' => 1]);
		$d->toString($this->quoter());
	}
}
