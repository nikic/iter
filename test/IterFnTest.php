<?php

namespace iter;

use iter\fn;

class IterFnTest extends \PHPUnit_Framework_TestCase {
    public function testIndex() {
        $getIndex3 = fn\index(3);
        $getIndexTest = fn\index('test');

        $arr1 = [10, 11, 12, 13, 14, 15];
        $arr2 = ['foo' => 'bar', 'test' => 'tset', 'bar' => 'foo'];

        $this->assertEquals($arr1[3], $getIndex3($arr1));
        $this->assertEquals($arr2['test'], $getIndexTest($arr2));
    }

    public function testProperty() {
        $getPropertyFoo = fn\property('foo');
        $getPropertyBar = fn\property('bar');

        $obj = (object) ['foo' => 'bar', 'bar' => 'foo'];

        $this->assertEquals($obj->foo, $getPropertyFoo($obj));
        $this->assertEquals($obj->bar, $getPropertyBar($obj));
    }

    public function testMethod() {
        $callMethod1 = fn\method('test');
        $callMethod2 = fn\method('test', []);
        $callMethod3 = fn\method('test', ['a', 'b']);

        $obj = new _MethodTestDummy;

        $this->assertEquals([], $callMethod1($obj));
        $this->assertEquals([], $callMethod2($obj));
        $this->assertEquals(['a', 'b'], $callMethod3($obj));
    }

    public function testNot() {
        $constFalse = fn\not(function() { return true; });
        $constTrue = fn\not(function() { return false; });
        $invert = fn\not(function($bool) { return $bool; });
        $nand = fn\not(fn\operator('&&'));

        $this->assertEquals(false, $constFalse());
        $this->assertEquals(true, $constTrue());
        $this->assertEquals(false, $invert(true));
        $this->assertEquals(true, $invert(false));
        $this->assertEquals(false, $nand(true, true));
        $this->assertEquals(true, $nand(true, false));
    }

    /** @dataProvider provideTestOperator */
    public function testOperator($op, $a, $b, $result) {
        $fn1 = fn\operator($op);
        $fn2 = fn\operator($op, $b);

        $this->assertEquals($result, $fn1($a, $b));
        $this->assertEquals($result, $fn2($a));
    }

    public function provideTestOperator() {
        return [
            ['instanceof', new \stdClass, 'stdClass', true],
            ['*', 3, 2, 6],
            ['/', 3, 2, 1.5],
            ['%', 3, 2, 1],
            ['+', 3, 2, 5],
            ['-', 3, 2, 1],
            ['.', 'foo', 'bar', 'foobar'],
            ['<<', 1, 8, 256],
            ['>>', 256, 8, 1],
            ['<', 3, 5, true],
            ['<=', 5, 5, true],
            ['>', 3, 5, false],
            ['>=', 3, 5, false],
            ['==', 0, 'foo', true],
            ['!=', 1, 'foo', true],
            ['===', 0, 'foo', false],
            ['!==', 0, 'foo', true],
            ['&', 3, 1, 1],
            ['|', 3, 1, 3],
            ['^', 3, 1, 2],
            ['&&', true, false, false],
            ['||', true, false, true],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown operator "**"
     */
    public function testInvalidOperator() {
        fn\operator('**');
    }
}

class _MethodTestDummy {
    public function test() {
        return func_get_args();
    }
}