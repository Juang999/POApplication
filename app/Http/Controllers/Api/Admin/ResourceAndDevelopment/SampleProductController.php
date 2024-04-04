<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Hash, DB};
use App\Http\Requests\Admin\SampleProduct\{
    SampleProductRequest,
    InsertSamplePhotoRequest,
    InputFabricTextureRequest,
    UpdateSampleProductRequest,
    InputSampleDesignRequest,
    CreateMasterMaterialRequest
};
use App\{
    User,
    Models\SIP\UserSIP,
    Models\SampleDesign,
    Models\SampleProduct,
    Models\FabricTexture,
    Models\MasterMaterial,
    Models\SampleProductPhoto,
    Models\HistorySampleProduct,
    Models\HistoryFabricTexture,
    Models\HistorySampleProductPhoto,
};
use Spatie\Activitylog\Models\Activity;

class SampleProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $requestArticle = request()->article;
            $requestEntity = request()->entity;

            $sampleProduct = SampleProduct::select(
                'id',
                'date',
                'article_name',
                'entity_name',
            )->with(['Thumbnail' => function ($query) {
                $query->select('sample_product_id', 'sequence', 'photo');
            }])->when($requestArticle, function ($query) use ($requestArticle) {
                $query->where('article_name', 'like', "%$requestArticle%");
            })->when($requestEntity, function ($query) use ($requestEntity) {
                $query->where('entity_name', '=', $requestEntity);
            })->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $sampleProduct,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SampleProductRequest $request)
    {
        try {
            DB::beginTransaction();
                $designerId = $this->createUserStaffDesigner($request->designer_id);
                $merchandiserId = $this->createUserStaffDesigner($request->md_id);
                $leaderDesignerId = $this->createUserStaffDesigner($request->leader_designer_id);

                $sampleProduct = SampleProduct::create([
                    'date' => $request->date,
                    'reference_sample_id' => $request->reference_sample_id,
                    'article_name' => $request->article_name,
                    'style_id' => $request->style_id,
                    'sub_style_id' => ($request->sub_style_id) ? $request->sub_style_id : null,
                    'entity_name' => $request->entity_name,
                    'material' => '-',
                    'size' => $request->size,
                    'accessories' => $request->accessories,
                    'note_and_description' => ($request->note_description) ? $request->note_description : '-',
                    'design_file' => $request->design_file,
                    'designer_id' => $designerId,
                    'md_id' => $merchandiserId,
                    'leader_designer_id' => $leaderDesignerId,
                ]);

                $this->inputSamplePhoto(['sp_id' => $sampleProduct->id, 'photo' => $request->photo]);
                $this->inputFabricPhoto(['sample_product_id' => $sampleProduct->id, 'material_id' => $request->material_id]);
                $this->helperInputSampleDesign(['design_photo' => $request->sample_design, 'sample_product_id' => $sampleProduct->id]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $sampleProduct,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage(),
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
        try {
            $sampleProduct = SampleProduct::select(
                'sample_products.id',
                'date',
                'article_name',
                'entity_name',
                DB::raw('styles.style_name AS style_name'),
                'material',
                'size',
                'accessories',
                'note_and_description',
                'design_file',
                'designer_id',
                DB::raw('designer.name AS designer_name'),
                'md_id',
                DB::raw('merchandiser.name AS merchandiser_name'),
                'leader_designer_id',
                DB::raw('designer_leader.name AS designer_leader_name'),
            )->leftJoin(DB::raw('users AS designer'), 'designer.attendance_id', '=', 'sample_products.designer_id')
            ->leftJoin(DB::raw('users AS merchandiser'), 'merchandiser.attendance_id', '=', 'sample_products.md_id')
            ->leftJoin(DB::raw('users AS designer_leader'), 'designer_leader.attendance_id', '=', 'sample_products.leader_designer_id')
            ->leftJoin('styles', 'styles.id', '=', 'sample_products.style_id')
            ->with([
                    'PhotoSampleProduct' => fn ($query) => $query->select('id', 'sample_product_id', 'sequence', 'photo')->orderBy('sequence', 'ASC'),
                    'FabricTexture' => fn ($query) => $query->select('id', 'sample_product_id', 'description', 'photo')->orderBy('sequence', 'ASC'),
                    'SampleDesign' => fn ($query) => $query->select('id', 'sample_product_id', 'design_photo')
                ])->find($id);

            return response()->json([
                'status' => 'success',
                'data' => $sampleProduct,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSampleProductRequest $request, $id)
    {
        try {
            $requestSampleProduct = $this->requestUpdateSampelProduct($request, $id);

            $requests = $requestSampleProduct['requests'];
            $sampleProduct = $requestSampleProduct['sampleProduct'];

            DB::beginTransaction();
                $sampleProduct->update([
                    'date' => $requests['date'],
                    'article_name' => $requests['article_name'],
                    'style_id' => $requests['style_id'],
                    'sub_style_id' => $requests['sub_style_id'],
                    'entity_name' => $requests['entity_name'],
                    'material' => $requests['material'],
                    'size' => $requests['size'],
                    'accessories' => $requests['accessories'],
                    'note_and_description' => $requests['note_and_description'],
                    'design_file' => $requests['design_file'],
                    'designer_id' => $requests['designer_id'],
                    'md_id' => $requests['md_id'],
                    'leader_designer_id' => $requests['leader_designer_id'],
                ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
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
            DB::beginTransaction();
                $this->deleteFabric($id);
                $this->deleteSamplePhoto($id);
                $this->helperDeleteSampleDesign($id);

                $dataSampleProduct = SampleProduct::where('id', '=', $id)->first();
                $dataSampleProduct->delete();
            DB::commit();
                return response()->json([
                    'status' => 'success',
                    'data' => true,
                    'error' => null
                ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function getHistorySample($id)
    {
        try {
            $historySample = Activity::query()->select([
                                'activity_log.id',
                                'subject_id',
                                'log_name',
                                'description',
                                DB::raw("CASE
                                    WHEN subject_type LIKE '%SampleProductPhoto' THEN 'photo'
                                    WHEN subject_type LIKE '%FabricTexture' THEN 'fabric-texture'
                                    ELSE 'sample-product'
                                END AS log_type"),
                                DB::raw('causer.name AS causer_name'),
                                'activity_log.created_at',
                                DB::raw('designer.name AS designer_name'),
                                DB::raw('merchandiser.name AS merchandiser_name'),
                                DB::raw('leader_designer.name AS leader_designer_name'),
                                'properties'
                            ])->leftJoin(DB::raw('users AS causer'), 'causer.id', '=', 'activity_log.causer_id')
                            ->leftJoin(DB::raw('users AS designer'), 'designer.attendance_id', '=', 'properties->attributes->designer_id')
                            ->leftJoin(DB::raw('users AS merchandiser'), 'merchandiser.attendance_id', '=', 'properties->attributes->md_id')
                            ->leftJoin(DB::raw('users AS leader_designer'), 'leader_designer.attendance_id', '=', 'properties->attributes->leader_designer_id')
                            ->where(function ($query) use ($id) {
                                $query->where('subject_type', '=',  'App\Models\SampleProduct')
                                    ->where('subject_id', '=', $id);
                            })
                            ->orWhere(function ($query) use ($id) {
                                $query->whereIn('subject_type', ['App\Models\SampleProductPhoto', 'App\Models\FabricTexture'])
                                    ->where('properties->attributes->sample_product_id', '=', $id);
                            })
                            ->orderByDesc('activity_log.created_at')
                            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $historySample,
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

    public function deletePhoto($id, $sampleProductId)
    {
        try {
            $data = SampleProductPhoto::where([['id', '=', $id], ['sample_product_id', '=', $sampleProductId]])->first();

            $data->delete();

            return response()->json([
                'status' => 'successs',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function insertSamplePhoto(InsertSamplePhotoRequest $request)
    {
        try {
            $this->inputSamplePhoto(['sp_id' => $request->sample_product_id, 'photo' => $request->photo]);

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

    public function getAllHistory()
    {
        try {
            $startDate = (request()->start_date) ? Carbon::parse(request()->start_date)->format('Y-m-d 00:00:00') : Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
            $endDate = (request()->end_date) ? Carbon::parse(request()->end_date)->format('Y-m-d 23:59:59') : Carbon::now()->endOfMonth()->format('Y-m-d 23:59:59');


            $dataHistory = Activity::select(
                                        'activity_log.id',
                                        'activity_log.subject_id',
                                        DB::raw('users.name'),
                                        DB::raw('designer.name AS designer_name'),
                                        DB::raw('merchandiser.name AS merchandiser_name'),
                                        DB::raw('leader_designer.name AS leader_designer_name'),
                                        'properties',
                                        'activity_log.created_at'
                                )->where([['subject_type', '=', 'App\Models\SampleProduct'], ['description', '=', 'deleted']])
                                ->whereBetween('activity_log.created_at', [$startDate, $endDate])
                                ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
                                ->leftJoin(DB::raw('users AS designer'), 'designer.attendance_id', '=', 'properties->attributes->designer_id')
                                ->leftJoin(DB::raw('users AS merchandiser'), 'merchandiser.attendance_id', '=', 'properties->attributes->md_id')
                                ->leftJoin(DB::raw('users AS leader_designer'), 'leader_designer.attendance_id', '=', 'properties->attributes->leader_designer_id')
                                ->orderByDesc('activity_log.created_at')
                                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $dataHistory,
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

    public function deleteFabricTexture($id, $sampleProductId)
    {
        try {
            $dataFabric = FabricTexture::where([['id', '=', $id], ['sample_product_id', '=', $sampleProductId]])->first();

            $dataFabric->delete();

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function inputSampleDesign(InputSampleDesignRequest $request)
    {
        try {
            $this->helperInputSampleDesign(['design_photo' => $request->design_photo, 'sample_product_id' => $request->sample_product_id]);

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

    public function deleteSampleDesign($id)
    {
        try {
            $dataDesignSample = SampleDesign::where('id', '=', $id)->first();

            $dataDesignSample->delete();

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

    public function inputFabricTexture(InputFabricTextureRequest $request)
    {
        try {
            $this->inputFabricPhoto([
                'photo_fabric' => $request->fabric_photo,
                'sample_product_id' => $request->sample_product_id,
                'description_fabric' => $request->fabric_description,
            ]);

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

    public function getReferencesSample()
    {
        try {
            $searchSample = request()->sample;

            $sampleProduct = SampleProduct::select('id', 'article_name')
                                        ->when($searchSample, fn ($query) => $query->where('article_name', 'LIKE', "%$searchSample%"))
                                        ->get();

            return response()->json([
                'status' => 'success',
                'data' => $sampleProduct,
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

    private function deleteFabric($sampleProductId)
    {
        $dataFabric = FabricTexture::select('id')->where('sample_product_id', '=', $sampleProductId)->get();

        if ($dataFabric != null) {
            collect($dataFabric)->map(function ($item) {
                $data = FabricTexture::where('id', '=', $item->id)->first();

                $data->delete();
            });
        }
    }

    private function deleteSamplePhoto($sampleProductId)
    {
        $dataSamplePhoto = SampleProductPhoto::select('id')->where('sample_product_id', '=', $sampleProductId)->get();

        if ($dataSamplePhoto != null) {
            collect($dataSamplePhoto)->map(function ($item) {
                $data = SampleProductPhoto::where('id', '=', $item->id)->first();

                $data->delete();
            });
        }
    }

    private function inputSamplePhoto(array $request)
    {
        $photos = explode(',', $request['photo']);
        $sequence = $this->getSequencePhoto($request['sp_id']) + 1;

        foreach ($photos as $photo) {
            SampleProductPhoto::create([
                            'sample_product_id' => $request['sp_id'],
                            'sequence' => $sequence,
                            'photo' => $photo
                        ]);

            $sequence += 1;
        }
    }

    private function getSequencePhoto($sp_id)
    {
        $dataSequence = SampleProductPhoto::select('sequence')
                                        ->where('sample_product_id', '=', $sp_id)
                                        ->latest()
                                        ->first();

        return ($dataSequence) ? $dataSequence->sequence : 0;
    }

    private function requestUpdateSampelProduct($request, $id)
    {
        $sampleProduct = SampleProduct::select('id','date','article_name', 'style_id', 'sub_style_id', 'entity_name', 'material', 'size', 'accessories', 'note_and_description', 'design_file', 'designer_id', 'md_id', 'leader_designer_id')
                                    ->where('id', '=', $id)
                                    ->first();

        $merchandiserId = ($request->md_id) ? $this->createUserStaffDesigner($request->md_id) : $sampleProduct->md_id;
        $designerId = ($request->designer_id) ? $this->createUserStaffDesigner($request->designer_id) : $sampleProduct->designer_id;
        $leaderDesignerId = ($request->leader_designer_id) ? $this->createUserStaffDesigner($request->leader_designer_id) : $sampleProduct->leader_designer_id;

        $requests = [
            'date' => ($request->date) ? $request->date : $sampleProduct->date,
            'article_name' => ($request->article_name) ? $request->article_name : $sampleProduct->article_name,
            'style_id' => ($request->style_id) ? $request->style_id : $sampleProduct->style_id,
            'sub_style_id' => ($request->sub_style_id) ? $request->sub_style_id : $sampleProduct->sub_style_id,
            'entity_name' => ($request->entity_name) ? $request->entity_name : $sampleProduct->entity_name,
            'material' => ($request->material) ? $request->material : $sampleProduct->material,
            'size' => ($request->size) ? $request->size : $sampleProduct->size,
            'accessories' => ($request->accessories) ? $request->accessories : $sampleProduct->accessories,
            'note_and_description' => ($request->note_description) ? $request->note_description : $sampleProduct->note_and_description,
            'design_file' => ($request->design_file) ? $request->design_file : $sampleProduct->design_file,
            'designer_id' => $designerId,
            'md_id' => $merchandiserId,
            'leader_designer_id' => $leaderDesignerId,
        ];

        $data = compact('sampleProduct', 'requests');

        return $data;
    }

    private function inputFabricPhoto($request)
    {
        $materialId = explode(',', $request['material_id']);
        $sampleProductId = $request['sample_product_id'];

        collect($materialId)->each(function ($item, $index) use ($sampleProductId) {
            $masterMaterial = MasterMaterial::find($item);

            FabricTexture::create([
                'sample_product_id' => $sampleProductId,
                'master_material_id' => $masterMaterial->id,
                'material_name' => $masterMaterial->material_name,
                'description' => $masterMaterial->material_description,
                'photo' => $masterMaterial->material_photo,
                'sequence' => $index + 1,
            ]);
        });
    }

    private function createUserStaffDesigner($attendanceId)
    {
        $checkUser = User::where('attendance_id', '=', $attendanceId)->first();

        $userSIP = UserSIP::select('username', 'password', 'attendance_id', 'sub_section_id', 'seksi', 'data_karyawans.nip', 'data_karyawans.img_karyawan')
                                ->leftJoin('detail_users', 'detail_users.id', '=', 'users.detail_user_id')
                                ->leftJoin('data_karyawans', 'data_karyawans.id', '=', 'detail_users.data_karyawan_id')
                                ->where('users.attendance_id', '=', $attendanceId)
                                ->first();

        if($checkUser == null) {
            User::create([
                'name' => $userSIP->username,
                'email' => "$userSIP->username@mutif.atpo",
                'password' => $$userSIP->password,
                'attendance_id' => $userSIP->attendance_id,
                'sub_section_id' => $userSIP->sub_section_id,
                'sub_section' => $userSIP->seksi,
                'nip' => $userSIP->nip,
                'photo' => $userSIP->img_karyawan
            ]);
        } else {
            User::where('attendance_id', '=', $attendanceId)->update([
                'name' => $userSIP->username,
                'email' => "$userSIP->username@mutif.atpo",
                'password' => $userSIP->password,
                'attendance_id' => $userSIP->attendance_id,
                'sub_section_id' => $userSIP->sub_section_id,
                'sub_section' => $userSIP->seksi,
                'nip' => $userSIP->nip,
                'photo' => $userSIP->img_karyawan
            ]);
        }

        return $attendanceId;
    }

    private function helperInputSampleDesign($request)
    {
        $explodedData = explode(',', $request['design_photo']);

        collect($explodedData)->map(function ($item, $index) use ($request) {
            $sampleProductId = $request['sample_product_id'];

            $index++;

            SampleDesign::create([
                'sample_product_id' => $sampleProductId,
                'sequence' => $index++,
                'design_photo' => $item
            ]);
        });
    }

    private function helperDeleteSampleDesign($sampleProductId)
    {
        $dataSampleDesign = SampleDesign::where('sample_product_id', '=', $sampleProductId)->get('id');

        if ($dataSampleDesign) {
            foreach ($dataSampleDesign as $sampleDesign) {
                $dataSample = SampleDesign::where('id', '=', $sampleDesign['id'])->first();

                $dataSample->delete();
            }
        }
    }
}
