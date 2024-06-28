<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PhpParser\Node\Expr\FuncCall;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            // key itu index nya
            $this->assertEquals($key + 1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEquals([1, 2, 3], $collection->all());

        $last = $collection->pop();
        $this->assertEquals(3, $last);
        $this->assertEquals([1, 2], $collection->all());
    }

    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($data) {
            return $data * 2;
        });
        $this->assertEquals([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(['Felix', 'Xilef']);
        $result  = $collection->mapInto(Person::class);
        $this->assertEquals([new Person("Felix"), new Person("Xilef")], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            ['Felix', 'Xilef'],
            ['Xilef', 'Felix']
        ]);

        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = "$firstName $lastName";
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Felix Xilef"),
            new Person("Xilef Felix"),
        ], $result->all());
    }

    public function testMapToGroups()
    {
        $collection = collect([
            [
                'name' => 'Felix',
                'department' => 'IT'
            ],
            [
                'name' => 'Xilef',
                'department' => 'IT'
            ],
            [
                'name' => 'Budi',
                'department' => 'HR'
            ]
        ]);

        $result = $collection->mapToGroups(function ($person) {
            return [$person['department'] => $person['name']];
            // hasilnya
            // IT : Felix
            // IT : Xilef
            // HR : Budi
            // Nanti yang key nya sama akan digabung, jadi
            // IT : Felix, Xilef
            // HR : Budi
        });

        $this->assertEquals([
            'IT' => collect(['Felix', 'Xilef']),
            'HR' => collect(['Budi'])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6])
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = collect(['name', 'country']);
        $collection2 = collect(['Felix', 'Wakanda']);
        $collection3 = $collection1->combine($collection2);

        $this->assertEquals([
            'name' => 'Felix',
            'country' => 'Wakanda'
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect(
            [
                [1, 2, 3],
                [4, 5, 6],
                [7, 8, 9]
            ]
        );

        $collapsed = $collection->collapse();

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $collapsed->all());
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                'name' => 'Felix',
                'hobbies' => ['Coding', 'Gaming']
            ],
            [
                'name' => 'Xilef',
                'hobbies' => ['Eating', 'Sleeping']
            ]
        ]);

        $flattened = $collection->flatMap(function ($person) {
            return $person['hobbies'];
        });

        $this->assertEquals(['Coding', 'Gaming', 'Eating', 'Sleeping'], $flattened->all());

        // $flattened = $collection->flatMap(function ($person) {
        //     return $person['name'];
        // });

        // $this->assertEquals(['Felix', 'Xilef'], $flattened->all());
    }

    public function testJoin()
    {
        $collection = collect(['Felix', 'Xilef', 'Orevas']);
        $glued = $collection->join('-');
        $this->assertEquals('Felix-Xilef-Orevas', $glued);

        $collection = collect(['Felix', 'Xilef', 'Orevas']);
        $glued = $collection->join(', ', ' and ');
        $this->assertEquals('Felix, Xilef and Orevas', $glued);
    }

    public function testFilter()
    {
        $collection = collect([
            'Felix' => 100,
            'Xilef' => 70,
            'Orevas' => 90
        ]);

        $passed = $collection->filter(function ($value, $key) {
            return $value > 70;
        });

        $this->assertEquals([
            'Felix' => 100,
            'Orevas' => 90
        ], $passed->all());
    }

    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8]);

        // data ganjil akan dibuang, dan data genap tetap ada.
        // masalahnya, index tidak berubah, jadi angka 2 indexnya tetap 1, angka 4 indexnya tetap 3, dst
        $filtered = $collection->filter(function ($value) {
            return $value % 2 == 0;
        });


        // $this->assertEquals([2, 4, 6, 8], $filtered->all()); // bakal error karena masalah index
        $this->assertEqualsCanonicalizing([2, 4, 6, 8], $filtered->all());
    }

    public function testPartitioning()
    {
        $collection = collect([
            'Felix' => 100,
            'Xilef' => 70,
            'Orevas' => 90
        ]);

        [$passed, $notPassed] = $collection->partition(function ($value, $key) {
            return $value > 70;
        });

        $this->assertEquals([
            'Felix' => 100,
            'Orevas' => 90
        ], $passed->all());

        $this->assertEquals([
            'Xilef' => 70
        ], $notPassed->all());
    }

    public function testTesting()
    {
        $collection = collect(['Felix', 'Xilef', 'Orevas']);
        $this->assertTrue($collection->contains('Xilef'));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value === 'Xilef';
        }));
    }

    public function testGroupBy()
    {
        $collection = collect([
            [
                'name' => 'Felix',
                'department' => 'IT'
            ],
            [
                'name' => 'Xilef',
                'department' => 'IT'
            ],
            [
                'name' => 'Budi',
                'department' => 'HR'
            ]
        ]);

        $groupByKey = $collection->groupBy('department');
        // unit test : hasil grouping yang pake collect()
        $this->assertEquals([
            'IT' => collect([

                [
                    'name' => 'Felix',
                    'department' => 'IT'
                ],
                [
                    'name' => 'Xilef',
                    'department' => 'IT'
                ]

            ]),
            'HR' => collect([
                [
                    'name' => 'Budi',
                    'department' => 'HR'
                ]
            ])
        ], $groupByKey->all());

        $groupByFunc = $collection->groupBy(function ($value, $key) {
            return $value['department'];
        });

        $this->assertEquals([
            'IT' => collect([

                [
                    'name' => 'Felix',
                    'department' => 'IT'
                ],
                [
                    'name' => 'Xilef',
                    'department' => 'IT'
                ]

            ]),
            'HR' => collect([
                [
                    'name' => 'Budi',
                    'department' => 'HR'
                ]
            ])
        ], $groupByKey->all());
    }

    public function testSlicing()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4, 5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($val, $key) {
            return $val === 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($val, $key) {
            return $val < 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($val, $key) {
            return $val === 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($val, $key) {
            return $val < 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testChunk()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(function ($value, $key) {
            return $value > 5;
        });
        $this->assertEquals(6, $result);
    }

    public function testLast()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->last();
        $this->assertEquals(9, $result);

        $result = $collection->last(function ($value, $key) {
            return $value < 5;
        });
        $this->assertEquals(4, $result);
    }

    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->random();
        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }

    public function testCheckingExistence()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(0));
        $this->assertTrue($collection->contains(function ($val, $key) {
            return $val === 8;
        }));
        $this->assertFalse($collection->containsOneItem());
    }

    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 4, 5, 7, 6, 8, 9]);

        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->reverse();
        $this->assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());
    }

    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(9, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->sum();
        $this->assertEquals(45, $result);
    }
}
