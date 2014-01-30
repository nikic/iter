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

    public function testZip() {
        $zipped = zip(range(0, 5), range(5, 0, -1));
        $this->assertSame([[0,5], [1,4], [2,3], [3,2], [4,1], [5,0]], toArray($zipped));
    }

    public function testZipKeyValue() {
        $zipped = zipKeyValue(range(5, 0, -1), range(0, 5));
        $this->assertSame([5=>0, 4=>1, 3=>2, 2=>3, 1=>4, 0=>5], toArrayWithKeys($zipped));
    }

    public function testChain() {
        $chained = chain(range(1, 3), range(4, 6), range(7, 9));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], toArray($chained));
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

    public function testKeyValue() {
        $array = ['a' => 'b', 'c' => 'd', 'e' => 'f'];
        $this->assertSame(['b', 'd', 'f'], toArrayWithKeys(values($array)));
        $this->assertSame(['a', 'c', 'e'], toArrayWithKeys(keys($array)));
    }

    public function testReduce() {
        $this->assertSame(15, reduce(fn\operator('+'), range(1, 5), 0));
        $this->assertSame(120, reduce(fn\operator('*'), range(1, 5), 1));
    }

    public function testAnyAll() {
        $this->assertTrue(all(fn\operator('>', 0), range(1, 10)));
        $this->assertFalse(all(fn\operator('>', 0), range(-5, 5)));
        $this->assertTrue(any(fn\operator('>', 0), range(-5, 5)));
        $this->assertFalse(any(fn\operator('>', 0), range(-10, 0)));
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
    }

    public function testToIter() {
        $iter = new \ArrayIterator([1, 2, 3]);
        $this->assertSame($iter, toIter($iter));

        $iter = toIter(new \ArrayObject([1, 2, 3]));
        $this->assertInstanceOf('Iterator', $iter);
        $this->assertSame([1, 2, 3], toArray($iter));

        $iter = toIter([1, 2, 3]);
        $this->assertInstanceOf('Iterator', $iter);
        $this->assertSame([1, 2, 3], toArray($iter));
    }

    public function testCount() {
        $this->assertSame(5, count([1, 2, 3, 4, 5]));
        $this->assertSame(5, count(toIter([1, 2, 3, 4, 5])));
        $this->assertSame(42, count(new _CountableTestDummy));
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
        $iterable = new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7]);

        $str = '';

        foreach (chunk($iterable, 2) as $chunk) {
            foreach ($chunk as $key => $value) {
                $str .= sprintf('%s:%s ', $key, $value);
            }
            $str .= "\n";
        }

        $this->assertEquals("a:1 b:2 \nc:3 d:4 \ne:5 f:6 \ng:7 \n", $str);
    }
}

class _CountableTestDummy implements \Countable {
    public function count() {
        return 42;
    }
}