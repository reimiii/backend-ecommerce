<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::latest()->get();

        return new CategoryResource(true, 'List of categories', $categories);
    }

    public function show($slug)
    {
        $category = Category::with('products.category')
            ->with('products', function ($query) {
                $query->withCount('reviews');
                $query->withAvg('reviews', 'rating');
            })
            ->where('slug', $slug)->first();

        if ( $category ) {
            return new CategoryResource(true, 'Category found : ' . $category->name, $category);
        }

        return new CategoryResource(false, 'Category not found', null);
    }

}