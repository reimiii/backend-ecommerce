<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with('category')->when(request()->q, function ($products) {
            $products = $products->where('title', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new ProductResource(true, 'List of products', $products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'       => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'title'       => [
                'required',
                'unique:products',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'description' => [
                'required',
            ],
            'weight'      => [
                'required',
            ],
            'price'       => [
                'required',
            ],
            'stock'       => [
                'required',
            ],
            'discount'    => [
                'required',
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

//        if ( $validator->fails() ) {
//            return response()->json([
//                'success' => false,
//                'message' => $validator->errors(),
//            ], 400);
//        }

        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        $product = Product::create([
            'image'       => $image->hashName(),
            'title'       => $request->title,
            'slug'        => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id'     => auth()->guard('api_admin')->user()->id,
            'description' => $request->description,
            'weight'      => $request->weight,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'discount'    => $request->discount,
        ]);

        if ( $product ) {
            return new ProductResource(true, 'Product created successfully', $product);
        }

        return new ProductResource(false, 'Product creation failed', null);
    }

    public function show($id)
    {
        $product = Product::whereId($id)->first();

        if ( $product ) {
            return new ProductResource(true, 'Product found', $product);
        }

        return new ProductResource(false, 'Product not found', null);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'image'       => [
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'title'       => [
                'required',
                'unique:products,title,' . $product->id,
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'description' => [
                'required',
            ],
            'weight'      => [
                'required',
            ],
            'price'       => [
                'required',
            ],
            'stock'       => [
                'required',
            ],
            'discount'    => [
                'required',
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        if ( $request->file('image') ) {
            Storage::disk('local')->delete('public/products/' . basename($product->image));

            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            $product->update([
                'image'       => $image->hashName(),
                'title'       => $request->title,
                'slug'        => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'user_id'     => auth()->guard('api_admin')->user()->id,
                'description' => $request->description,
                'weight'      => $request->weight,
                'price'       => $request->price,
                'stock'       => $request->stock,
                'discount'    => $request->discount,
            ]);
        }

        $product->update([
            'title'       => $request->title,
            'slug'        => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id'     => auth()->guard('api_admin')->user()->id,
            'description' => $request->description,
            'weight'      => $request->weight,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'discount'    => $request->discount,
        ]);

        if ( $product ) {
            return new ProductResource(true, 'Product updated successfully', $product);
        }

        return new ProductResource(false, 'Product update failed', null);
    }

    public function destroy(Product $product)
    {
        Storage::disk('local')->delete('public/products/' . basename($product->image));

        if ( $product->delete() ) {
            return new ProductResource(true, 'Product deleted successfully', $product);
        }

        return new ProductResource(false, 'Product deletion failed', null);
    }

}