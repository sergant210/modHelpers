<?php

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{

    public function testDefaultIfNotNull()
    {
        $foo = 'foo';
        $result = default_if($foo, 'bar');
        self::assertSame('foo', $result);
    }

    public function testDefaultIfNotNullWith3Param()
    {
        $foo = 'foo';
        $result = default_if($foo, 'bar', 'foo');
        self::assertSame('bar', $result);
    }

    public function testDefaultIfNull()
    {
        $foo = null;
        $result = default_if($foo, 'bar');
        self::assertSame('bar', $result);
    }

    public function testValueNull()
    {
        $foo = null;
        $result = value($foo, 'bar');
        self::assertSame('bar', $result);
    }

    public function testValueNotNull()
    {
        $foo = 'foo';
        $result = value($foo, 'bar');
        self::assertSame('foo', $result);
    }

    public function testValueClosure()
    {
        $foo = function () {return 'foo';};
        $result = value($foo, 'bar');
        self::assertSame('foo', $result);
    }
}
