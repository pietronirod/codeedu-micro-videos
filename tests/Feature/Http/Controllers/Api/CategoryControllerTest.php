<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use http\Message\Body;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestValidations;
use Tests\Traits\TestSaves;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void {
        parent::setUp();
    }
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route("categories.show", ['category' => $category->id]));
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');

        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT', route('categories.update', ['category' => $category->id]));
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    public function testStore()
    {
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data,
            $data + [
                'description' => null,
                'is_active' => true,
                'deleted_at' => null
            ]
        );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ];
        $this->assertStore($data,
            $data + [
                'description' => 'description',
                'is_active' => false
            ]
        );
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $data = [
            'name' => 'test',
            'description' => 'test',
            'is_active' => false
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'description' => ''
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $category->id])
        );
        $response->assertStatus(204);
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'required');
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $this->assertInvalidationFields(
            $response, ['name'], 'max.string', ['max' => 255]
        );
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id ]);
    }

    protected function model()
    {
        return Category::class;
    }
}
