<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\CatalogResource;
use App\Services\Vsemayki\RestConnectorNew;


class HomeController extends Controller
{
    public function index()
    {
        $clientId = $_ENV['CLIENT_ID'];
        $clientSecret = $_ENV['CLIENT_SECRET'];

        
        $rest = new RestConnectorNew($clientId, $clientSecret);
        
        $result = $rest->sendRequest(
           '/catalog/items',
           [
            'limit' => 10,
            'offset' => 3
           ]

        );

        return $result;

        $products = $result->items;


        
        return new CatalogResource($products);
        
        return view('home', [
            'products' => $products
        ]);
    }

       
}


