<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = Product::all();
        return AllProductResource::collection($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|numeric',
            'materials' => 'required|array',
            'materials. *.id' => 'required|exists:materials,id',
            'materials. *.quantity' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = Product::create(
            [
                'name' => $request->name,
                'code' => $request->code,
            ]
        );
        $materials = collect($request->materials)->map(function ($material) use ($model) {
            return [
                'product_id' => $model->id,
                'material_id' => $material['id'],
                'quantity' => $material['quantity'],
            ];
        })->toArray();
        $model->productMaterials()->createMany($materials);
        return new SingleProductResource($model);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = Product::find($id);
        return new SingleProductResource($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|numeric',
            'materials' => 'required|array',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = Product::find($id);
        if ($model) {
            $model->update([
                'name' => $request->name,
                'code' => $request->code,
            ]);
            $materials = collect($request->materials)->map(function ($material) use ($model) {
                return [
                    'product_id' => $model->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                ];
            })->toArray();
            $model->productMaterials()->delete();
            $model->productMaterials()->createMany($materials);
            return new SingleProductResource($model);
        }
        return response()->json(['data' => []]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Product::find($id);
        if ($model) {
            $model->delete();
        }
        return response()->json(['data' => []]);
    }
}
