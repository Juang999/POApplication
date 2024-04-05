<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryPartnumber;

class CategoryPartnumberController extends Controller
{
    public function index()
    {
        try {
            $dataCategoryPartnumber = CategoryPartnumber::select(['id','category_code','category_translate','category_description'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $dataCategoryPartnumber,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $dataCategory = CategoryPartnumber::create([
                'category_code' => $request->category_code,
                'category_translate' => $request->category_translate,
                'category_description' => $request->category_description
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $dataCategory,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $dataCategory = CategoryPartnumber::where('id', '=', $id)->first();
            $dataCategory->delete();

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
