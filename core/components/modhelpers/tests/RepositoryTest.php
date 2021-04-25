<?php

use modHelpers\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    protected static $store;

    public static function setUpBeforeClass() :void
    {
        self::$store = new Repository;
    }

    public function testGet()
    {
        $array = [
            'key1' => [
                'skey' => 'value',
            ],
            'key2' => true,
        ];
        $store = new Repository($array);
        self::assertTrue($store->has('key1.skey'));
        self::assertNotNull($store->get('key1'));
        self::assertSame('value', $store->get('key1.skey'));
        self::assertNotNull($store->get('key2'));
    }

    public function testSet()
    {
        $store = new Repository();
        self::assertNull($store->get('level1.level2.level3'));

        $store->set('level1.level2.level3', '3 levels');
        self::assertSame('3 levels', $store->get('level1.level2.level3'));
        self::assertSame('3 levels', $store->get('level1')['level2']['level3']);

        $array = $store->all();
        self::assertSame('3 levels', $array['level1']['level2']['level3']);
    }
}
