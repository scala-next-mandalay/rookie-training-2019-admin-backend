<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\Order;
use App\Models\Orderitem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    
    /** @test */
    public function order_by_id_asc_if_sortorder_does_not_desc()
    {
        factory(Order::class)->create(['id' => 3]);
        factory(Order::class)->create(['id' => 1]);
        factory(Order::class)->create(['id' => 2]);
        $url = self::API_PATH.'?sortorder=;&$';//for SQL Injection
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', $url); 
        $res->assertStatus(200);
        $res->assertJsonCount(3, 'data');
        $res->assertJson([
            'data' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]
        ]);
    }
    
    /** @test */
    public function order_by_id_asc_if_sortcol_does_not_exist()
    {
        factory(Order::class)->create(['id' => 3]);
        factory(Order::class)->create(['id' => 1]);
        factory(Order::class)->create(['id' => 2]);
        $url = self::API_PATH.'?sortcol=notexistscol';
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', $url); 
        $res->assertStatus(200);
        $res->assertJsonCount(3, 'data');
        $res->assertJson([
            'data' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]
        ]);
    }
    
    /** @test */
    public function orders_can_sort_by_first_name_asc()
    {
 
        factory(Order::class)->create(['first_name' => 'bbb']);
        factory(Order::class)->create(['first_name' => 'aaa']);
        factory(Order::class)->create(['first_name' => '03']);
        factory(Order::class)->create(['first_name' => '222']);
        $url = self::API_PATH.'?sortcol=first_name';
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', $url); 
        $res->assertStatus(200);
        $res->assertJsonCount(4, 'data');
        $res->assertJson([
            'data' => [
                ['first_name' => '03'],
                ['first_name' => '222'],
                ['first_name' => 'aaa'],
                ['first_name' => 'bbb'],
            ]
        ]);
    }
    
    /** @test */
    public function orders_can_sort_by_first_name_desc()
    {
 
        factory(Order::class)->create(['first_name' => 'bbb']);
        factory(Order::class)->create(['first_name' => 'aaa']);
        factory(Order::class)->create(['first_name' => '03']);
        factory(Order::class)->create(['first_name' => '222']);
        $url = self::API_PATH.'?sortcol=first_name&sortorder=desc';
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', $url); 
        $res->assertStatus(200);
        $res->assertJsonCount(4, 'data');
        $res->assertJson([
            'data' => [
                ['first_name' => 'bbb'],
                ['first_name' => 'aaa'],
                ['first_name' => '222'],
                ['first_name' => '03'],
            ]
        ]);
    }
    
     /** @test */
    public function orders_can_sort_by_last_name_asc()
    {
 
        factory(Order::class)->create(['last_name' => 'bbb']);
        factory(Order::class)->create(['last_name' => 'aaa']);
        factory(Order::class)->create(['last_name' => '03']);
        factory(Order::class)->create(['last_name' => '222']);
        $url = self::API_PATH.'?sortcol=last_name';
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', $url); 
        $res->assertStatus(200);
        $res->assertJsonCount(4, 'data');
        $res->assertJson([
            'data' => [
                ['last_name' => '03'],
                ['last_name' => '222'],
                ['last_name' => 'aaa'],
                ['last_name' => 'bbb'],
            ]
        ]);
    }


       

        /** @test */
    public function orders_everyone_can_get_rows()
    {
        //echo "This..............................................";
        $order =  factory(Order::class)->create();
       $res = $this->withHeaders($this->getAuthHeader())->get('/api/orders');
       $res->assertStatus(200);
       $res->assertExactJson([
            'data' => [
                [
                    'id'=>$order->id,
                    'total_price'=>$order->total_price,
                    'first_name'=>$order->first_name,
                    'last_name'=>$order->last_name,
                    'address1'=>$order->address1,
                    'address2'=>$order->address2,
                    'country'=>$order->country,
                    'state'=>$order->state,
                    'city'=>$order->city,           
                    'created_at' => $this->toMySqlDateFromJson($order->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($order->created_at),
                ],         
            ]
        ]);
    }

     /** @test */
    public function orders_are_order_by_id_asc()
    {
        //echo "This..............................................";
        factory(Order::class)->create(['id' => 42]);
        factory(Order::class)->create(['id' => 8]);
        factory(Order::class)->create(['id' => 35]);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', self::API_PATH); 
        $res->assertStatus(200);
        $res->assertJsonCount(3, 'data');
        $res->assertJson([
            'data' => [
                ['id' => 8],
                ['id' => 35],
                ['id' => 42],
            ]
        ]);
    }

    /** @test */
    public function get_11th_to_20th_orders_if_limit10_offset10_totalSize30()
    {
           //echo "This..............................................";
          $exps =  factory(Order::class, 30)->create();
          $res = $this->withHeaders($this->getAuthHeader())->json('GET','/api/orders?start=10');
          $res->assertJsonCount(10,'data');
          $res->assertJson([
              'data' => [
                  ['id' => $exps[10]->id],//11th
                  ['id' => $exps[11]->id],
                  ['id' => $exps[12]->id],
                  ['id' => $exps[13]->id],
                  ['id' => $exps[14]->id],
                  ['id' => $exps[15]->id],
                  ['id' => $exps[16]->id],
                  ['id' => $exps[17]->id],
                  ['id' => $exps[18]->id],
                  ['id' => $exps[19]->id],//20th
              ]
          ]);
    }

    /** @test */
    public function get_11th_to_15th_orders_if_limit10_offset10_totalSize15()
    {
        //echo "This..............................................";
        $exps =  factory(Order::class, 15)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orders?start=10'); 
        $res->assertJsonCount(5, 'data');
        $res->assertJson([
            'data' => [
                ['id' => $exps[10]->id],//11th
                ['id' => $exps[11]->id],
                ['id' => $exps[12]->id],
                ['id' => $exps[13]->id],
                ['id' => $exps[14]->id],//15th
            ]
        ]);
    }

    /** @test */
    public function get_search_orders_list_same_one_row()
    {
        $exps = factory(Order::class)->create(['first_name' => 'wai']);
        $exps1 = factory(Order::class)->create(['first_name' => 'mya']);
        $exps2 = factory(Order::class)->create(['city' => 'Myanmar']);
        $exps3 = factory(Order::class)->create(['first_name' => 'phyo']);
        $exps4 = factory(Order::class)->create(['first_name' => 'ei']);
        $exps5 = factory(Order::class)->create(['first_name' => 'hsu']);
        $exps6 = factory(Order::class)->create(['first_name' => 'thit']);
        $exps7 = factory(Order::class)->create(['first_name' => 'myat']);
        $exps8 = factory(Order::class)->create(['first_name' => 'Htay']);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orders?start=0&search=wai');
        $res->assertStatus(200);
       $res->assertExactJson([
            'data' => [
                [
                    'id'=>$exps->id,
                    'total_price'=>$exps->total_price,
                    'first_name'=>$exps->first_name,
                    'last_name'=>$exps->last_name,
                    'address1'=>$exps->address1,
                    'address2'=>$exps->address2,
                    'country'=>$exps->country,
                    'state'=>$exps->state,
                    'city'=>$exps->city,           
                    'created_at' => $this->toMySqlDateFromJson($exps->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps->created_at),
                ],         
            ]
        ]);
    }

    /** @test */
    public function get_all_orders_list_same_search_same_two_row()
    {
        $exps = factory(Order::class)->create(['first_name' => 'wai']);
        $exps1 = factory(Order::class)->create(['first_name' => 'mya']);
        $exps2 = factory(Order::class)->create(['city' => 'Myanmar']);
        $exps3 = factory(Order::class)->create(['first_name' => 'phyo']);
        $exps4 = factory(Order::class)->create(['first_name' => 'ei']);
        $exps5 = factory(Order::class)->create(['first_name' => 'hsu']);
        $exps6 = factory(Order::class)->create(['first_name' => 'thit']);
        $exps7 = factory(Order::class)->create(['first_name' => 'myint']);
        $exps8 = factory(Order::class)->create(['first_name' => 'Htay']);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orders?start=0&search=mya');
        $res->assertStatus(200);
       $res->assertExactJson([
            'data' => [
                [
                    'id'=>$exps1->id,
                    'total_price'=>$exps1->total_price,
                    'first_name'=>$exps1->first_name,
                    'last_name'=>$exps1->last_name,
                    'address1'=>$exps1->address1,
                    'address2'=>$exps1->address2,
                    'country'=>$exps1->country,
                    'state'=>$exps1->state,
                    'city'=>$exps1->city,           
                    'created_at' => $this->toMySqlDateFromJson($exps1->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps1->created_at),
                ],
                [
                    'id'=>$exps2->id,
                    'total_price'=>$exps2->total_price,
                    'first_name'=>$exps2->first_name,
                    'last_name'=>$exps2->last_name,
                    'address1'=>$exps2->address1,
                    'address2'=>$exps2->address2,
                    'country'=>$exps2->country,
                    'state'=>$exps2->state,
                    'city'=>$exps2->city,           
                    'created_at' => $this->toMySqlDateFromJson($exps2->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps2->created_at),
                ],                
            ]
        ]);
    }

    /** @test */ 
    public function no_return_notExists_search()
    {
        $exps = factory(Order::class)->create(['first_name' => 'wai']);
        $exps1 = factory(Order::class)->create(['first_name' => 'mya']);
        $exps2 = factory(Order::class)->create(['city' => 'Myanmar']);
        $exps3 = factory(Order::class)->create(['first_name' => 'phyo']);
        $exps4 = factory(Order::class)->create(['first_name' => 'ei']);
        $exps5 = factory(Order::class)->create(['first_name' => 'hsu']);
        $exps6 = factory(Order::class)->create(['first_name' => 'thit']);
        $exps7 = factory(Order::class)->create(['first_name' => 'myint']);
        $exps8 = factory(Order::class)->create(['first_name' => 'Htay']);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orders?start=4&search=mya');
        $res->assertStatus(200);
        $res->assertJsonCount(0, 'data');
    }
    
    //Start store
         /** @test */
    public function on_store_order_success()
    {
        //echo "This..............................................";

        $order=factory(Order::class)->create();
        $item1 = factory(Item::class)->create();
        $item2 = factory(Item::class)->create();
        $item3 = factory(Item::class)->create();
       //$order = factory(Order::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
            'total_price' => 5000,
            'first_name' => 'Wai',
            'last_name' => 'Phyo',
            'address1' => 'Mandalay',
            'address2' => 'pku',
            'country' => 'Myanmar',
            'state' => 'Yaw',
            'city' => 'Htilin',
            'order_id'=>'$order->id',
            'item_id_array'=>[$item1->id,$item2->id,$item3->id],
            'item_qty_array'=>[3,5,2],
            'item_price_array'=>[1500,1500,2000],
        ]);
        $res->assertStatus(201);
        $res->assertJsonCount(12, 'data');
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
                'updated_at',
                'Orderitem'
            ]
        ]);
        $json = $res->json();//1 is id
        $this->assertEquals(5000, $json['data']['total_price']);//2
        $this->assertEquals('Wai', $json['data']['first_name']);//3
        $this->assertEquals('Phyo', $json['data']['last_name']);//4
        $this->assertEquals('Mandalay', $json['data']['address1']);//5
        $this->assertEquals('pku', $json['data']['address2']);//6
        $this->assertEquals('Myanmar', $json['data']['country']);//7
        $this->assertEquals('Yaw', $json['data']['state']);//8
        $this->assertEquals('Htilin', $json['data']['city']);//9
        $this->assertLessThan(2, time() - strtotime($json['data']['created_at']));//10
        $this->assertLessThan(2, time() - strtotime($json['data']['updated_at']));//11

        $this->assertEquals($json['data']['id'],$json['data']['Orderitem'][0]['order_id']);
        $this->assertEquals($item1->id,$json['data']['Orderitem'][0]['item_id']);
        $this->assertEquals(3,$json['data']['Orderitem'][0]['quantity']);
        $this->assertEquals(1500,$json['data']['Orderitem'][0]['unit_price']);
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][0]['created_at']));
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][0]['updated_at']));
         
        $this->assertEquals($json['data']['id'],$json['data']['Orderitem'][1]['order_id']);
        $this->assertEquals($item2->id,$json['data']['Orderitem'][1]['item_id']);
        $this->assertEquals(5,$json['data']['Orderitem'][1]['quantity']);
        $this->assertEquals(1500,$json['data']['Orderitem'][1]['unit_price']);
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][1]['created_at']));
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][1]['updated_at']));

        $this->assertEquals($json['data']['id'],$json['data']['Orderitem'][0]['order_id']);
        $this->assertEquals($item3->id,$json['data']['Orderitem'][2]['item_id']);
        $this->assertEquals(2,$json['data']['Orderitem'][2]['quantity']);
        $this->assertEquals(2000,$json['data']['Orderitem'][2]['unit_price']);
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][2]['created_at']));
        $this->assertLessThan(2, time() - strtotime($json['data']['Orderitem'][2]['updated_at']));

    }
    
    /** @test */
    public function store_without_postData_will_occur_validation_error()
    {
        //echo "This..............................................";
        $this->expectException(ValidationException::class);
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH);
    }
    
    /** @test */
    public function store_noParentItemId_will_occur_database_error()
    {
        //echo "This..............................................";
        $this->expectException(QueryException::class);
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
    public function store_last_name_length_0_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res->assertStatus(201);
    }
    
    /** @test */
    public function store_last_name_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
    public function store_address2_length_0_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res->assertStatus(201);
    }
    
    /** @test */
    public function store_address2_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
    public function store_state_length_0_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res->assertStatus(201);
    }
    
    /** @test */
    public function store_state_length_1_will_no_validation_error()
    {
        //echo "This..............................................";
        $item =  factory(Item::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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
        $res = $this->withHeaders($this->getAuthHeader())->json('POST', self::API_PATH, [
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

    //Start Show
     /** @test */
    public function on_show_order_success()
    {
        //echo "This..............................................";
        $exps = factory(Order::class, 3)->create();

        $res = $this->withHeaders($this->getAuthHeader())->json('GET', self::API_PATH.'/'.$exps[1]->id); 
        $res->assertStatus(200); 
        $res->assertExactJson([
            'data' => [
                [
                    'id'=>$exps[1]->id,
                    'total_price'=>$exps[1]->total_price,
                    'first_name'=>$exps[1]->first_name,
                    'last_name'=>$exps[1]->last_name,
                    'address1'=>$exps[1]->address1,
                    'address2'=>$exps[1]->address2,
                    'country'=>$exps[1]->country,
                    'state'=>$exps[1]->state,
                    'city'=>$exps[1]->city,           
                    'created_at' => $this->toMySqlDateFromJson($exps[1]->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps[1]->created_at)
            ],
         ]
        ]);
    }
    
    /** @test */
    public function showorder_deletedId_will_occur_error()
    {
        //echo "This..............................................";
        $row = factory(Order::class)->create();
        $row->delete();
        
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\Order] '.$row->id);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', self::API_PATH.'/'.$row->id); 
    }
    
    /** @test */
    public function showorder_notExistsId_will_occur_error()
    {
        //echo "This..............................................";
        $row = factory(Order::class)->create();
        $errorId = $row->id + 1;
        
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\Order] '.$errorId);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', self::API_PATH.'/'.$errorId); 
    }
//End Show
}
