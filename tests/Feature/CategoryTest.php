<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
class CategoryTest extends TestCase
{
    use RefreshDatabase;
    const API_PATH = '/api/categories';
    const STR255 = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789ABCDE';
    const STR256 = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789ABCDEF';
    /**
     * A basic feature test example.
     *
     * @return void
     */

//For Index
    /** @test */
    public function testindexget()
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

    
//for show
    /** @test */
    public function on_show_category_success()
    {
        $exps = factory(Category::class, 3)->create();

        $res = $this->json('GET', self::API_PATH.'/'.$exps[1]->id); 
        $res->assertStatus(200); 
        $res->assertExactJson([
            'data' => [
                'id' => $exps[1]->id,
                'name' => $exps[1]->name,
                'created_at' => $this->toMySqlDateFromJson($exps[1]->updated_at),
                'updated_at' => $this->toMySqlDateFromJson($exps[1]->created_at),
                'deleted_at' => null,
            ]
        ]);
    }

    /** @test */
    public function show_deletedId_will_occur_error()
    {
        $row = factory(Category::class)->create();
        $row->delete();

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\Category] '.$row->id);
        $res = $this->json('GET', self::API_PATH.'/'.$row->id); 

    }

    /** @test */
    public function show_notExistsId_will_occur_error()
    {
        $row = factory(Category::class)->create();
        $errorId = $row->id + 1;

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\Category] '.$errorId);
        $res = $this->json('GET', self::API_PATH.'/'.$errorId); 
    }


 //For store 

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



    /** @test */
    public function store_without_postData_will_occur_validation_error()
    {
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH);
    }

    
    /** @test */
    public function store_name_length_0_will_occur_validation_error()
    {
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'name' => ''
        ]);
    }
    

    /** @test */
    public function store_name_length_1_will_no_validation_error()
    {
        $res = $this->json('POST', self::API_PATH, [
            'name' => '1'
        ]);
        $res->assertStatus(201); 
    }

    

    /** @test */
    public function store_name_length_256_will_occur_validation_error()
    {
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));

        //then, confirm exception is occured
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'name' => self::STR256
        ]);
    }
    

    /** @test */
    public function store_name_length_255_will_no_validation_error()
    {
        $res = $this->json('POST', self::API_PATH, [
            'name' => self::STR255
        ]);
        $res->assertStatus(201); 
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['name']));
    }

//For UPDATE
    /** @test */
    public function on_update_category_success()
    {
        $row = factory(Category::class)->create();
        $res = $this->json('PUT', self::API_PATH.'/'.$row->id, [
            'name' => 'editedCategory'
        ]);
        $res->assertStatus(200);
        $res->assertJsonCount(5, 'data');
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]);
        $json = $res->json();//1 is id
        $this->assertEquals('editedCategory', $json['data']['name']);//2
        $this->assertLessThan(2, time() - strtotime($json['data']['created_at']));//3
        $this->assertLessThan(2, time() - strtotime($json['data']['updated_at']));//4
        $this->assertEquals(null, $json['data']['deleted_at']);//5
    }


    /** @test */
    public function update_name_length_1_will_no_validation_error()
    {
        $row = factory(Category::class)->create();
        $res = $this->json('PUT', self::API_PATH.'/'.$row->id, [
            'name' => '1'
        ]);
        $res->assertStatus(200); 
    }

    /** @test */
    public function update_name_length_256_will_occur_validation_error()
    {
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));

        //then, confirm exception is occured
        $this->expectException(ValidationException::class);
        $row = factory(Category::class)->create();
        $res = $this->json('PUT', self::API_PATH.'/'.$row->id, [
            'name' => self::STR256
        ]);
    }

    /** @test */
    public function update_name_length_255_will_no_validation_error()
    {
        $row = factory(Category::class)->create();
        $res = $this->json('PUT', self::API_PATH.'/'.$row->id, [
            'name' => self::STR255
        ]);
        $res->assertStatus(200); 
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['name']));
    }

//For DELETE
     /** @test */
    public function on_destory_category_success()
    {
        $row = factory(Category::class)->create();
        $res = $this->json('DELETE', self::API_PATH.'/'.$row->id);
        $res->assertStatus(204);
        $this->assertSoftDeleted('categories', ['id' => $row->id]);
    }
}
