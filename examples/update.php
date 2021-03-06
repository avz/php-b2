<?php
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/../tests/mock/Quote.php';
$quote = new b2\tests\mock\Quote();

$b2 = new b2\B2();

echo $b2->update('user')->set('money', 10)->where('id', 1)->toString($quote), "\n";
echo $b2->update('user')->set(['money' => 10])->where(['id' => 1])->toString($quote), "\n";
echo $b2->update('user')->set('`money` = ?', [10])->where('`id` = ?', [1])->toString($quote), "\n";
echo $b2->update('user')->set('`money` = :money', [':money' => 10])->where('`id` = :id', [':id' => 1])->toString($quote), "\n";
echo $b2->update('user')->set("`money` = '10'")->where("`id` = '1'")->toString($quote), "\n";

echo $b2->update('user')
		->set('`money` = `money` - ?', [20])
		->set(['vip' => 1])
		->set(['bannedUntil' => null])
		->set($b2->sql('field = ?', [10]))
		->where('id', 2)
		->where('`id` OR `uid`')
	->toString($quote)
. "\n";
