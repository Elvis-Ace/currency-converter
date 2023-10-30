<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Torann\Currency\Contracts\FormatterInterface;

class ConverterController extends Controller
{
    public function gethome(){
        $value = 1;
        return $value . ($this->config['append_code'] === true ? $code : '');
        return view('welcome', ['data'=> $data]);
    }
}
