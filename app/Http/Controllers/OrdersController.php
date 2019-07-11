<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order;
use App\Models\Orderitem;
use App\Http\Requests\Order\StoreOrderRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrdersController extends Controller
{
   public function store(StoreOrderRequest $request)
   {
   	return \DB::transaction(function() use($request){
   		$data=$request->validated();

   		$orderKeys=['total_price','first_name','last_name','address1','address2','country','state','city'];
   		$orderArr=[];
   		foreach ($orderKeys as $key) {
   			$orderArr[$key]=$data[$key];
   		}
   		$orderModel=Order::create($orderArr);

   		foreach ($data['item_id_array'] as $i => $itemId) {
   			$itemArr=[
   				    'order_id' => $orderModel->id,
                    'item_id' => $itemId,
                    'unit_price' => $data['item_price_array'][$i],
                    'quantity' => $data['item_qty_array'][$i]
   			];
   			$dump[]=Orderitem::create($itemArr);
   		}
   		$orderModel->Orderitem=$dump;
   		return new JsonResource($orderModel);
   	});
   }

   public function index(): ResourceCollection
    {
        return JsonResource::collection(
            Order::orderBy('id')->get()
        );
    }

}
