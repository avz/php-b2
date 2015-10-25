# b2 - PHP SQL Query Builder for MySQL

inspired by https://github.com/lichtner/fluentpdo

## Why are we doing this again?

"Just for Fun", "NIH", "I can do better" ... choose any.

## Table of Contents

 - [Queries](#queries)
   - [`SELECT`](#select)
   - [`UPDATE`](#update)
   - [`INSERT`](#insert)
   - [`DELETE`](#delete)
 - [Filtering](#filtering)
 - [Binds (similar to prepared statements)](#binding)

## Simple example

```php
require_once "b2/autoload.php";

$mysql = new mysqli(/* ... */);
$quote = \b2\Quote::createFromMysqli($mysql);

$b2 = new \b2\B2;

$selectObject = $b2->select('user', 'id > ?', [10])
	->leftJoin('payment', 'payment.id = user.id')
	->fields(['user.id', 'sum' => $b2->sql('SUM(payment.value)')])
	->orderBy('sum', 'DESC')
;

$selectSql = $selectObject->toString($quote);

echo $selectSql;
```

Output (added formatting for easy reading)

```sql
SELECT `user`.`id`, SUM(payment.value) AS `sum`
FROM `user`
LEFT JOIN `payment` ON payment.id = user.id
WHERE id > '10' ORDER BY `sum` DESC
```

## Queries

### `SELECT`

#### Filters

No filters
```php
$b2->select('user')->allFields();
```
```sql
SELECT * FROM `user`
```

Select by filter
```php
$b2->select('user', 'id', 10)->allFields();

// or
$b2->select('user', 'id = ?', [10])->allFields();

// or
$b2->select('user', ['id' => 10])->allFields();

// or use explicit where(). See below
$b2->select('user')->where('id', 10)->allFields();
```
All of them SQL
```sql
SELECT * FROM `user` WHERE `id` = '10'
```

#### Fields to select
```php
$b2->select('user')->fields(['id', 'alias' => 'name']);

// or
$b2->select('user')->field('id')->field('name', 'alias');
```
```sql
SELECT `id`, `name` AS `alias` FROM `user`
```

#### `GROUP BY`

```php
$b2->select('user')->field('id')->groupBy('level');
```
```sql
SELECT `id` FROM `user` GROUP BY `level`
```

Group by multiple columns
```php
$b2->select('user')->field('id')
	->groupBy('level')
	->groupBy('bYear')
;
```
```sql
SELECT `id` FROM `user` GROUP BY `level`, `bYear`
```

#### `ORDER BY`
```php
$b2->select('user')->field('id')->orderBy('level', 'DESC');
```
```sql
SELECT `id` FROM `user` ORDER BY `level` DESC
```

Group by multiple columns
```php
$b2->select('user')->field('id')
	->orderBy('level', 'DESC')
	->orderBy('bYear')
;
```
```sql
SELECT `id` FROM `user` ORDER BY `level` DESC, `bYear`
```

#### `HAVING`

Not implemented yet :(

#### `LIMIT` and `OFFSET`

```php
$b2->select('user')->field('id')->limit(10)->offset(20);
```
```sql
SELECT `id` FROM `user` LIMIT 10 OFFSET 20
```

### `UPDATE`

```php
$b2->update('user')->set('money', 10)->where('id', 1);

// or
$b2->update('user')->set(['money' => 10])->where(['id' => 1]);

// or
$b2->update('user')->set('`money` = ?', [10])->where('`id` = ?', [1]);

// or
$b2->update('user')
	->set('`money` = :money', [':money' => 10])
	->where('`id` = :id', [':id' => 1])
;

// or
$b2->update('user')->set("`money` = '10'")->where("`id` = '1'");
```
```sql
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
```

#### `ORDER BY`, `LIMIT`, `OFFSET`

See [`SELECT`](#select) section

### `INSERT`

```php
$b2->insert('user', [['id' => 1, 'name' => 'John']]);

// or
$b2->insert('user')
	->row(['id' => 1, 'name' => 'John'])
;

// or
$b2->insert('user')
	->rows([['id' => 1, 'name' => 'John']])
;
```
```sql
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John')
```

Multi-rows
```php
$b2->insert('user', [
	['id' => 1, 'name' => 'John'],
	['id' => 2, 'name' => 'Ivan']
]);

// or
$b2->insert('user')
	->row(['id' => 1, 'name' => 'John'])
	->row(['id' => 2, 'name' => 'Ivan'])
;

// or
$b2->insert('user')
	->rows([
		['id' => 1, 'name' => 'John']
		, ['id' => 2, 'name' => 'Ivan']
	])
;
```
```sql
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John'), ('2', 'Ivan')
```

#### `ON DUPLICATE KEY UPDATE`

Update all inserting fields
```php
$b2->insert('user', [['id' => 1, 'name' => 'John']])
	->onDuplicateKeyUpdate()
;
```
```sql
INSERT INTO `user`(`id`, `name`) VALUES ('1', 'John')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `name` = VALUES(`name`)
```

Update specified fields
```php
$b2->insert('user', [['id' => 1, 'name' => 'John', 'age' => 20]])
	->onDuplicateKeyUpdate(['name', 'age'])
;

// or
$b2->insert('user', [['id' => 1, 'name' => 'John', 'age' => 20]])
	->onDuplicateKeyUpdate('name')
	->onDuplicateKeyUpdate('age')
;
```
```sql
INSERT INTO `user`(`age`, `id`, `name`) VALUES ('20', '1', 'John')
ON DUPLICATE KEY UPDATE `age` = VALUES(`age`), `name` = VALUES(`name`)
```

Update with expression

```php
$b2->insert('counter')->row(['name' => 'visits', 'count' => 1])
	->onDuplicateKeyUpdate([
		'count' => $b2->sql('`count` + VALUES(`count`)')
	])
;
```
```sql
INSERT INTO `counter`(`count`, `name`) VALUES ('1', 'visits')
ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)
```
### `DELETE`

```php
$b2->delete('user', 'id', 10);

//or
$b2->delete('user')->where('id', 10);
```
```sql
DELETE FROM `user` WHERE `id` = '10'
```

#### `ORDER BY`, `LIMIT`, `OFFSET`

See [`SELECT`](#select) section

## Filtering

### Simple
```php
$select->where('key', 'value');

// or
$select->where(['key' => 'value']);

//or
$select->where('`key` = ?', ['value']);
```
```sql
WHERE `key` = 'value'
```

### `AND`

```php
$select
	->where('key1', 'value1')
	->where('key2', 'value2')
;

// or
$select
	->where(['key1' => 'value1', 'key2' => 'value2'])
;
```
```sql
WHERE `key1` = 'value1' AND `key2` = 'value2'
```

### `OR`
```php
$select->where($b2->sql('key1 = ? OR key2 = ?', [10, 20]));
```
```sql
WHERE key1 = '10' OR key2 = '20'
```

## Binds

### Unnamed
```php
$b2->where('id = ? OR id = ?', [1, 2]);
```
### Named
```php
$b2->where('id = :id1 OR id = :id2', [':id1' => 1, ':id2' => 2]);
```

### Lists
```php
$b2->where('id IN(:ids)', [':ids' => [1, 2, 3]]);
```
```sql
id IN('1', '2', '3')
```
