<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::when(request()->q, function ($categories) {
            $categories = $categories->where('name', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new CategoryResource(true, 'List of categories', $categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'name'  => [
                'required',
                'string',
                'unique:categories',
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/categories', $image->hashName());

        $category = Category::create([
            'name'  => $request->name,
            'image' => $image->hashName(),
            'slug'  => Str::slug($request->name, '-'),
        ]);

        if ( $category ) {
            return new CategoryResource(true, 'Category created successfully', $category);
        }

        return new CategoryResource(false, 'Category not created', null);

    }

    public function show($id)
    {
        $category = Category::whereId($id)->first();

        if ( $category ) {
            return new CategoryResource(true, 'Category found', $category);
        }

        return new CategoryResource(false, 'Category not found', null);
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name'  => [
                'required',
                'string',
                'unique:categories,name,' . $category->id,
            ],
            'image' => [
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        if ( $request->file('image') ) {
            Storage::disk('local')->delete('public/categories/' . basename($category->image));

            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            $category->update([
                'name'  => $request->name,
                'image' => $image->hashName(),
                'slug'  => Str::slug($request->name, '-'),
            ]);
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if ( $category ) {
            return new CategoryResource(true, 'Category updated successfully', $category);
        }

        return new CategoryResource(false, 'Category not updated', null);

    }

    public function destroy(Category $category)
    {
        Storage::disk('local')->delete('public/categories/' . basename($category->image));

        if ( $category->delete() ) {
            return new CategoryResource(true, 'Category deleted successfully', $category);
        }

        return new CategoryResource(false, 'Category not deleted', null);
    }

}
