<?php
namespace b2\tests;

class ExamplesTest extends \b2\tests\Base
{
	public function testSelect()
	{
		$this->expectOutputString(<<<EOF
SELECT `uid`, `name`, `xp` FROM `user` WHERE id > '10'
SELECT SUM(payment.price * '10') AS `sum` FROM `user` LEFT JOIN `payment` ON payment.id = user.id WHERE id = '10'
SELECT `user`.`id`, SUM(payment.value) AS `sum` FROM `user` LEFT JOIN `payment` ON payment.id = user.id WHERE id > '10' ORDER BY `sum` DESC

EOF
		);
		require_once __DIR__ . '/../examples/select.php';
	}

	public function testUpdate()
	{
		$this->expectOutputString(<<<EOF
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = `money` - '20', `vip` = '1', `bannedUntil` = NULL, field = '10' WHERE (`id` = '2') AND (`id` OR `uid`)

EOF
		);
		require_once __DIR__ . '/../examples/update.php';
	}

	public function testInsert()
	{
		$this->expectOutputString(<<<EOF
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John'), ('2', 'Ivan')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John'), ('2', 'Ivan')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John'), ('2', 'Ivan')
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John') ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `name` = VALUES(`name`)
INSERT INTO `user`(`age`, `id`, `name`) VALUES ('20', '1', 'John') ON DUPLICATE KEY UPDATE `age` = VALUES(`age`), `name` = VALUES(`name`)
INSERT INTO `user`(`age`, `id`, `name`) VALUES ('20', '1', 'John') ON DUPLICATE KEY UPDATE `age` = VALUES(`age`), `name` = VALUES(`name`)
INSERT INTO `counter`(`count`, `name`) VALUES ('1', 'visits') ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)

EOF
		);
		require_once __DIR__ . '/../examples/insert.php';
	}

	public function testDelete()
	{
		$this->expectOutputString(<<<EOF
DELETE FROM `user` WHERE `id` = '10'
DELETE FROM `user` WHERE `id` = '10'

EOF
		);
		require_once __DIR__ . '/../examples/delete.php';
	}
}
