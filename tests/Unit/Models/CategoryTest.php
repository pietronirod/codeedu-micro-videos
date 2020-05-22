<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    public function testFillable()
    {
        $category = new Category();
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEqualsCanonicalizing($fillable, $category->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class, Uuid::class
        ];
        $this->assertEqualsCanonicalizing($traits, class_uses(Category::class));

    }

    public function testCasts(){
        $category = new Category();
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEqualsCanonicalizing($casts, $category->getCasts());
    }

    public function testDatesAttributes(){
        $category = new Category();
        $dates = ['deleted_at', 'created_at', 'updated_at'];

        $this->assertEqualsCanonicalizing($dates, $category->getDates());
    }

    public function testIncrementing(){
        $category = new Category();

        $this->assertFalse($category->incrementing);
    }
}
