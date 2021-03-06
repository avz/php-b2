<?php
namespace b2\ability;

use b2\Literal;
use b2\literal\BiOperation;
use b2\literal\PlainSql;
use b2\literal\Constant;
use b2\literal\In;
use b2\Exception;
use b2\literal\Identifier;

/**
 * @internal
 */
abstract class WhereUpdateCommon {
	static private function prepared($prepared, array $binds = [])
	{
		$binds = array_map(
			function ($value) {
				if (is_array($value)) {
					$list = [];
					foreach ($value as $v) {
						$list[] = new Constant($v);
					}

					return new \b2\literal\AnyList($list);
				}

				return new Constant($value);
			},
			$binds
		);

		return new PlainSql($prepared, $binds);
	}

	static private function fieldEqual($fieldName, $fieldValue)
	{
		$e = null;

		if (is_array($fieldValue)) {
			$cases = array_map(
				function ($value) {
					if ($value instanceof Literal)
						return $value;
					else
						return new Constant($value);
				},
				$fieldValue
			);

			$e = new In(new Identifier($fieldName), $cases);
		} else {
			$v = null;
			if ($fieldValue instanceof Literal)
				$v = $fieldValue;
			else
				$v = new Constant($fieldValue);

			$e = new BiOperation(new Identifier($fieldName), '=', $v);
		}

		return $e;
	}

	static public function extractExpressions(array $args)
	{
		$numArgs = sizeof($args);

		if (!$numArgs)
			throw new Exception('Not enough arguments');

		if ($numArgs > 2)
			throw new Exception('Too many arguments');

		$fieldNameOrPrepared = $args[0];
		$valueOrBinds = null;

		if ($numArgs > 1)
			$valueOrBinds = $args[1];

		$expressions = [];

		if (is_string($fieldNameOrPrepared)) {
			/*
			 * Агрументы либо (prepared, binds), либо (field, value)
			 */

			if (PlainSql::hasPlaceholders($fieldNameOrPrepared) || $numArgs === 1) {
				if ($numArgs == 1) {
					$valueOrBinds = [];
				} else {
					if (!is_array($valueOrBinds))
						throw new Exception('Binds must be an array');
				}

				$expressions[] = self::prepared($fieldNameOrPrepared, $valueOrBinds);
			} else {
				if ($numArgs !== 2)
					throw new Exception('Exactly 2 arguments expected');

				$expressions[] = self::fieldEqual($fieldNameOrPrepared, $valueOrBinds);
			}
		} else if ($fieldNameOrPrepared instanceof Literal) {
			/*
			 * Аргумент - литерал. Дополнительных аргументов не нужно
			 */

			if ($numArgs !== 1)
				throw new Exception('Two-arguments form is not allowed when Literal given');

			$expressions[] = $fieldNameOrPrepared;

		} else if (is_array($fieldNameOrPrepared)) {
			/*
			 * Аргумент - массив соответствий типа [field1 => value1, field2 => value2]
			 */

			if ($numArgs !== 1)
				throw new Exception('Exactly one argument expected');

			foreach ($fieldNameOrPrepared as $field => $value) {
				$expressions[] = self::fieldEqual($field, $value);
			}
		} else {
			throw new Exception('Incorrect expression definition');
		}

		return $expressions;
	}
}
