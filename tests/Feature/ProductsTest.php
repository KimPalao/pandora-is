<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductsTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected function setUp(): void
    {
        parent::setUp();
        $table = new Product(['name' => 'Crafting Table']);
        $table->save();

        $axe = new Product(['name' => 'Axe']);
        $axe->save();

        $plank = new Resource(['name' => 'Wooden Plank', 'stock' => 2]);
        $plank->save();

        $stick = new Resource(['name' => 'Stick', 'stock' => 4]);
        $stick->save();

        $table->resources()->attach([$plank->id => ['quantity' => 4]]);

        $axe->resources()->attach([
            $plank->id => ['quantity' => 3],
            $stick->id => ['quantity' => 2],
        ]);
    }

    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_resolve_products()
    {
        $plank = Resource::whereName('Wooden Plank')->first();
        $stick = Resource::whereName('Stick')->first();
        $response = $this->getJson('/api/resolve-products?products=1,2&quantities=2,1');
        $response->assertJson([
            'resources' => [
                $plank->id => [
                    'quantity' => 9
                ],
                $stick->id => [
                    'quantity' => 0
                ]
            ]
        ]);
    }
}
