<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SIP\UserSIP;

class DesignerController extends Controller
{
    public function getDesigner()
    {
        try {
            $requestName = request()->searchName;

            $designer = UserSIP::select('id', 'username', 'attendance_id', 'seksi', 'sub_section_id')
                                ->where('sub_section_id', '=', 10021)
                                ->whereIn('power_lvl', [4, 6])
                                ->when($requestName, fn ($query) =>
                                    $query->where('username', 'like', "%$requestName%")
                                )->get();

            return response()->json([
                'status' => 'success',
                'data' => $designer,
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

    public function getMerchandiser()
    {
        try {
            $requestName = request()->searchName;

            $merchandiser = UserSIP::select('id', 'username', 'attendance_id', 'seksi', 'sub_section_id')
                                    ->where('sub_section_id', 10022)
                                    ->whereIn('power_lvl', [4, 6])
                                    ->when($requestName, fn ($query) =>
                                        $query->where('username', 'like', "%$requestName%")
                                    )->get();

            return response()->json([
                'status' => 'success',
                'data' => $merchandiser,
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

    public function getLeaderDesigner()
    {
        try {
            $requestName = request()->searchName;

            $leaderDesigner = UserSIP::select('id', 'username', 'attendance_id', 'seksi', 'sub_section_id')
                                    ->where('sub_section_id', '=', 10021)
                                    ->whereNotIn('power_lvl', [4,6])
                                    ->when($requestName, fn ($query) =>
                                        $query->where('username', 'like', "%$requestName%")
                                    )->get();

            return response()->json([
                'status' => 'success',
                'data' => $leaderDesigner,
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
