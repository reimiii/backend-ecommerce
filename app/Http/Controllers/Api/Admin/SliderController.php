<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{

    public function index()
    {
        $sliders = Slider::latest()->paginate(5);

        return new SliderResource(true, 'Sliders list', $sliders);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048'
            ],
            'link'  => [
                'nullable',
                'url'
            ]
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/sliders', $image->hashName());

        $slider = Slider::create([
            'image' => $image->hashName(),
            'link'  => $request->link
        ]);

        if ( $slider ) {
            return new SliderResource(true, 'Slider created successfully', $slider);
        }

        return new SliderResource(false, 'Slider not created', null);

    }

    public function destroy(Slider $slider)
    {
        Storage::disk('local')->delete('public/sliders/' . basename($slider->image));

        if ( $slider->delete() ) {
            return new SliderResource(true, 'Slider deleted successfully', $slider);
        }

        return new SliderResource(false, 'Slider not deleted', null);
    }

}
