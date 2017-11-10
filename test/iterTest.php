<?php

namespace iter;

class IterTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideTestRange */
    public function testRange($start, $end, $step, $resultArray) {
        $this->assertSame($resultArray, toArray(range($start, $end, $step)));
    }

    public function provideTestRange() {
        return [
            [0, 10, null,  [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
            [0, 10, 2,  [0, 2, 4, 6, 8, 10]],
            [0, 3, 0.5, [0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0]],
            [10, 0, null, [10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]],
            [10, 0, -2, [10, 8, 6, 4, 2, 0]],
            [3, 0, -0.5, [3, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0]],
            [5, 5, 0, [5]]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage If start < end the step must be positive
     */
    public function testRangeStepMustBePositive() {
        toArray(range(0, 10, -1));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage If start > end the step must be negative
     */
    public function testRangeStepMustBeNegative() {
        toArray(range(10, 0, 1));
    }

    public function testMap() {
        $range = range(0, 5);
        $mapped = map(function($n) { return $n * 3; }, $range);
        $this->assertSame([0, 3, 6, 9, 12, 15], toArray($mapped));
    }

    public function testMapKeys() {
        $range = range(0, 5);
        $mapped = mapKeys(function($n) { return $n * 3; }, $range);
        $this->assertSame(
            [0 => 0, 3 => 1, 6 => 2, 9 => 3, 12 => 4, 15 => 5],
            toArrayWithKeys($mapped)
        );

        $mapped = mapKeys('strtolower', ['A' => 1, 'B' => 2, 'C' => 3]);
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            toArrayWithKeys($mapped)
        );
    }

    public function testFlatMap() {
        $this->assertSame(
            [-1, 1, -2, 2, -3, 3, -4, 4, -5, 5],
            toArray(flatMap(function($v) {
                return [-$v, $v];
            }, [1, 2, 3, 4, 5]))
        );
        $this->assertSame(
            [],
            toArray(flatMap(function() { return []; }, [1, 2, 3, 4, 5]))
        );
    }

    public function testReindex() {
        $iter = reindex('strtoupper', ['a', 'b', 'c', 'd', 'e']);
        $this->assertSame(
            ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'],
            toArrayWithKeys($iter)
        );

        $iter = reindex(fn\operator('*', 2), [1, 2, 3, 4]);
        $this->assertSame(
            [2 => 1, 4 => 2, 6 => 3, 8 => 4],
            toArrayWithKeys($iter)
        );
    }

    public function testApply() {
        $range = range(0, 5);
        $result = [];
        apply(function($n) use (&$result) { $result[] = $n; }, $range);

        $this->assertSame([0, 1, 2, 3, 4, 5], $result);
    }

    public function testFilter() {
        $range = range(-5, 5);
        $filtered = filter(function($n) { return $n < 0; }, $range);
        $this->assertSame([-5, -4, -3, -2, -1], toArray($filtered));
    }

    public function testEnumerate() {
         $this->assertSame([[0, 'a'], [1, 'b']], toArray(enumerate(['a', 'b'])));
    }

    public function testEnumerateWithStringKeys() {
        $enumerated = enumerate([
            'a' => 1,
            'b' => 2,
        ]);
        $this->assertSame([['a', 1], ['b', 2]], toArray($enumerated));
    }

    public function testZip() {
        $zipped = zip(range(0, 5), range(5, 0, -1));
        $this->assertSame([[0,5], [1,4], [2,3], [3,2], [4,1], [5,0]], toArray($zipped));
    }

    public function testZipEmpty() {
        $res = toArray(zip());
        $this->assertSame([], $res);
    }

    public function testZipKeyValue() {
        $zipped = zipKeyValue(range(5, 0, -1), range(0, 5));
        $this->assertSame([5=>0, 4=>1, 3=>2, 2=>3, 1=>4, 0=>5], toArrayWithKeys($zipped));
    }

    public function testChain() {
        $chained = chain(range(1, 3), range(4, 6), range(7, 9));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], toArray($chained));

        // empty chain
        $this->assertSame([], toArray(chain()));
    }

    public function testSlice() {
        $this->assertSame(
            [5, 6, 7, 8, 9],
            toArray(slice(range(0, INF), 5, 5))
        );
        $this->assertSame(
            [5, 6, 7, 8, 9],
            toArray(slice(range(0, 9), 5))
        );

        // empty slice
        $this->assertSame([], toArray(slice(range(0, INF), 0, 0)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Length must be non-negative
     */
    public function testSliceNegativeLengthError() {
        toArray(slice(range(0, INF), 0, -1));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Start offset must be non-negative
     */
    public function testSliceNegativeStartOffsetError() {
        toArray(slice(range(0, INF), -1, 5));
    }

    public function testTakeDrop() {
        $this->assertSame([1, 2, 3], toArray(take(3, [1, 2, 3, 4, 5])));
        $this->assertSame([4, 5], toArray(drop(3, [1, 2, 3, 4, 5])));
        $this->assertSame([], toArray(take(3, [])));
        $this->assertSame([], toArray(drop(3, [])));
    }

    public function testRepeat() {
        $this->assertSame([1, 1, 1, 1, 1], toArray(repeat(1, 5)));
        $this->assertSame([], toArray(repeat(1, 0)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Number of repetitions must be non-negative
     */
    public function testRepeatNegativeNumError() {
        toArray(repeat(1, -1));
    }

    public function testKeyValue() {
        $array = ['a' => 'b', 'c' => 'd', 'e' => 'f'];
        $this->assertSame(['b', 'd', 'f'], toArrayWithKeys(values($array)));
        $this->assertSame(['a', 'c', 'e'], toArrayWithKeys(keys($array)));
    }

    public function testReduce() {
        $this->assertSame(15, reduce(fn\operator('+'), range(1, 5), 0));
        $this->assertSame(120, reduce(fn\operator('*'), range(1, 5), 1));
    }

    public function testComplexReduce() {
        $this->assertSame('abcdef', reduce(function ($acc, $value, $key) {
            return $acc . $key . $value;
        }, ['a' => 'b', 'c' => 'd', 'e' => 'f'], ''));
    }

    public function testReductions() {
        $this->assertSame(
            [1, 3, 6, 10, 15],
            toArrayWithKeys(reductions(fn\operator('+'), range(1, 5), 0))
        );
        $this->assertSame(
            [1, 2, 6, 24, 120],
            toArrayWithKeys(reductions(fn\operator('*'), range(1, 5), 1))
        );
    }

    public function testComplexReductions() {
        $this->assertSame(
            ['ab', 'abcd', 'abcdef'],
            toArrayWithKeys(reductions(function ($acc, $value, $key) {
                return $acc . $key . $value;
            }, ['a' => 'b', 'c' => 'd', 'e' => 'f'], ''))
        );
    }

    public function testAnyAll() {
        $this->assertTrue(all(fn\operator('>', 0), range(1, 10)));
        $this->assertFalse(all(fn\operator('>', 0), range(-5, 5)));
        $this->assertTrue(any(fn\operator('>', 0), range(-5, 5)));
        $this->assertFalse(any(fn\operator('>', 0), range(-10, 0)));
    }

    public function testSearch() {
        $iter = new \ArrayIterator(['foo', 'bar', 'baz']);
        $this->assertSame('baz', search(fn\operator('===', 'baz'), $iter));

        $iter = new \ArrayIterator(['foo', 'bar', 'baz']);
        $this->assertSame(null, search(fn\operator('===', 'qux'), $iter));

        $iter = new \ArrayIterator([]);
        $this->assertSame(null, search(fn\operator('===', 'qux'), $iter));
    }

    public function testTakeOrDropWhile() {
        $this->assertSame(
            [3, 1, 4],
            toArray(takeWhile(fn\operator('>', 0), [3, 1, 4, -1, 5]))
        );
        $this->assertSame(
            [-1, 5],
            toArray(dropWhile(fn\operator('>', 0), [3, 1, 4, -1, 5]))
        );
        $this->assertSame(
            [1, 2, 3],
            toArray(takeWhile(fn\operator('>', 0), [1, 2, 3]))
        );
        $this->assertSame(
            [],
            toArray(dropWhile(fn\operator('>', 0), [1, 2, 3]))
        );
    }

    public function testFlatten() {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, 2, 3, 4, 5]))
        );
        $this->assertSame(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, [2, 3], 4, [], 5]))
        );
        $this->assertSame(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, [[2, 3], 4], 5]))
        );
        $this->assertSame(
            [1, 2, 3, 4, 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5]))
        );
        $this->assertSame(
            [1, 2, 3, 4, 5],
            toArray(flatten(new \ArrayIterator([
                new \ArrayIterator([1, 2]),
                3,
                new \ArrayIterator([4, 5]),
            ])))
        );

        // Test key preservation
        $this->assertSame(
            ['a' => 1, 'c' => 2, 'd' => 3],
            toArrayWithKeys(flatten(['a' => 1, 'b' => ['c' => 2, 'd' => 3]]))
        );
    }

    public function testFlattenLevels() {
        $this->assertSame(
            [[1, [[2, [[]], 3], 4]], 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5], 0))
        );
        $this->assertSame(
            [1, [[2, [[]], 3], 4], 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5], 1))
        );
        $this->assertSame(
            [1, [2, [[]], 3], 4, 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5], 2))
        );
        $this->assertSame(
            [1, 2, [[]], 3, 4, 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5], 3))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Number of levels must be non-negative
     */
    public function testFlattenNegativeLevelError() {
        toArray(flatten([1, 2, 3], -1));
    }

    public function testToIter() {
        $iter = new \ArrayIterator([1, 2, 3]);
        $this->assertSame($iter, toIter($iter));

        $iter = toIter(new \ArrayObject([1, 2, 3]));
        $this->assertInstanceOf('Iterator', $iter);
        $this->assertSame([1, 2, 3], toArray($iter));

        $iter = toIter([1, 2, 3]);
        $this->assertInstanceOf('ArrayIterator', $iter);
        $this->assertSame([1, 2, 3], toArray($iter));
    }

    public function testCount() {
        $this->assertSame(5, count([1, 2, 3, 4, 5]));
        $this->assertSame(5, count(toIter([1, 2, 3, 4, 5])));
        $this->assertSame(42, count(new _CountableTestDummy));
    }

    public function testIsEmpty() {
        $this->assertTrue(isEmpty([]));
        $this->assertFalse(isEmpty([null]));
        $this->assertTrue(isEmpty(toArray([])));
        $this->assertFalse(isEmpty(toArray([null])));
        $this->assertTrue(isEmpty(repeat(42, 0)));
        $this->assertFalse(isEmpty(repeat(42)));
    }

    public function testToArray() {
        $this->assertSame([1, 2, 3], toArray(['a' => 1, 'b' => 2, 'c' => 3]));
        $this->assertSame(
            [1, 2, 3],
            toArray(new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]))
        );
        $this->assertSame(
            [1, 2, 3],
            toArray(chain(['a' => 1, 'b' => 2], ['a' => 3]))
        );
    }

    public function testToArrayWithKeys() {
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            toArrayWithKeys(['a' => 1, 'b' => 2, 'c' => 3])
        );
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            toArrayWithKeys(new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]))
        );
        $this->assertSame(
            ['a' => 3, 'b' => 2],
            toArrayWithKeys(chain(['a' => 1, 'b' => 2], ['a' => 3]))
        );
    }


    public function testFlip() {
        $this->assertSame(
            [1 => 'a', 2 => 'b', 3 => 'c'],
            toArrayWithKeys(flip(['a' => 1, 'b' => 2, 'c' => 3]))
        );
    }

    public function testJoin() {
        $this->assertSame('', join(', ', []));
        $this->assertSame(
            'a, b, c',
            join(', ', new \ArrayIterator(['a', 'b', 'c']))
        );
    }

    public function testChunk() {
        $iterable = new \ArrayIterator(
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]
        );

        $this->assertSame(
            [['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4], ['e' => 5]],
            toArray(chunk($iterable, 2))
        );
        $this->assertSame(
            [[1, 2], [3, 4], [5]],
            toArray(chunk($iterable, 2, false))
        );

        $this->assertSame(
            [[0=>0, 1=>1], [2=>2, 3=>3]],
            toArray(chunk([0, 1, 2, 3], 2))
        );
        $this->assertSame(
            [[0, 1], [2, 3]],
            toArray(chunk([0, 1, 2, 3], 2, false))
        );

        $this->assertSame([[0, 1, 2]], toArray(chunk([0, 1, 2], 100000)));
        $this->assertSame([], toArray(chunk([], 100000)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Chunk size must be positive
     */
    public function testZeroChunkSizeError() {
        toArray(chunk([1, 2, 3], 0));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Chunk size must be positive
     */
    public function testNegativeChunkSizeError() {
        toArray(chunk([1, 2, 3], -1));
    }

    public function testProduct() {
        $this->assertKeysValues([[]], [[]], function() { return product(); });

        $this->assertKeysValues(
            [[0],[1]], [[1],[2]], function() { return product([1,2]); });

        $this->assertKeysValues(
            [[0,0],[0,1],[1,0],[1,1]],
            [[1,3],[1,4],[2,3],[2,4]],
            function() { return product([1,2],[3,4]); });

        $this->assertKeysValues(
            [[0,0,0],[0,0,1],[0,1,0],[0,1,1],[1,0,0],[1,0,1],[1,1,0],[1,1,1]],
            [[1,1,1],[1,1,2],[1,2,1],[1,2,2],[2,1,1],[2,1,2],[2,2,1],[2,2,2]],
            function() {
                return product(range(1,2), [1,2], new \ArrayIterator([1,2]));
            }
        );
    }

    function testRecurse() {
        $iter = new \ArrayIterator(['a' => 1, 'b' => 2,
            'c' => new \ArrayIterator(['d' => 3, 'e' => 4])]);

        $this->assertSame(
            [1, 2, [3, 4]],
            recurse('iter\toArray', $iter)
        );

        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4]],
            recurse('iter\toArrayWithKeys', $iter)
        );
    }

    private function assertKeysValues(array $keys, array $values, callable $fn) {
        $this->assertSame($keys, toArray(keys($fn())));
        $this->assertSame($values, toArray(values($fn())));
    }

    public function testIsIterable() {
        $this->assertTrue(isIterable([]));
        $this->assertTrue(isIterable([1, 2, 3]));
        $this->assertTrue(isIterable(new \ArrayIterator([1, 2, 3])));
        $gen = function() { yield; };
        $this->assertTrue(isIterable($gen()));

        $this->assertFalse(isIterable(new \stdClass()));
        $this->assertFalse(isIterable("foobar"));
        $this->assertFalse(isIterable(123));
    }

    /** @dataProvider provideTestAssertIterableFails */
    public function testAssertIterableFails(callable $fn, $expectedMessage) {
        $this->setExpectedException(
            \InvalidArgumentException::class, $expectedMessage);
        $ret = $fn();

        // For generators the body will not be run until the first operation
        if ($ret instanceof \Generator) {
            $ret->rewind();
        }
    }

    public function provideTestAssertIterableFails() {
        yield [
            function() { _assertIterable(new \stdClass(), 'Argument'); },
            'Argument must be iterable'
        ];
        yield [
            function() { _assertIterable("foobar", 'Argument'); },
            'Argument must be iterable'
        ];
        yield [
            function() { _assertAllIterable([[], new \stdClass()]); },
            'Argument 2 must be iterable'
        ];
        yield [
            function() { return count(new \stdClass()); },
            'Argument must be iterable or implement Countable'
        ];
        yield [
            function() { return isEmpty(new \stdClass()); },
            'Argument must be iterable or implement Countable'
        ];
        yield [
            function() { return toIter(new \stdClass()); },
            'Argument must be iterable'
        ];
        yield [
            function() {
                return map(function($v) { return $v; }, new \stdClass());
            },
            'Second argument must be iterable'
        ];
        yield [
            function() {
                return chain([1], [2], new \stdClass());
            },
            'Argument 3 must be iterable'
        ];
        yield [
            function() {
                return zip([1], [2], new \stdClass());
            },
            'Argument 3 must be iterable'
        ];
    }
}

class _CountableTestDummy implements \Countable {
    public function count() {
        return 42;
    }
}
