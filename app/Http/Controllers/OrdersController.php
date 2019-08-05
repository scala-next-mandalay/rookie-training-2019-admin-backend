<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order;
use App\Models\Orderitem;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\IndexOrderRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class OrdersController extends Controller
{
    const STORE_COLUMNS = ['total_price','first_name','last_name','address1','address2','country','state','city'];
    public function store(StoreOrderRequest $request)
    {
        return \DB::transaction(function() use($request)
        {
            $data=$request->validated();
            $orderArr=[];
            foreach (self::STORE_COLUMNS as $key) 
            {
                $orderArr[$key]=$data[$key];
            }
            $orderModel=Order::create($orderArr);

            foreach ($data['item_id_array'] as $i => $itemId) 
            {
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
   
    public function index(IndexOrderRequest $request)
    {
        $builder=Order::query();
        $builder->take(10);
        
        $start = $request->start ? $request->start : 0;
        $builder->skip($request->start);

        $searchColumns = array_merge(self::STORE_COLUMNS, ['created_at']);
        if ($request->sortcol && in_array($request->sortcol, $searchColumns)) {
            $sortorder = (strtoupper($request->sortorder) === 'DESC') ? 'DESC': 'ASC';
            $builder->orderBy($request->sortcol, $sortorder);
        }
        else {
            $builder->orderBy('id');
        }
        
        if ($request->search) 
        {
            $builder
            ->where('first_name', 'like', '%' .$request->search. '%')
            ->orwhere('last_name', 'like', '%' .$request->search. '%')
            ->orwhere('total_price', 'like', '%' .$request->search. '%')
            ->orwhere('address1', 'like', '%' .$request->search. '%')
            ->orwhere('address2', 'like', '%' .$request->search. '%')
            ->orwhere('country', 'like', '%' .$request->search. '%')
            ->orwhere('state', 'like', '%' .$request->search. '%')
            ->orwhere('city', 'like', '%' .$request->search. '%');
        }


        return JsonResource::collection($builder->get());
    }

    public function show(Order $order): JsonResource
    {
        $ord=Order::where('id','=', $order->id)->get();
        return JsonResource::collection($ord);
    }

}
