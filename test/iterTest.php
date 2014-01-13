<?php

namespace iter;

class Aggregate implements \IteratorAggregate {
    public function getIterator() {
        return new \ArrayIterator([1, 2, 3]);
    }
}

class IterTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideTestRange */
    public function testRange($start, $end, $step, $resultArray) {
        $this->assertEquals($resultArray, toArray(range($start, $end, $step)));
    }

    public function provideTestRange() {
        return [
            [0, 10, null,  [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
            [0, 10, 2,  [0, 2, 4, 6, 8, 10]],
            [0, 3, 0.5, [0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0]],
            [10, 0, null, [10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]],
            [10, 0, -2, [10, 8, 6, 4, 2, 0]],
            [3, 0, -0.5, [3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0]],
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
        $this->assertEquals([0, 3, 6, 9, 12, 15], toArray($mapped));
    }

    public function testApply() {
        $range = range(0, 5);
        $result = [];
        apply(function($n) use (&$result) { $result[] = $n; }, $range);

        $this->assertEquals([0, 1, 2, 3, 4, 5], $result);
    }

    public function testFilter() {
        $range = range(-5, 5);
        $filtered = filter(function($n) { return $n < 0; }, $range);
        $this->assertEquals([-5, -4, -3, -2, -1], toArray($filtered));
    }

    public function testZip() {
        $zipped = zip(range(0, 5), range(5, 0, -1));
        $this->assertEquals([[0,5], [1,4], [2,3], [3,2], [4,1], [5,0]], toArray($zipped));
    }

    public function testZipKeyValue() {
        $zipped = zipKeyValue(range(5, 0, -1), range(0, 5));
        $this->assertEquals([5=>0, 4=>1, 3=>2, 2=>3, 1=>4, 0=>5], toArrayWithKeys($zipped));
    }

    public function testChain() {
        $chained = chain(range(1, 3), range(4, 6), range(7, 9));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], toArray($chained));
    }

    public function testSlice() {
        $this->assertEquals(
            [5, 6, 7, 8, 9],
            toArray(slice(range(0, INF), 5, 5))
        );
        $this->assertEquals(
            [5, 6, 7, 8, 9],
            toArray(slice(range(0, 9), 5))
        );
    }

    public function testTakeDrop() {
        $this->assertEquals([1, 2, 3], toArray(take(3, [1, 2, 3, 4, 5])));
        $this->assertEquals([4, 5], toArray(drop(3, [1, 2, 3, 4, 5])));
        $this->assertEquals([], toArray(take(3, [])));
        $this->assertEquals([], toArray(drop(3, [])));
    }

    public function testRepeat() {
        $this->assertEquals([1, 1, 1, 1, 1], toArray(repeat(1, 5)));
        $this->assertEquals([], toArray(repeat(1, 0)));
    }

    public function testKeyValue() {
        $array = ['a' => 'b', 'c' => 'd', 'e' => 'f'];
        $this->assertEquals(['b', 'd', 'f'], toArrayWithKeys(values($array)));
        $this->assertEquals(['a', 'c', 'e'], toArrayWithKeys(keys($array)));
    }

    public function testReduce() {
        $this->assertEquals(15, reduce(fn\operator('+'), range(1, 5), 0));
        $this->assertEquals(120, reduce(fn\operator('*'), range(1, 5), 1));
    }

    public function testAnyAll() {
        $this->assertEquals(true, all(fn\operator('>', 0), range(1, 10)));
        $this->assertEquals(false, all(fn\operator('>', 0), range(-5, 5)));
        $this->assertEquals(true, any(fn\operator('>', 0), range(-5, 5)));
        $this->assertEquals(false, any(fn\operator('>', 0), range(-10, 0)));
    }

    public function testTakeOrDropWhile() {
        $this->assertEquals(
            [3, 1, 4],
            toArray(takeWhile(fn\operator('>', 0), [3, 1, 4, -1, 5]))
        );
        $this->assertEquals(
            [-1, 5],
            toArray(dropWhile(fn\operator('>', 0), [3, 1, 4, -1, 5]))
        );
        $this->assertEquals(
            [1, 2, 3],
            toArray(takeWhile(fn\operator('>', 0), [1, 2, 3]))
        );
        $this->assertEquals(
            [],
            toArray(dropWhile(fn\operator('>', 0), [1, 2, 3]))
        );
    }

    public function testFlatten() {
        $this->assertEquals(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, 2, 3, 4, 5]))
        );
        $this->assertEquals(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, [2, 3], 4, [], 5]))
        );
        $this->assertEquals(
            [1, 2, 3, 4, 5],
            toArray(flatten([1, [[2, 3], 4], 5]))
        );
        $this->assertEquals(
            [1, 2, 3, 4, 5],
            toArray(flatten([[1, [[2, [[]], 3], 4]], 5]))
        );
        $this->assertEquals(
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
        $this->assertEquals([1, 2, 3], toArray($iter));
    }

    public function testCount() {
        $this->assertEquals(5, count([1, 2, 3, 4, 5]));
        $this->assertEquals(5, count(toIter([1, 2, 3, 4, 5])));
    }

    public function testReindex() {
        $keyFn = function($value) {
            return strtoupper($value);
        };
        $iter = reindex(["a", "b", "c", "d", "e"], $keyFn);
        $expected = ["A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e"];
        $this->assertEquals($expected, toArrayWithKeys($iter));

        $keyFn = function($value) {
            return $value * 2;
        };
        $iter = reindex([1, 2, 3, 4], $keyFn);
        $expected = [2 => 1, 4 => 2, 6 => 3, 8 => 4];
        $this->assertEquals($expected, toArrayWithKeys($iter));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage keyFn did not return a valid key
     */
    public function testReindexKeyMustBeScalar()
    {
        $keyFn = function($value) {
            return array();
        };

        $iter = reindex([1, 2, 3], $keyFn);
        toArrayWithKeys($iter);
    }
}