<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    const API_PATH = '/api/orders';
    const STR255 = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789ABCDE';
    const STR256 = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789ABCDEF';
    /**
     * A basic feature test example.
     *
     * @return void
     */
    
    //Start store
         /** @test */
    public function on_store_order_success()
    {
        //echo "This..............................................";
        $item = factory(Item::class)->create();
        
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
        ]);
        $res->assertStatus(201);
        $res->assertJsonCount(11, 'data');
        $res->assertJsonStructure([
            'data' => [
                'id',
                'total_price',
                'first_name',
                'last_name',
                'address1',
                'address2',
                'country',
                'state',
                'city',
                'created_at',
                'updated_at'
            ]
        ]);
        $json = $res->json();//1 is id
        $this->assertEquals(55555, $json['data']['total_price']);//3
        $this->assertEquals('Wai', $json['data']['first_name']);//4
        $this->assertEquals('Phyo', $json['data']['last_name']);//5
        $this->assertEquals('Mandalay', $json['data']['address1']);//5
        $this->assertEquals('pku', $json['data']['address2']);//5
        $this->assertEquals('Myanmar', $json['data']['country']);//5
        $this->assertEquals('Yaw', $json['data']['state']);//5
        $this->assertEquals('Htilin', $json['data']['city']);//5
        $this->assertLessThan(2, time() - strtotime($json['data']['created_at']));//6
        $this->assertLessThan(2, time() - strtotime($json['data']['updated_at']));//7
    }
    
    /** @test */
    public function store_without_postData_will_occur_validation_error()
    {
        //echo "This..............................................";
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH);
    }
    
    /** @test */
    public function store_noParentItemId_will_occur_database_error()
    {
        //echo "This..............................................";
        $this->expectException(QueryException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[1],//there is no items
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_first_name_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => '',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
        ]);
    }
    
    /** @test */
    public function store_first_name_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => '1',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
            
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_first_name_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => self::STR256,
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_first_name_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
           'total_price' => 55555,
            'first_name' => self::STR255,
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['first_name']));
    }
    
    /** @test */
    public function store_last_name_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => '',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_last_name_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => '1',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_last_name_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => self::STR256,
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_last_name_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => self::STR255,
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['last_name']));
    }

    /** @test */
    public function store_address1_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => '',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
        ]);
    }
    
    /** @test */
    public function store_address1_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => '1',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
            
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_address1_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => self::STR256,
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_address1_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
           'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => self::STR255,
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['address1']));
    }

    /** @test */
    public function store_address2_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => '',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_address2_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => '1',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_address2_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => self::STR256,
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_address2_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => self::STR255,
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['address2']));
    }

    /** @test */
    public function store_country_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => '',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
        ]);
    }
    
    /** @test */
    public function store_country_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => '1',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
            
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_country_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => self::STR256,
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_country_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
           'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => self::STR255,
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['country']));
    }
    
    /** @test */
    public function store_state_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => '',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_state_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => '1',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_state_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => self::STR256,
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_state_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => self::STR255,
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['state']));
    }

    /** @test */
    public function store_city_length_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => '',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
        ]);
    }
    
    /** @test */
    public function store_city_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => '1',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999],
            
        ]);
        $res->assertStatus(201); 
    }
    
    /** @test */
    public function store_city_length_256_will_occur_validation_error()
    {
        //echo "This..............................................";
        //first, confirm strlen is 256
        $this->assertEquals(256, strlen(self::STR256));
        
        //then, confirm exception is occured
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => self::STR256,
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_city_length_255_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
           'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => self::STR255,
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
        
        //Confirm that the string is not truncated due to DB constraints.
        $json = $res->json();
        $this->assertEquals(255, strlen($json['data']['city']));
    }
    
    /** @test */
    public function store_total_price_minus1_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => -1,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_total_price_0_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 0,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
    }

    /** @test */
    public function store_item_price_minus1_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[-1]
        ]);
    }
    
    /** @test */
    public function store_item_price_0_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[3],
            'item_price_array'=>[0]
        ]);
        $res->assertStatus(201); 
    }

    /** @test */
    public function store_item_qty_0_will_occur_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $this->expectException(ValidationException::class);
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[0],
            'item_price_array'=>[999]
        ]);
    }
    
    /** @test */
    public function store_item_qty_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->json('POST', self::API_PATH, [
            'total_price' => 55555,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'item_id_array'=>[$item->id],
            'item_qty_array'=>[1],
            'item_price_array'=>[999]
        ]);
        $res->assertStatus(201); 
    }
    // //End Store
}
