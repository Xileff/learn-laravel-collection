<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        // $flattened = $collection->flatMap(function ($person) {
        //     return $person['hobbies'];
        // });

        // $this->assertEquals(['Coding', 'Gaming', 'Eating', 'Sleeping'], $flattened->all());

        $flattened = $collection->flatMap(function ($person) {
            return $person['name'];
        });

        $this->assertEquals(['Felix', 'Xilef'], $flattened->all());
    }
}
