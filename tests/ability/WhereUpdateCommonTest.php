<?php
namespace b2\tests\ability;

use b2\literal\PlainSql;
use b2\literal\Constant;
use b2\literal\Identifier;
use b2\literal\BiOperation;
use b2\ability\WhereUpdateCommon;

class WhereUpdateCommonTest extends \b2\tests\Base
{
	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentObject() {
		WhereUpdateCommon::extractExpressions([new \stdClass]);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Too many arguments
	 */
	public function testTooMany() {
		WhereUpdateCommon::extractExpressions(['a', 'b', 'c']);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Not enough arguments
	 */
	public function testNotEnough() {
		WhereUpdateCommon::extractExpressions([]);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentNull() {
		WhereUpdateCommon::extractExpressions([null]);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentBool() {
		WhereUpdateCommon::extractExpressions([true]);
	}

		/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Two-arguments form is not allowed when Literal given
	 */
	public function testInvalidArgumentLiteralWithBinds() {
		WhereUpdateCommon::extractExpressions([new PlainSql('field'), 'hello']);
	}

	public function testPlainSql() {
		$r = WhereUpdateCommon::extractExpressions([new PlainSql('hello')]);

		$this->assertEquals([new PlainSql('hello')], $r);
	}

	public function testPlainKeyValue() {
		$r = WhereUpdateCommon::extractExpressions(['field', 'value']);

		$this->assertEquals([new BiOperation(new Identifier('field'), '=', new Constant('value'))], $r);
	}
}
