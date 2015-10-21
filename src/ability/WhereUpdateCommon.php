<?php
namespace b2\ability;

use b2\Literal;
use b2\literal\BiOperation;
use b2\literal\PlainSql;
use b2\literal\Constant;
use b2\literal\In;
use b2\Exception;
use b2\literal\Identifier;

abstract class WhereUpdateCommon {
	static private function prepared($prepared, array $binds = [])
	{
		$binds = array_map(
			function ($value) {
				return new Constant($value);
			},
			$binds
		);

		return new PlainSql($prepared, $binds);
	}

	static private function fieldEqual($columnName, $columnValue)
	{
		$e = null;

		if (is_array($columnValue)) {
			$cases = array_map(
				function ($value) {
					if ($value instanceof Literal)
						return $value;
					else
						return new Constant($value);
				},
				$columnValue
			);

			$e = new In(new Identifier($columnName), $cases);
		} else {
			$v = null;
			if ($columnValue instanceof Literal)
				$v = $columnValue;
			else
				$v = new Constant($columnValue);

			$e = new BiOperation(new Identifier($columnName), '=', $v);
		}

		return $e;
	}

	static public function extractExpressionsFromArgs(array $args)
	{
		$numArgs = sizeof($args);

		if (!$numArgs)
			throw new Exception('Not enough arguments');

		$fieldNameOrPrepared = $args[0];
		$valueOrBinds = null;

		if ($numArgs > 1)
			$valueOrBinds = $args[1];

		$expressions = [];

		if (is_string($fieldNameOrPrepared)) {
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

			if ($numArgs !== 1)
				throw new Exception('Two-arguments form is not allowed when Literal given');

			$expressions[] = $fieldNameOrPrepared;

		} else if (is_array($fieldNameOrPrepared)) {

			foreach ($fieldNameOrPrepared as $column => $value) {
				$expressions[] = self::fieldEqual($column, $value);
			}
		} else {
			throw new Exception('Incorrect expression definition');
		}

		return $expressions;
	}
}
