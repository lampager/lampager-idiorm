<p align="center">
<img width="320" alt="lampager-idiorm" src="https://user-images.githubusercontent.com/1351893/32459320-948766b8-c372-11e7-84b9-061ffa6233ba.png">
</p>
<p align="center">
<a href="https://travis-ci.org/lampager/lampager-idiorm"><img src="https://travis-ci.org/lampager/lampager-idiorm.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/lampager/lampager-idiorm?branch=master"><img src="https://coveralls.io/repos/github/lampager/lampager-idiorm/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://scrutinizer-ci.com/g/lampager/lampager-idiorm/?branch=master"><img src="https://scrutinizer-ci.com/g/lampager/lampager-idiorm/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
</p>

# Lampager for Idiorm and Paris

Rapid pagination without using OFFSET

## Requirements

- PHP: ^5.6 || ^7.0
- idiorm: ^5.4, paris: ^1.5
- [lampager/lampager](https://github.com/lampager/lampager): ^0.3

## Installing

```bash
composer require lampager/lampager-idiorm
```

## Basic Usage

You can wrap `ORM` instance with global function `lampager()` to make it chainable.

```php
$cursor = [
    'id' => 3,
    'created_at' => '2017-01-10 00:00:00',
    'updated_at' => '2017-01-20 00:00:00',
];

$result = lampager(ORM::for_table('posts')->where_equal('user_id', 1))
    ->forward()
    ->limit(5)
    ->order_by_desc('updated_at') // ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
    ->order_by_desc('created_at')
    ->order_by_desc('id')
    ->seekable()
    ->paginate($cursor)
    ->to_json(JSON_PRETTY_PRINT);
```

It will run the optimized query.


```sql
SELECT * FROM (

    SELECT * FROM `posts`
    WHERE `user_id` = 1
    AND (
        `updated_at` = '2017-01-20 00:00:00' AND `created_at` = '2017-01-10 00:00:00' AND `id` > 3
        OR
        `updated_at` = '2017-01-20 00:00:00' AND `created_at` > '2017-01-10 00:00:00'
        OR
        `updated_at` > '2017-01-20 00:00:00'
    )
    ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
    LIMIT 1

) `temporary_table`

UNION ALL

SELECT * FROM (

    SELECT * FROM `posts`
    WHERE `user_id` = 1
    AND (
        `updated_at` = '2017-01-20 00:00:00' AND `created_at` = '2017-01-10 00:00:00' AND `id` <= 3
        OR
        `updated_at` = '2017-01-20 00:00:00' AND `created_at` < '2017-01-10 00:00:00'
        OR
        `updated_at` < '2017-01-20 00:00:00'
    )
    ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
    LIMIT 6

) `temporary_table`
```

And you'll get


```json
{
  "records": [
    {
      "id": 3,
      "user_id": 1,
      "text": "foo",
      "created_at": "2017-01-10 00:00:00",
      "updated_at": "2017-01-20 00:00:00"
    },
    {
      "id": 5,
      "user_id": 1,
      "text": "bar",
      "created_at": "2017-01-05 00:00:00",
      "updated_at": "2017-01-20 00:00:00"
    },
    {
      "id": 4,
      "user_id": 1,
      "text": "baz",
      "created_at": "2017-01-05 00:00:00",
      "updated_at": "2017-01-20 00:00:00"
    },
    {
      "id": 2,
      "user_id": 1,
      "text": "qux",
      "created_at": "2017-01-17 00:00:00",
      "updated_at": "2017-01-18 00:00:00"
    },
    {
      "id": 1,
      "user_id": 1,
      "text": "quux",
      "created_at": "2017-01-16 00:00:00",
      "updated_at": "2017-01-18 00:00:00"
    }
  ],
  "has_previous": false,
  "previous_cursor": null,
  "has_next": true,
  "next_cursor": {
    "updated_at": "2017-01-18 00:00:00",
    "created_at": "2017-01-14 00:00:00",
    "id": 6
  }
}
```

## Classes

Note: See also [lampager/lampager](https://github.com/lampager/lampager).

| Name | Type | Parent Class | Description |
|:---|:---|:---|:---|
| Lampager\\Idiorm\\`Paginator` | Class | Lampager\\`Paginator` | Fluent factory implementation for Idiorm and Paris |
| Lampager\\Idiorm\\`Processor` | Class | Lampager\\`AbstractProcessor` | Processor implementation for Idiorm and Paris |
| Lampager\\Idiorm\\`PaginationResult` | Class | Lampager\\`PaginationResult` | PaginationResult implementation for Idiorm and Paris |

- All *camelCase* methods in `Paginator`, `Processor` and `PaginationResult` can be invoked by *snake_case* style.

## API

Note: See also [lampager/lampager](https://github.com/lampager/lampager).

### Paginator::__construct()<br>Paginator::create()

Create a new paginator instance.  
If you use global function `lampager()`, however, you don't need to directly instantiate.

```php
static Paginator create(\ORM|\ORMWrapper $builder): static
Paginator::__construct(\ORM|\ORMWrapper $builder)
```

### Paginator::transform()

Transform Lampager Query into Illuminate builder.

```php
Paginator::transform(\Lampager\Query $query): \ORM|\ORMWrapper
```

### Paginator::build()

Perform configure + transform.

```php
Paginator::build(\Lampager\Cursor|array $cursor = []): \ORM|\ORMWrapper
```

### Paginator::paginate()

Perform configure + transform + process.

```php
Paginator::paginate(\Lampager\Cursor|array $cursor = []): \Lampager\idiorm\PaginationResult
```

#### Arguments

- **`(mixed)`** __*$cursor*__<br> An associative array that contains `$column => $value` or an object that implements `\Lampager\Cursor`. It must be **all-or-nothing**.
  - For initial page, omit this parameter or pass empty array.
  - For subsequent pages, pass all parameters. Partial parameters are not allowd.

#### Return Value

e.g. 

```php
object(Lampager\Idiorm\PaginationResult)#1 (5) {
  ["records"]=>
  array(5) {
    [0]=>
    object(ORM)#2 (22) { ... }
    [1]=>
    object(ORM)#3 (22) { ... }
    [2]=>
    object(ORM)#4 (22) { ... }
    [3]=>
    object(ORM)#5 (22) { ... }
    [4]=>
    object(ORM)#6 (22) { ... }
  }
  ["hasPrevious"]=>
  bool(false)
  ["previousCursor"]=>
  NULL
  ["hasNext"]=>
  bool(true)
  ["nextCursor"]=>
  array(2) {
    ["updated_at"]=>
    string(19) "2017-01-18 00:00:00"
    ["created_at"]=>
    string(19) "2017-01-14 00:00:00"
    ["id"]=>
    int(6)
  }
}
```

### Paginator::useFormatter()<br>Paginator::restoreFormatter()<br>Paginator::process()

Invoke Processor methods.

```php
Paginator::useFormatter(\Lampager\Formatter|callable $formatter): $this
Paginator::restoreFormatter(): $this
Paginator::process(\Lampager\Query $query, array|\IdiormResultSet $rows): \Lampager\idiorm\PaginationResult
```

### PaginationResult::toArray()<br>PaginationResult::jsonSerialize()

Convert the object into array.

**IMPORTANT: *camelCase* properties are converted into *snake_case* form.**

```php
PaginationResult::toArray(): array
PaginationResult::jsonSerialize(): array
```

### PaginationResult::__call()

Call `IdiormResultSet` methods.

```php
PaginationResult::__call(string $name, array $args): mixed
```
