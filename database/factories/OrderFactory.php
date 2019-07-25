<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Order;
use App\Models\Orderitem;
use App\Models\Item;

$factory->define(Order::class, function (Faker $faker) {
    $word = $faker->word();
    $word1 = $faker->word();
    $word2 = $faker->word();
    $word3 = $faker->word();
    $word4 = $faker->word();
    $word5 = $faker->word();
    $word6 = $faker->word();
    return [
        'total_price'=>$faker->numberBetween($min = 100, $max = 100000),
        'first_name'=>$word,
        'last_name'=>$word1,
        'address1'=>$word2,
        'address2'=>$word3,
        'country'=>$word4,
        'state'=>$word5,
        'city'=>$word6
    ];
});
