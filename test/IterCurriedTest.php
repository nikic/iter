<?php

namespace iter;

use PHPUnit\Framework\TestCase;

class IterCurriedTest extends TestCase
{
    public function testCurry() {
        $fn = function($a, $b, $c) {
            return [$a, $b, $c];
        };

        $this->assertEquals(
            [1,2,3],
            curry($fn, 2)(1)(2)(3)
        );
    }

    public function testPipe() {
        $a = function($a) { return $a . 'a'; };
        $b = function($b) { return $b . 'b'; };
        $this->assertEquals(
            'ab',
            pipe($a, $b)('')
        );
    }

    public function testCompose() {
        $a = function($a) { return $a . 'a'; };
        $b = function($b) { return $b . 'b'; };
        $this->assertEquals(
            'ba',
            compose($a, $b)('')
        );
    }

    private function assertCurriedEquals($array, $iter, $withKeys = false) {
        $fn = $withKeys ? 'iter\\toArrayWithKeys' : 'iter\\toArray';
        $this->assertSame($array, $fn($iter));
    }

    public function testCurriedVariants() {
        $this->assertCurriedEquals(
            [3, 6, 9, 12, 15],
            curried\map(fn\operator('*', 3))(range(1, 5))
        );
        $this->assertCurriedEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            curried\mapKeys('strtolower')(['A' => 1, 'B' => 2, 'C' => 3]),
            true
        );
        $this->assertCurriedEquals(
            [-1, 1, -2, 2, -3, 3, -4, 4, -5, 5],
            curried\flatMap(function($v) {
                return [-$v, $v];
            })([1, 2, 3, 4, 5])
        );
        $this->assertCurriedEquals(
            [2 => 1, 4 => 2, 6 => 3, 8 => 4],
            curried\reindex(fn\operator('*', 2))([1, 2, 3, 4]),
            true
        );
        $result = [];
        curried\apply(function($v) use (&$result) {
            $result[] = $v;
        })([1,2,3]);
        $this->assertCurriedEquals(
            [1,2,3],
            $result
        );
        $this->assertCurriedEquals(
            [-5, -4, -3, -2, -1],
            curried\filter(fn\operator('<', 0))(range(-5, 5))
        );
        $this->assertCurriedEquals(
            [1,2],
            curried\take(2)([1,2,3,4])
        );
        $this->assertCurriedEquals(
            [3,4],
            curried\drop(2)([1,2,3,4])
        );
        $this->assertEquals(
            true,
            curried\any(fn\operator('==', 2))([1,2,3])
        );
        $this->assertEquals(
            false,
            curried\all(fn\operator('>', 2))([2,3])
        );
        $this->assertEquals(
            2,
            curried\search(fn\operator('<', 3))([3,5,2])
        );
        $this->assertCurriedEquals(
            [1,2],
            curried\takeWhile(fn\operator('<=', 2))([1,2,3,4])
        );
        $this->assertCurriedEquals(
            [3,4],
            curried\dropWhile(fn\operator('<=', 2))([1,2,3,4])
        );
        $this->assertEquals(
            'a,b,c',
            curried\join(',')(['a', 'b', 'c'])
        );
        $this->assertCurriedEquals(
            [1,2,[3,4]],
            curried\recurse('iter\toArray')([1,2, new \ArrayIterator([3,4])])
        );
    }
}

