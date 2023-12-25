<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllMaterialResource;
use App\Http\Resources\SingleMaterialResource;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = Material::all();
        return AllMaterialResource::collection($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = Material::create(
            [
                'name' => $request->name,
            ]
        );
        return new SingleMaterialResource($model);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = Material::find($id);
        return new SingleMaterialResource($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = Material::find($id);
        if ($model) {
            $model->update([
                'name' => $request->name,
            ]);
        }
        return new SingleMaterialResource($model);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Material::find($id);
        if ($model) {
            $model->delete();
        }
        return new SingleMaterialResource($model);
    }
}
