<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmrShawky\LaravelCurrency\Facade\Currency;

class ConverterController extends Controller
{
    public function gethome(){
        $data = Currency::rates()
        ->latest()
        ->symbols(['USD', 'EUR', 'EGP']) //An array of currency codes to limit output currencies
        ->base('GBP') //Changing base currency (default: EUR). Enter the three-letter currency code of your preferred base currency.
        ->amount(5.66) //Specify the amount to be converted
        ->round(2) //Round numbers to decimal places
        ->source('ecb') //Switch data source between forex `default`, bank view or crypto currencies.
        ->get();

        dd($data);

        return view('welcome', ['data'=> $data]);
    }
}
