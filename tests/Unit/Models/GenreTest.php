<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    public function testUseTraits()
    {
        $this->assertEqualsCanonicalizing([SoftDeletes::class, Uuid::class], class_uses(Genre::class));
    }

    public function testFillable()
    {
        $genre = new Genre();
        $this->assertEqualsCanonicalizing(['name', 'is_active'], $genre->getFillable());
    }

    public function testDates()
    {
        $genre = new Genre();
        $this->assertEqualsCanonicalizing(['created_at', 'deleted_at', 'updated_at'], $genre->getDates());
    }

    public function testCasts()
    {
        $genre = new Genre();
        $this->assertEqualsCanonicalizing(['name' => 'string', 'is_active' => 'boolean'], $genre->getCasts());
    }

    public function testIncrementing(){
        $genre = new Genre();
        $this->assertFalse($genre->incrementing);
    }
}
