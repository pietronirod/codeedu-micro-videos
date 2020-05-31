<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        Factory(Genre::class, 1)->create();
        $this->assertCount(1, Genre::all());
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'test1']);
        $genre->refresh();
        $this->assertRegExp('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $genre->id);
        $this->assertEquals('test1', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $this->assertEquals('test1', $genre->name);
        $this->assertFalse($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'test1',
            'is_active' => false
        ]);

        $data = [
            'name' => 'name_updated',
            'is_active' => true
        ];
        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create()->first();
        $genre->delete();
        $this->assertSoftDeleted('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
            'is_active' => $genre->is_active
        ]);
    }
}
