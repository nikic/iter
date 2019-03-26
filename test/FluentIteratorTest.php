<?php


namespace iter;


use function iter\fn\method;
use function iter\fn\operator;
use PHPUnit\Framework\TestCase;

class FluentIteratorTest extends TestCase
{

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

    public function testMap() {
        $range = range(0, 5);
        $fluent = new FluentIterator($range);
        $mapped = $fluent->map(function($n) { return $n * 3; });
        $this->assertSame([0, 3, 6, 9, 12, 15], $mapped->toArray());
    }


    public function testMapKeys() {
        $range = range(0, 5);
        $fluent = new FluentIterator($range);
        $mapped = $fluent->mapKeys(function($n) { return $n * 3; });
        $this->assertSame(
            [0 => 0, 3 => 1, 6 => 2, 9 => 3, 12 => 4, 15 => 5],
            $mapped->toArrayWithKeys()
        );

        $mapped = (new FluentIterator(['A' => 1, 'B' => 2, 'C' => 3]))->mapKeys('strtolower');
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            $mapped->toArrayWithKeys()
        );
    }

    public function testFlatMap() {
        $this->assertSame(
            [-1, 1, -2, 2, -3, 3, -4, 4, -5, 5],
            (new FluentIterator([1, 2, 3, 4, 5]))->flatMap(function($v) {
                return [-$v, $v];
            })->toArray()
        );
        $this->assertSame(
            [],
            (new FluentIterator([1, 2, 3, 4, 5]))->flatMap(function() { return []; })->toArray()
        );
    }

    public function testReindex() {

        $iter = (new FluentIterator(['a', 'b', 'c', 'd', 'e']))->reindex('strtoupper');
        $this->assertSame(
            ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'],
            $iter->toArrayWithKeys()
        );

        $iter = reindex(fn\operator('*', 2), [1, 2, 3, 4]);
        $this->assertSame(
            [2 => 1, 4 => 2, 6 => 3, 8 => 4],
            toArrayWithKeys($iter)

        );
    }

    public function testApply() {
        $range = new FluentIterator(range(0, 5));
        $result = [];
        $range->apply(function($n) use (&$result) { $result[] = $n; });

        $this->assertSame([0, 1, 2, 3, 4, 5], $result);
    }

    public function testFilter() {
        $range = new FluentIterator(range(-5, 5));
        $filtered = $range->filter(function($n) { return $n < 0; });
        $this->assertSame([-5, -4, -3, -2, -1], $filtered->toArray());
    }

    public function testEnumerateIsAliasOfToPairs() {

        $this->assertSame((new FluentIterator(['a', 'b']))->toPairs()->toArray(),
            (new FluentIterator(['a', 'b']))->enumerate()->toArray());
    }

    public function testToPairs() {
        $this->assertSame([[0, 'a'], [1, 'b']], (new FluentIterator(['a', 'b']))->toPairs()->toArray());
    }

    public function testToPairsWithStringKeys() {
        $enumerated = (new FluentIterator([
            'a' => 1,
            'b' => 2,
        ]))->toPairs();
        $this->assertSame([['a', 1], ['b', 2]], $enumerated->toArray());
    }

    public function testFromPairs() {
        $this->assertSame(['a', 'b'], (new FluentIterator([[0, 'a'], [1, 'b']]))->fromPairs()->toArrayWithKeys());
    }

    public function testFromPairsInverseToPairs() {
        $map = new FluentIterator(['a' => 1, 'b' => 2]);
        $this->assertSame($map->toArrayWithKeys(), $map->toPairs()->fromPairs()->toArrayWithKeys());
    }

    public function testZipKeyValue() {
        $keys = new FluentIterator(range(5, 0, -1));
        $values = new FluentIterator(range(0, 5));

        $expected = [5=>0, 4=>1, 3=>2, 2=>3, 1=>4, 0=>5];

        $this->assertSame($expected, $keys->zipValues(range(0, 5))->toArrayWithKeys());
        $this->assertSame($expected, $values->zipKeys(range(5, 0, -1))->toArrayWithKeys());
    }

    public function testChain() {
        $chained = (new FluentIterator(range(1, 3)))
            ->chain((new FluentIterator(range(4, 6))))
            ->chain(range(7, 9));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], $chained->toArray());

    }

    public function testSlice() {
        $this->assertSame(
            [5, 6, 7, 8, 9],
            (new FluentIterator(range(0, INF)))->slice(5, 5)->toArray()
        );
        $this->assertSame(
            [5, 6, 7, 8, 9],
            (new FluentIterator(range(0, 9)))->slice(5)->toArray()
        );

        // empty slice
        $this->assertSame([], (new FluentIterator(range(0, INF)))->slice(0, 0)->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Length must be non-negative
     */
    public function testSliceNegativeLengthError() {
        (new FluentIterator(range(0, INF)))->slice(0, -1)->toArray();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Start offset must be non-negative
     */
    public function testSliceNegativeStartOffsetError() {
        (new FluentIterator(range(0, INF)))->slice(-1, 5)->toArray();
    }

    public function testTakeDrop() {

        $this->assertSame([1, 2, 3], (new FluentIterator([1, 2, 3, 4, 5]))->take(3)->toArray());
        $this->assertSame([4, 5], (new FluentIterator([1, 2, 3, 4, 5]))->drop(3)->toArray());
        $this->assertSame([], (new FluentIterator([]))->take(3)->toArray());
        $this->assertSame([], (new FluentIterator([]))->take(3)->toArray());
    }

    public function testKeyValue() {
        $this->assertSame(['b', 'd', 'f'], (new FluentIterator(['a' => 'b', 'c' => 'd', 'e' => 'f']))
            ->values()
            ->toArrayWithKeys()
        );
        $this->assertSame(['a', 'c', 'e'], (new FluentIterator(['a' => 'b', 'c' => 'd', 'e' => 'f']))
            ->keys()
            ->toArrayWithKeys()
        );
    }

    public function testReduce() {

        $this->assertSame(15, (new FluentIterator(range(1, 5)))->reduce(fn\operator('+'), 0));
        $this->assertSame(120, (new FluentIterator(range(1, 5)))->reduce(fn\operator('*'), 1));
    }

    public function testComplexReduce() {

        $iterator = new FluentIterator(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $this->assertSame('abcdef', $iterator->reduce(function ($acc, $value, $key) {
            return $acc . $key . $value;
        }, ''));
    }

    public function testReductions() {
        $this->assertSame(
            [1, 3, 6, 10, 15],
            (new FluentIterator(range(1, 5)))->reductions(fn\operator('+'), 0)->toArrayWithKeys()
        );
        $this->assertSame(
            [1, 2, 6, 24, 120],
            (new FluentIterator(range(1, 5)))->reductions(fn\operator('*'), 1)->toArrayWithKeys()
        );
    }

    public function testComplexReductions() {
        $iterator = new FluentIterator(['a' => 'b', 'c' => 'd', 'e' => 'f']);
        $this->assertSame(
            ['ab', 'abcd', 'abcdef'], $iterator->reductions(function ($acc, $value, $key) {
                return $acc . $key . $value;
            }, '')->toArrayWithKeys()
        );
    }

    public function testAnyAll() {
        $this->assertTrue((new FluentIterator(range(1, 10)))->all(fn\operator('>', 0)));
        $this->assertFalse((new FluentIterator(range(-5, 5)))->all(fn\operator('>', 0)));
        $this->assertTrue((new FluentIterator(range(5, 5)))->any(fn\operator('>', 0)));
        $this->assertFalse((new FluentIterator(range(-10, 0)))->any(fn\operator('>', 0)));
    }

    public function testSearch() {
        $iter = new FluentIterator(['foo', 'bar', 'baz']);
        $this->assertSame('baz', $iter->search(fn\operator('===', 'baz')));

        $iter = new FluentIterator(['foo', 'bar', 'baz']);
        $this->assertSame(null, $iter->search(fn\operator('===', 'qux')));

        $iter = new FluentIterator([]);
        $this->assertSame(null, $iter->search(fn\operator('===', 'qux')));
    }

    public function testTakeOrDropWhile() {

        $this->assertSame(
            [3, 1, 4],
            (new FluentIterator([3, 1, 4, -1, 5]))
                ->takeWhile(fn\operator('>', 0))
                ->toArray()
        );
        $this->assertSame(
            [-1, 5],
            (new FluentIterator([3, 1, 4, -1, 5]))
                ->dropWhile(fn\operator('>', 0))
                ->toArray()
        );
        $this->assertSame(
            [1, 2, 3],
            (new FluentIterator([1, 2, 3]))
                ->takeWhile(fn\operator('>', 0))
                ->toArray()
        );
        $this->assertSame(
            [],
            (new FluentIterator([1, 2, 3]))
                ->dropWhile(fn\operator('>', 0))
                ->toArray()
        );
    }

    public function flattenTestRange() {
        return [
            [[1, 2, 3, 4, 5]],
            [[[1, [2, 3], 4, [], 5]]],
            [[1, [[2, 3], 4], 5]],
            [[[1, [[2, [[]], 3], 4]], 5]],
            [new \ArrayIterator([
                new \ArrayIterator([1, 2]),
                3,
                new \ArrayIterator([4, 5]),
            ])]

        ];
    }

    /** @dataProvider flattenTestRange */
    public function testFlatten($input) {
        $this->assertSame([1, 2, 3, 4, 5], (new FluentIterator($input))->flatten()->toArray());
    }

    public function testFlattenKeyPreservation()
    {
        // Test key preservation
        $this->assertSame(
            ['a' => 1, 'c' => 2, 'd' => 3],
            (new FluentIterator(['a' => 1, 'b' => ['c' => 2, 'd' => 3]]))->flatten()->toArrayWithKeys()
        );
    }

    public function testFlattenLevels() {

        $this->assertSame(
            [[1, [[2, [[]], 3], 4]], 5],
            (new FluentIterator([[1, [[2, [[]], 3], 4]], 5]))->flatten(0)->toArray()
        );
        $this->assertSame(
            [1, [[2, [[]], 3], 4], 5],
            (new FluentIterator([[1, [[2, [[]], 3], 4]], 5]))->flatten(1)->toArray()
        );
        $this->assertSame(
            [1, [2, [[]], 3], 4, 5],
            (new FluentIterator([[1, [[2, [[]], 3], 4]], 5]))->flatten(2)->toArray()
        );
        $this->assertSame(
            [1, 2, [[]], 3, 4, 5],
            (new FluentIterator([[1, [[2, [[]], 3], 4]], 5]))->flatten(3)->toArray()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Number of levels must be non-negative
     */
    public function testFlattenNegativeLevelError() {
        (new FluentIterator([1, 2, 3]))->flatten(-1)->toArray();
    }

    public function testCount() {

        $this->assertSame(5, (new FluentIterator([1, 2, 3, 4, 5]))->count());
        $this->assertSame(5, (new FluentIterator(toIter([1, 2, 3, 4, 5])))->count());
    }

    public function testIsEmpty() {

        $this->assertTrue((new FluentIterator([]))->isEmpty());
        $this->assertFalse((new FluentIterator([null]))->isEmpty());
        $this->assertTrue((new FluentIterator(toArray([])))->isEmpty());
        $this->assertFalse((new FluentIterator(toArray([null])))->isEmpty());
        $this->assertTrue(isEmpty(repeat(42, 0)));
        $this->assertTrue((new FluentIterator(repeat(42, 0)))->isEmpty());
        $this->assertFalse((new FluentIterator(repeat(42)))->isEmpty());
    }

    public function testToArray() {
        $this->assertSame([1, 2, 3], (new FluentIterator(['a' => 1, 'b' => 2, 'c' => 3]))->toArray());
        $this->assertSame(
            [1, 2, 3],
            (new FluentIterator(new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3])))->toArray()
        );
        $this->assertSame(
            [1, 2, 3],
            (new FluentIterator(chain(['a' => 1, 'b' => 2], ['a' => 3])))->toArray()
        );
    }

    public function testToArrayWithKeys() {
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            (new FluentIterator(['a' => 1, 'b' => 2, 'c' => 3]))->toArrayWithKeys()
        );
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            (new FluentIterator(new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3])))->toArrayWithKeys()
        );
        $this->assertSame(
            ['a' => 3, 'b' => 2],
            (new FluentIterator(chain(['a' => 1, 'b' => 2], ['a' => 3])))->toArrayWithKeys()
        );
    }


    public function testFlip() {
        $this->assertSame(
            [1 => 'a', 2 => 'b', 3 => 'c'],
            (new FluentIterator(['a' => 1, 'b' => 2, 'c' => 3]))->flip()->toArrayWithKeys()
        );
    }

    public function testJoin() {
        $this->assertSame('', (new FluentIterator([]))->join(', '));
        $this->assertSame(
            'a, b, c',
            (new FluentIterator(['a', 'b', 'c']))->join(', ')
        );
    }

    public function testChunk() {
        $iterable = new FluentIterator(
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]
        );

        $this->assertSame(
            [['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4], ['e' => 5]],
            $iterable->chunk(2)->toArray()
        );
        $this->assertSame(
            [[1, 2], [3, 4], [5]],
            $iterable->chunk(2, false)->toArray()
        );

        $this->assertSame(
            [[0=>0, 1=>1], [2=>2, 3=>3]],
            (new FluentIterator([0, 1, 2, 3]))->chunk(2)->toArray()
        );
        $this->assertSame(
            [[0, 1], [2, 3]],
            (new FluentIterator([0, 1, 2, 3]))->chunk(2, false)->toArray()
        );

        $this->assertSame([[0, 1, 2]], (new FluentIterator([0, 1, 2]))->chunk(100000)->toArray());
        $this->assertSame([], (new FluentIterator([]))->chunk(100000)->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Chunk size must be positive
     */
    public function testZeroChunkSizeError() {
        (new FluentIterator([1, 2, 3]))->chunk(0)->toArray();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Chunk size must be positive
     */
    public function testNegativeChunkSizeError() {
        (new FluentIterator([1, 2, 3]))->chunk(-1)->toArray();
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
            (new FluentIterator($iter))->recurse('iter\toArray')
        );

        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4]],
            (new FluentIterator($iter))->recurse('iter\toArrayWithKeys')
        );
    }

    private function assertKeysValues(array $keys, array $values, callable $fn) {

        $this->assertSame($keys, (new FluentIterator($fn()))->keys()->toArray());
        $this->assertSame($values, (new FluentIterator($fn()))->values()->toArray());
    }


    public function testRewindable()
    {
        $rewindable = new FluentIterator(['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $rewindable->toArray());
        $this->assertSame(['a', 'b', 'c'], $rewindable->toArray());

        $this->assertSame(['a'], $rewindable->take(1)->toArray());
        $this->assertSame(['a'], $rewindable->take(1)->toArray());
    }


    public function testVia()
    {
        $iterator = new FluentIterator(range(1, 1000000));


        $this->assertSame([11, 12, 13, 14 , 15], $iterator
            ->drop(5)
            ->via(function(iterable $iterator) {
                return take(10, $iterator);
            })
            ->drop(5)
            ->toArray()
        );

    }

}