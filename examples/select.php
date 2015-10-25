<?php
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/../tests/mock/Quote.php';
$quote = new b2\tests\mock\Quote();

$b2 = new b2\B2;

echo $b2->select('user', 'id > ?', [10])
	->field('uid')
	->field('name')
	->field('xp')
	->toString($quote)
. "\n";

echo $b2->select('user', 'id = ?', [10])
	->leftJoin('payment', 'payment.id = user.id')
	->field($b2->sql('SUM(payment.price * ?)', [10]), 'sum')
	->toString($quote)
. "\n";

$mysql = new mysqli(/* ... */);

$selectObject = $b2->select('user', 'id > ?', [10])
	->leftJoin('payment', 'payment.id = user.id')
	->fields(['user.id', 'sum' => $b2->sql('SUM(payment.value)')])
	->orderBy('sum', 'DESC');

$selectSql = $selectObject->toString($quote);

echo $selectSql . "\n";
