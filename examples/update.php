<?php
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/../tests/mock/Quote.php';
$quote = new d2\tests\mock\Quote();

$d2 = new d2\D2();

echo $d2->update('user')->set('money', 10)->where('id', 1)->toString($quote), "\n";
echo $d2->update('user')->set(['money' => 10])->where(['id' => 1])->toString($quote), "\n";
echo $d2->update('user')->set('`money` = ?', [10])->where('`id` = ?', [1])->toString($quote), "\n";
echo $d2->update('user')->set('`money` = :money', [':money' => 10])->where('`id` = :id', [':id' => 1])->toString($quote), "\n";
echo $d2->update('user')->set("`money` = '10'")->where("`id` = '1'")->toString($quote), "\n";

echo $d2->update('user')
		->set('`money` = `money` - ?', [20])
		->set(['vip' => 1])
		->set(['bannedUntil' => null])
		->where('id', 2)
	->toString($quote)
, "\n";
