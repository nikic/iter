Iteration primitives using generators
=====================================

This library implements iteration primitives like `map()` and `filter()`
using generators. To a large part this serves as a repository for small
examples of generator usage, but of course the functions are also practically
quite useful.

All functions in this library accept arbitrary iterables, i.e. arrays,
traversables, iterators and aggregates, which makes it quite different from
functions like `array_map()` (which only accept arrays) and the SPL iterators
(which usually only accept iterators, not even aggregates). The operations are
of course lazy.

Functionality
-------------

A small usage example for the ``map()`` and ``range()`` functions:

```php
<?php

use iter, iter\fn;

// require 'path/to/src/bootstrap.php'; has to happen somewhere

$nums = iter\range(1, 10);
$numsTimesTen = iter\map(fn\operator('*', 10), $nums);
// => iter(10, 20, 30, 40, 50, 60, 70, 80, 90, 100)
```

You can find documentation and usage examples for the individual functions in
[iter.php](https://github.com/nikic/iter/blob/master/src/iter.php), here I only
list the function signatures as an overview:

    Iterator range(number $start, number $end, number $step = null)
    Iterator map(callable $function, iterable $iterable)
    void     apply(callable $function, iterable $iterable)
    Iterator filter(callable $predicate, iterable $iterable)
    mixed    reduce(callable $function, iterable $iterable, mixed $startValue = null)
    Iterator zip(iterable... $iterables)
    Iterator zipKeyValue(iterable $keys, iterable $values)
    Iterator chain(iterable... $iterables)
    Iterator slice(iterable $iterable, int $start, int $length = INF)
    Iterator take(int $num, iterable $iterable)
    Iterator drop(int $num, iterable $iterable)
    Iterator repeat(mixed $value, int $num = INF)
    Iterator keys(iterable $iterable)
    Iterator values(iterable $iterable)
    bool     any(callable $predicate, iterable $iterable)
    bool     all(callable $predicate, iterable $iterable)
    Iterator takeWhile(callable $predicate, iterable $iterable)
    Iterator dropWhile(callable $predicate, iterable $iterable)
    Iterator flatten(iterable $iterable)
    int      count(iterable $iterable)
    Iterator toIter(iterable $iterable)
    array    toArray(iterable $iterable)
    array    toArrayWithKeys(iterable $iterable)
    Iterator flip(iterable $iterable)

As the functionality is implemented using generators the resulting iterators
are by default not rewindable. This library implements additional functionality
to allow creating rewindable generators.

You can find documentation for this in [iter.rewindable.php](https://github.com/nikic/iter/blob/master/src/iter.rewindable.php),
here is just a small usage example of the two main functions:

```php
<?php

use iter, iter\fn;

// require 'path/to/src/bootstrap.php';

/* Create a rewindable map function which can be used multiple times */
$rewindableMap = iter\makeRewindable('iter\\map');
$res = $rewindableMap(fn\operator('*', 3), [1, 2, 3]);

/* Do a rewindable call to map, just once */
$res = iter\callRewindable('iter\\map', fn\operator('*', 3), [1, 2, 3]);
```

The above functions are only useful for your own generators though, for the
`iter` generators rewindable variants are directly provided with an
`iter\rewindable` prefix:

    $res = iter\rewindable\map(fn\operator('*', 3), [1, 2, 3]);
    // etc