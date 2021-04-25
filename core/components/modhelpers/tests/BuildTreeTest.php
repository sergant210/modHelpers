<?php

use PHPUnit\Framework\TestCase;

class BuildTreeTest extends TestCase
{
    public $sourceWithParent = [
        [
            'id' => 1,
            'name' => 'Level 1.1',
            'parent' => 0,
        ],
        [
            'id' => 2,
            'name' => 'Level 1.2',
            'parent' => 0,
        ],
        [
            'id' => 3,
            'name' => 'Level 2.1',
            'parent' => 1,
        ],
        [
            'id' => 4,
            'name' => 'Level 2.2',
            'parent' => 2,
        ],
        [
            'id' => 5,
            'name' => 'Level 2.3',
            'parent' => 2,
        ],
        [
            'id' => 6,
            'name' => 'Level 3',
            'parent' => 5,
        ],
    ];

    public $sourceWithoutParent = [
        [
            'id' => 1,
            'name' => 'Resource 1',
        ],
        [
            'id' => 2,
            'name' => 'Resource 2',
        ],
        [
            'id' => 3,
            'name' => 'Resource 3',
        ],
        [
            'id' => 4,
            'name' => 'Resource 4',
        ],
        [
            'id' => 5,
            'name' => 'Resource 5',
        ],
        [
            'id' => 6,
            'name' => 'Resource 6',
        ],
    ];

    public $customArray = [
        [
            'rid' => 1,
            'name' => 'Level 1.1',
            'parent_id' => 0,
        ],
        [
            'rid' => 2,
            'name' => 'Level 1.2',
            'parent_id' => 0,
        ],
        [
            'rid' => 3,
            'name' => 'Level 2.1',
            'parent_id' => 1,
        ],
        [
            'rid' => 4,
            'name' => 'Level 2.2',
            'parent_id' => 2,
        ],
        [
            'rid' => 5,
            'name' => 'Level 2.3',
            'parent_id' => 2,
        ],
        [
            'rid' => 6,
            'name' => 'Level 3',
            'parent_id' => 5,
        ],
    ];

    public function testArrayWithParents()
    {
        $expected = [
            1 => [
                "id" => 1,
                "name" => "Level 1.1",
                "parent" => 0,
                "children" => [
                    3 => [
                        "id" => 3,
                        "name" => "Level 2.1",
                        "parent" => 1,
                    ],
                ],
            ],
            2 => [
                "id" => 2,
                "name" => "Level 1.2",
                "parent" => 0,
                "children" => [
                    4 => [
                        "id" => 4,
                        "name" => "Level 2.2",
                        "parent" => 2,
                    ],
                    5 => [
                        "id" => 5,
                        "name" => "Level 2.3",
                        "parent" => 2,
                        "children" => [
                            6 => [
                                "id" => 6,
                                "name" => "Level 3",
                                "parent" => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        self::assertSame($expected, build_tree($this->sourceWithParent));
    }

    public function testArrayWithoutParents()
    {
        $expected = [];
        foreach ($this->sourceWithoutParent as $row) {
            $expected[$row['id']] = $row;
        }
        self::assertSame($expected, build_tree($this->sourceWithoutParent));
    }

    public function testReturnOnlyLevel1()
    {
        $expected = [
            1 => [
                "id" => 1,
                "name" => "Level 1.1",
                "parent" => 0,
            ],
            2 => [
                "id" => 2,
                "name" => "Level 1.2",
                "parent" => 0,
            ],
        ];
        self::assertSame($expected, build_tree($this->sourceWithParent, 0, 1));
    }

    public function testExactParent()
    {
        $expected = [
            4 => [
                "id" => 4,
                "name" => "Level 2.2",
                "parent" => 2,
            ],
            5 => [
                "id" => 5,
                "name" => "Level 2.3",
                "parent" => 2,
                "children" => [
                    6 => [
                        "id" => 6,
                        "name" => "Level 3",
                        "parent" => 5,
                    ],
                ],
            ],
        ];
        self::assertSame($expected, build_tree($this->sourceWithParent, 2));
    }

    public function testArrayWithCustomIdAndParentNames()
    {
        $expected = [
            1 => [
                "rid" => 1,
                "name" => "Level 1.1",
                "parent_id" => 0,
                "children" => [
                    3 => [
                        "rid" => 3,
                        "name" => "Level 2.1",
                        "parent_id" => 1,
                    ],
                ],
            ],
            2 => [
                "rid" => 2,
                "name" => "Level 1.2",
                "parent_id" => 0,
                "children" => [
                    4 => [
                        "rid" => 4,
                        "name" => "Level 2.2",
                        "parent_id" => 2,
                    ],
                    5 => [
                        "rid" => 5,
                        "name" => "Level 2.3",
                        "parent_id" => 2,
                        "children" => [
                            6 => [
                                "rid" => 6,
                                "name" => "Level 3",
                                "parent_id" => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        self::assertSame($expected, build_tree($this->customArray, 0, 10, ['idField' => 'rid', 'parentField' => 'parent_id']));
    }
}
