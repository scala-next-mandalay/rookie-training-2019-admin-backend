<?php

namespace App\Http\Controllers;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order;
use App\Models\Orderitem;
use App\Http\Requests\OrderItem\IndexOrderItemRequest;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index(IndexOrderItemRequest $request): JsonResource
    {
      $orderitem=Orderitem::
           join('items','items.id','=','orderitems.item_id')
           ->select('orderitems.id','orderitems.item_id','items.name','orderitems.quantity','orderitems.unit_price')
           ->where('order_id','=', $request->order_id)
           ->get();
           return JsonResource::collection($orderitem);
       }
}
