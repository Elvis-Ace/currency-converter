<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmrShawky\LaravelCurrency\Facade\Currency;

class ConverterController extends Controller
{
    public function gethome(){
        $value = 1;
        return $value . ($this->config['append_code'] === true ? $code : '');
        return view('welcome', ['data'=> $data]);
    }
}
