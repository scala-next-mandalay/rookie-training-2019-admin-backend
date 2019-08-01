<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\Order;
use App\Models\Orderitem;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */
    public function orderitems_everyone_can_get_rows()
    {
        //echo "This..............................................";
        $order =  factory(Order::class)->create(['id'=>'100']);
        $order1 =  factory(Order::class)->create(['id'=>'101']);
        $item =  factory(Item::class)->create();
        $exps = factory(Orderitem::class, 2)->create(['order_id' => $order->id,'item_id' => $item->id]);
        $exps1 = factory(Orderitem::class, 2)->create(['order_id' => $order1->id,'item_id' => $item->id]);
       //$res = $this->get('/api/orderitems?order_id='.$order->id);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orderitems?order_id='.$order->id);
        $res->assertStatus(200);
        $res->assertExactJson([
            'data' => [
                [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'first_name' => $order->first_name,
                    'last_name' => $order->last_name,
                    'address1' => $order->address1,
                    'address2' => $order->address2,
                    'city' => $order->city,
                    'country' => $order->country,
                    'state' => $order->state,
                    'orderitems_id' => $exps[0]->id,
                    'item_id'=>$item->id,
                    'name'=>$item->name,
                    'unit_price'=>$exps[0]->unit_price,
                    'quantity'=>$exps[0]->quantity,
                    'created_at' => $this->toMySqlDateFromJson($exps[0]->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps[0]->created_at),           
                ],
                [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'first_name' => $order->first_name,
                    'last_name' => $order->last_name,
                    'address1' => $order->address1,
                    'address2' => $order->address2,
                    'city' => $order->city,
                    'country' => $order->country,
                    'state' => $order->state,
                    'orderitems_id' => $exps[1]->id,
                    'item_id'=>$item->id,
                    'name'=>$item->name,
                    'unit_price'=>$exps[1]->unit_price,
                    'quantity'=>$exps[1]->quantity,
                    'created_at' => $this->toMySqlDateFromJson($exps[1]->updated_at),
                    'updated_at' => $this->toMySqlDateFromJson($exps[1]->created_at),           
                ]
                
                ],         
        ]);
    }

    /** @test */
    public function get_orderitems_with_orderId()
    {
        $order=factory(Order::class)->create(['id'=>'100']);
        $orderid1 =  factory(Orderitem::class)->create();
        $orderid2 =  factory(Orderitem::class)->create(['order_id'=>$order->id]);
        $orderid3 =  factory(Orderitem::class)->create();
        //$res = $this->json('GET', '/api/orderitems?order_id='.$order->id);
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orderitems?order_id='.$order->id); 
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJson([
            'data' => [
                [
                    'orderitems_id' =>$orderid2->id,
                    'id'=>$orderid2->order_id,
                ],
                
            ]
        ]);
    }   

    /** @test */ 
    public function no_return_no_required_orderId()
    {
        $orderitem =  factory(Orderitem::class)->create();
        $res = $this->withHeaders($this->getAuthHeader())->json('GET', '/api/orderitems/'); 
        $res->assertStatus(200);
        $res->assertJsonCount(0, 'data');
    }
}
