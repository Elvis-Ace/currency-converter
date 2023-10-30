<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmrShawky\LaravelCurrency\Facade\Currency;

class ConverterController extends Controller
{
    public function gethome(){
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://currency-converter-by-api-ninjas.p.rapidapi.com/v1/convertcurrency?have=USD&want=EUR&amount=5000', [
            'headers' => [
                'X-RapidAPI-Host' => 'currency-converter-by-api-ninjas.p.rapidapi.com',
                'X-RapidAPI-Key' => '18f0ccf186msh264990c2b8a8938p1d6cb5jsn35b9540c8420',
            ],
        ]);

        dd($response->getBody());
    }
}
