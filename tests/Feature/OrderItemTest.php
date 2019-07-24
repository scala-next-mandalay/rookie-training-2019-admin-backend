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
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function orderitems_everyone_can_get_rows()
    {
        //echo "This..............................................";
        $order =  factory(Order::class)->create();
        $item =  factory(Item::class)->create();
        $exps = factory(Orderitem::class, 2)->create(['order_id' => $order->id,'item_id' => $item->id]);
       $res = $this->get('/api/orderitems?order_id=1');
       $res->assertStatus(200);
       $res->assertExactJson([
            'data' => [
                [
                    'id' => $exps[0]->id,
                    'item_id'=>$item->id,
                    'name'=>$item->name,
                    'unit_price'=>$exps[0]->unit_price,
                    'quantity'=>$exps[0]->quantity,           
                    
                ],
                [
                 'id' => $exps[1]->id,
                    'item_id'=>$item->id,
                    'name'=>$item->name,
                    'unit_price'=>$exps[1]->unit_price,
                    'quantity'=>$exps[1]->quantity,           
                ]

                ],         
            
        ]);
    }
}
