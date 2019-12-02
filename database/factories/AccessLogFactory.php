<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\AccessLog;
use Faker\Generator as Faker;

$factory->define(AccessLog::class, function (Faker $faker) {
    return [
        'time_in' => now(),
        'nomor_barcode' => Str::random(5),
    ];
});
