<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Order;
use App\Models\Orderitem;
use App\Models\Item;

$factory->define(Model::class, function (Faker $faker) {
	$word = $faker->word();
    return [
        'total_price'=>$faker->numberBetween($min = 100, $max = 100000),
       'first_name'=>$word,
       'last_name'=>$word,
       'address1'=>$word,
       'address2'=>$word,
       'country'=>$word,
       'state'=>$word,
       'city'=>$word
    ];
});
