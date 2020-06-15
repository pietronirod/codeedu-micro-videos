<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route("genres.show", ['genre' => $genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
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
        $this->assertInvalidationInStoreAction($data, 'max.string',
            ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');

        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'PUT', route('genres.update', ['genre' => $genre->id])
        );
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
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
                'is_active' => true,
                'deleted_at' => null
            ]
        );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $this->assertStore($data,
            $data + [
                'is_active' => false
            ]
        );
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $response = $this->assertUpdate($data, $data + [
            'deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id])
        );
        $response->assertStatus(204);
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
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
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }
}
