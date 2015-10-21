<?php
namespace b2\tests\ability;

use b2\literal\PlainSql;
use b2\literal\Constant;
use b2\literal\Identifier;
use b2\ability\WhereUpdateCommon;

class WhereUpdateCommonTest extends \b2\tests\Base
{
	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentObject() {
		WhereUpdateCommon::extractExpressionsFromArgs([new \stdClass]);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentNull() {
		WhereUpdateCommon::extractExpressionsFromArgs([null]);
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Incorrect expression definition
	 */
	public function testInvalidArgumentBool() {
		WhereUpdateCommon::extractExpressionsFromArgs([true]);
	}

		/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Two-arguments form is not allowed when Literal given
	 */
	public function testInvalidArgumentLiteralWithBinds() {
		WhereUpdateCommon::extractExpressionsFromArgs([new PlainSql('column'), 'hello']);
	}
}
