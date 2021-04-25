<?php

use PHPUnit\Framework\TestCase;

class SanitizePathTest extends TestCase
{
    public function dataProviderForTrue()
    {
        return [
          ['/path/to/../.././file.php', '/path/to/file.php'],
          ['/path/to//..//file.php', '/path/to/file.php'],
          ['/path/to/....//file.php', '/path/to/file.php'],
          ['./path/to/file.php', '/path/to/file.php'],
        ];
    }
    /**
     * @dataProvider dataProviderForTrue
     */
    public function testCheckPathAndReturnTrue($path, $expected)
    {
        self::assertSame(sanitize_path($path), $expected);
    }
}
