<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductCatalogController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->with('category:id,name,slug')
            ->where('estado', 'activo')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $products]);
    }
}
