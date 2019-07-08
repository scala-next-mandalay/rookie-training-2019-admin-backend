<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
class CategoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function testExample()
    {
        $exps = factory(Category::class, 2)->create();
 
        $res = $this->get('/api/categories'); 
        $res->assertStatus(200); 
        $res->assertExactJson([
            'data' => [
                [
                    'id' => $exps[0]->id,
                    'name' => $exps[0]->name,
                    'created_at' => $this->toMySqlDateFromJson($exps[0]->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps[0]->created_at),
                    'deleted_at' => null,
                ],
                [
                    'id' => $exps[1]->id,
                    'name' => $exps[1]->name,
                    'created_at' => $this->toMySqlDateFromJson($exps[1]->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps[1]->created_at),
                    'deleted_at' => null,
                ]
            ]
        ]);
    }
    /** @test */
    public function testassoc()
    {
        factory(Category::class)->create(['id' => 9]);
        factory(Category::class)->create(['id' => 8]);
        factory(Category::class)->create(['id' => 3]);
        $res = $this->get('/api/categories'); 
        $res->assertStatus(200);
        $res->assertJsonCount(3, 'data');
        $res->assertJson([
            'data' => [
                ['id' => 3],
                ['id' => 8],
                ['id' => 9],
            ]
        ]);
    }
    
    /** @test */
    public function deletednotshow()
    {
        $a = factory(Category::class)->create();
        $b = factory(Category::class)->create();
        $b->delete();
        $c = factory(Category::class)->create();
       
        
        $ans = $this->get('/api/categories'); 
        $ans->assertStatus(200);
        $ans->assertJsonCount(2, 'data');
        $ans->assertJson([
            'data' => [
                ['id' => $a->id],
                ['id' => $c->id],
            ]
        ]);
    }
    /** @test */
    public function add_row()
    {
        $res = $this->post('/api/categories', [
            'name' => 'category1'
        ]);
        $res->assertStatus(201);
        $res->assertJsonCount(4, 'data');
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ]
        ]);
        $json = $res->json();//1 is id
        $this->assertEquals('category1', $json['data']['name']);//2
        $this->assertLessThan(2, time() - strtotime($json['data']['created_at']));//3
        $this->assertLessThan(2, time() - strtotime($json['data']['updated_at']));//4
    }
}