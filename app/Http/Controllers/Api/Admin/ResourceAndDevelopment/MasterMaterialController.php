<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterMaterial;
use App\Http\Requests\Admin\SampleProduct\CreateMasterMaterialRequest;
use Illuminate\Support\Facades\Http;

class MasterMaterialController extends Controller
{
    public $url;

    public function __construct()
    {
        $this->url = env('URL_EXAPRO');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $searchName = (request()->searchname) ? request()->searchname : '';
            $exapro = $this->url;

            $dataMaterial = Http::get("$exapro/api/master/get-material?searchname=$searchName");
            $masterMaterial = json_decode($dataMaterial->body());

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial->data,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getmessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateMasterMaterialRequest $request)
    {
        try {
            $masterMaterial = MasterMaterial::create([
                'material_name' => $request->material_name,
                'material_description' => $request->material_description,
                'material_function' => $request->material_function,
                'material_photo' => $request->material_photo,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial,
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $masterMaterial = MasterMaterial::where('id', '=', $id)->first();
            $masterMaterial->delete();

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

    public function getAdditionalMaterial()
    {
        try {
            $masterMaterial = MasterMaterial::select(
                                'id',
                                'material_name',
                                'material_description',
                                'material_photo'
                            )->where('material_function', '=', 'is_additional')
                            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getmessage()
            ], 400);
        }
    }

    public function getProductAccessories()
    {
        try {
            $masterMaterial = MasterMaterial::select(
                                'id',
                                'material_name',
                                'material_description',
                                'material_photo'
                            )->where('material_function', '=', 'product_accessories')
                            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getmessage()
            ], 400);
        }
    }

    public function getAdditionalAccessories()
    {
        try {
            $masterMaterial = MasterMaterial::select(
                                'id',
                                'material_name',
                                'material_description',
                                'material_photo'
                            )->where('material_function', '=', 'material_accessories')
                            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getmessage()
            ], 400);
        }
    }

    public function getMaterialFunction()
    {
        try {
            $materialFunction = [
                [
                    'material_function' => 'is_main',
                ],[
                    'material_function' => 'is_additional',
                ],[
                    'material_function' => 'product_accessories',
                ],[
                    'material_function' => 'material_accessories',
                ],
            ];

            return response()->json([
                'status' => 'success',
                'data' => $materialFunction,
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

    public function getAccessories()
    {
        try {
            $searchName = (request()->searchname) ? request()->searchname : '';
            $exapro = $this->url;

            $dataMaterial = Http::get("$exapro/api/master/get-accessories?searchname=$searchName");
            $masterMaterial = json_decode($dataMaterial->body());

            return response()->json([
                'status' => 'success',
                'data' => $masterMaterial->data,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getmessage()
            ], 400);
        }
    }
}
