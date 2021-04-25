<?php

use PHPUnit\Framework\TestCase;

class ChunkSnippetFileTest extends TestCase
{
    private $path = '/path/to/elements/';

    public function chunkProvider()
    {
        return [
            ['./chunk', '/path/to/elements/chunk'],
            ['/path/to/elements/chunk.php', '/path/to/elements/chunk.php'],
            ['chunk', 'chunk'],
            ['chunk.php', 'chunk.php'],
        ];
    }

    /**
     * @dataProvider chunkProvider
     */
    public function testChunkNameValidation($fileName, $expected)
    {
        if (strpos($fileName, './') === 0) {
            $fileName = $this->path . $fileName;
        }
        $fileName = sanitize_path($fileName);
        self::assertSame($fileName, $expected);
    }

    public function fileSnippetProvider()
    {
        return [
            ['./snippet.php'],
            ['.//snippet.php'],
            ['./../snippet.php'],
            ['/path/to/elements/snippet.php'],
        ];
    }

    /**
     * @dataProvider fileSnippetProvider
     */
    public function testSnippetNameValidationTrue($filename)
    {
        if (strpos($filename, './') === 0) {
            $filename = $this->path . $filename;
        }
        $filename = sanitize_path($filename);
        self::assertTrue(pathinfo($filename, PATHINFO_EXTENSION) === 'php' && $filename === '/path/to/elements/snippet.php');
    }

    public function snippetProvider()
    {
        return [
            ['snippet'],
            ['./snippet'],
            ['snippet.php'],
            ['//snippet.php'],
            ['/../snippet.php'],
        ];
    }

    /**
     * @dataProvider snippetProvider
     */
    public function testSnippetFileNameValidationFalse($snippet)
    {
        if (strpos($snippet, './') === 0) {
            $snippet = $this->path . $snippet;
        }
        $snippet = sanitize_path($snippet);
        self::assertFalse(pathinfo($snippet, PATHINFO_EXTENSION) === 'php' && $snippet === '/path/to/elements/snippet.php');
    }
}
