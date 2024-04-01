<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Style, SubStyle};
use Illuminate\Support\Facades\DB;

class StyleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $style = Style::select('id', 'style_name')->get();

            return response()->json([
                'status' => 'success',
                'data' => $style,
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
    public function store(Request $request)
    {
        try {
            $checkExistanceData = $this->checkStyleName($request->style_name);

            if ($checkExistanceData == false) {
                $style = Style::create([
                    'style_name' => ucwords($request->style_name)
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => ($checkExistanceData == false) ? $style : $checkExistanceData,
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
        //
    }

    public function getSubStyle()
    {
        try {
            $styleId = request()->style_id;

            $subStyle = SubStyle::select([
                'sub_styles.id',
                DB::raw('styles.style_name'),
                'sub_style_name'
            ])->leftJoin('styles', 'styles.id', '=', 'sub_styles.sub_style_id')
            ->when($styleId, function ($query) use ($styleId) {
                $query->where('style_id', '=', $styleId);
            })->get();

            return response()->json([
                'status' => 'success',
                'data' => $subStyle,
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

    public function createSubStyle(Request $request)
    {
        try {
            $subStyle = SubStyle::create([
                'sub_style_id' => $request->style_id,
                'sub_style_name' => $request->sub_style_name
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $subStyle,
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

    public function deleteSubStyle($id)
    {
        try {
            $subStyle = SubStyle::where('id', '=', $id)->first();
            $subStyle->delete();

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

    private function checkStyleName($styleName)
    {
        $explode = explode(' ', $styleName);
        $implode = implode(' ', $explode);

        $style = Style::where('style_name', '=', ucwords($implode))->first();

        return ($style) ? $style : false;
    }
}
