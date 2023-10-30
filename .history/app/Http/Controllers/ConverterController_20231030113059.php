<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmrShawky\LaravelCurrency\Facade\Currency;

class ConverterController extends Controller
{
    public function gethome(){
        $data = Currency::convert()
        ->from('USD')
        ->to('EUR')
        ->get();

        return view('welcome', ['data'=> $data]);
    }
}
