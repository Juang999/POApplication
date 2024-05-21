<?php

namespace App\Http\Controllers\Api\Admin\ResourceAndDevelopment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\{User, Models\SIP\UserSIP};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Hash, DB, Auth};
use App\Http\Requests\Admin\Voting\{
    CreateVotingEventRequest,
    InviteMemberRequest,
    AddSampleRequest,
    LoginVotingRequest,
    VoteSampleRequest,
    UpdateEventRequest
};
use App\Models\{
    VotingEvent,
    VotingMember,
    VotingSample,
    VotingScore,
    SampleProduct
};

class VotingController extends Controller
{
    public function getAllEvent()
    {
        try {
            $searchName = request()->search;

            $dataEvent = VotingEvent::select([
                                        'voting_events.id',
                                        'title',
                                        'type',
                                        'is_activate',
                                        DB::raw('AVG(voting_scores.score) AS average_score'),
                                        DB::raw('CASE WHEN AVG(voting_scores.score) IS NULL THEN true ELSE false END AS status_edit')
                                    ])->leftJoin('voting_scores', 'voting_scores.voting_event_id', '=', 'voting_events.id')
                                    ->when($searchName, function ($query) use ($searchName) {
                                        $query->where('title', 'like', "%$searchName%");
                                    })->groupBy([
                                        'voting_events.id',
                                        'title',
                                        'is_activate'
                                    ])->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $dataEvent,
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

    public function getDetailEvent($id)
    {
        try {
            $event = VotingEvent::query()->select('voting_events.id', 'start_date', 'title', 'type', 'description', 'voting_events.created_by', 'voting_events.updated_by', 'voting_events.created_at')
                                ->with([
                                    'Member' => fn ($query) =>
                                                        $query->select(
                                                                    'voting_members.id',
                                                                    'voting_event_id',
                                                                    DB::raw('users.attendance_id'),
                                                                    DB::raw('users.name'),
                                                                    'users.nip',
                                                                    'users.photo'
                                                                )->leftJoin('users', 'users.attendance_id', '=', 'voting_members.attendance_id'),
                                    'Sample' => fn ($query) =>
                                                        $query->select(
                                                                    DB::raw('voting_samples.id as vid'),
                                                                    'voting_event_id',
                                                                    'sample_product_id',
                                                                    DB::raw('sample_products.id'),
                                                                    DB::raw('sample_products.article_name'),
                                                                    DB::raw('sample_products.entity_name'),
                                                                    'show'
                                                                )->leftJoin('sample_products', 'sample_products.id', '=', 'voting_samples.sample_product_id')
                                                                ->with([
                                                                        'Thumbnail' => fn ($query) => $query->select('sample_product_id', 'photo'),
                                                                        // 'VotingScore' => fn ($query) => $query->select('sample_id', DB::raw('users.name'), 'score')->leftJoin('users', 'users.attendance_id', '=', 'voting_scores.attendance_id')
                                                                    ])
                                    // 'Sample.Thumbnail'
                                ])->find($id);

            return response()->json([
                'status' => 'success',
                'data' => $event,
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

    public function createEvent(CreateVotingEventRequest $request)
    {
        try {
            $userId = Auth::user()->id;
            $user = DB::table('users')->select('name')->where('id', '=', $userId)->first();
            DB::beginTransaction();
                $dataEventVoting = VotingEvent::create([
                    'start_date' => $request->start_date,
                    'title' => $request->title,
                    'description' => $request->description,
                    'type' => $request->type,
                    'created_by' => $user->name
                ]);

                $this->inputSample($request->sample_id, $dataEventVoting->id);
                $this->inputMember($request->member_attendance_id, $dataEventVoting->id);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $dataEventVoting,
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

    public function inviteMember(InviteMemberRequest $request)
    {
        try {
            $this->inputMember($request->attendance_id, $request->event_id);

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

    public function addNewSample(AddSampleRequest $request)
    {
        try {
            $this->inputSample($request->sample_id, $request->event_id);

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

    public function getSample()
    {
        try {
            $querySearch = request()->search_article;

            $dataSample = SampleProduct::query()->select('id', 'article_name', 'entity_name')
                                    ->with(['Thumbnail' => function ($query) {
                                        $query->select('sample_product_id', 'sequence', 'photo');
                                    }])
                                    ->when($querySearch, function ($query) use ($querySearch) {
                                        $query->where('article_name', 'LIKE', "%$querySearch%");
                                    })
                                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => $dataSample,
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

    public function updateEvent(UpdateEventRequest $request, $id)
    {
        try {
            $updateRequest = $this->updateRequestEvent($request, $id);

            $dataEvent = $updateRequest['votingEvent'];
            $requests = $updateRequest['requests'];

            $dataEvent->update([
                'start_date' => $requests['start_date'],
                'title' => $requests['title'],
                'description' => $requests['description'],
                'updated_by' => $requests['updated_by'],
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

    public function deleteEvent($id)
    {
        try {
            DB::beginTransaction();
                $this->deleteInvitation($id);
                $this->deleteSample($id);

                $dataEvent = VotingEvent::find($id);

                if($dataEvent) {
                    $dataEvent->delete();
                }
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

    public function removeInvitation($id, $attendanceId)
    {
        try {
            $dataInvitation = VotingMember::where([['id', '=', $id],['attendance_id', '=', $attendanceId]])->first();

            $dataInvitation->delete();

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

    public function removeSample($id, $sampleId)
    {
        try {
            $dataSample = VotingSample::where([['id', '=', $id], ['sample_product_id', '=', $sampleId]])->first();

            $dataSample->delete();

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

    public function showingSampleForAdmin($id)
    {
        try {
            $this->turnOff($id);

            VotingSample::where('id', '=', $id)
                    ->update([
                        'show' => true
                    ]);

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => false,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function getActiveEvent()
    {
        try {
            $event = VotingEvent::select([
                                    'voting_events.id',
                                    'start_date',
                                    'title',
                                    'description',
                                    'is_activate',
                                    DB::raw('MAX(voting_scores.score) AS highest_score'),
                                    DB::raw('AVG(voting_scores.score) AS average_score'),
                                    DB::raw('MIN(voting_scores.score) AS lowest_score'),
                                    DB::raw('COUNT(DISTINCT(voting_scores.attendance_id)) AS followed_participant'),
                                    'created_by',
                                    'updated_by',
                                    'voting_events.created_at',
                                    'voting_events.updated_at',
                                ])->leftJoin('voting_scores', 'voting_scores.voting_event_id', '=', 'voting_events.id')
                                ->where('is_activate', '=', true)
                                ->groupBy([
                                    'id',
                                    'start_date',
                                    'title',
                                    'description',
                                    'is_activate',
                                    'created_by',
                                    'updated_by',
                                    'voting_events.created_at',
                                    'voting_events.updated_at',
                                ])->with([
                                    'Highest' => function ($query) {
                                        $query->select('voting_event_id', DB::raw('sample_products.article_name AS article_name'), DB::raw('AVG(score) AS highest_score'))
                                                ->leftJoin('sample_products', 'sample_products.id', '=', 'voting_scores.sample_product_id')
                                                ->groupBy(['voting_event_id', 'article_name', 'sample_product_id'])
                                                ->orderByDesc('highest_score');
                                    },
                                    'Lowest' => function ($query) {
                                        $query->select('voting_event_id', DB::raw('sample_products.article_name AS article_name'), DB::raw('AVG(score) AS highest_score'))
                                                ->leftJoin('sample_products', 'sample_products.id', '=', 'voting_scores.sample_product_id')
                                                ->groupBy(['voting_event_id', 'article_name', 'sample_product_id'])
                                                ->orderBy('highest_score', 'ASC');
                                    }
                                ])->first();

            return response()->json([
                'status' => 'success',
                'data' => $event,
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

    public function activateEvent($id)
    {
        try {
            $this->turnOffEvent();

            $userId = Auth::user()->id;
            $user = DB::table('users')->where('id', '=', $userId)->first();

            VotingEvent::where('id', '=', $id)
                    ->update([
                        'is_activate' => true,
                        'updated_by' => $user->name
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

    public function getResultVoting($eventId, $vid)
    {
        try {
            $data = VotingSample::select([
                    DB::raw('voting_samples.id as vid'),
                    DB::raw('voting_events.title AS title'),
                    DB::raw('sample_products.article_name AS article_name'),
                    DB::raw('sample_products.entity_name AS entity_name'),
                    DB::raw('AVG(voting_scores.score) AS average'),
                    DB::raw('MAX(voting_scores.score) AS max'),
                    DB::raw('MIN(voting_scores.score) AS min'),
                ])->leftJoin('voting_events', 'voting_events.id', '=', 'voting_samples.voting_event_id')
                ->leftJoin('sample_products', 'sample_products.id', '=', 'voting_samples.sample_product_id')
                ->leftJoin('voting_scores', 'voting_scores.sample_id', '=', 'voting_samples.id')
                ->where([
                    ['voting_samples.id', '=', $vid],
                    ['voting_samples.voting_event_id', '=', $eventId]
                ])->with([
                    'VotingScore' => fn ($query) =>
                        $query->select([
                                'sample_id',
                                DB::raw('users.name'),
                                DB::raw('users.photo'),
                                DB::raw('users.nip'),
                                'score'
                            ])->leftJoin('users', 'users.attendance_id', '=', 'voting_scores.attendance_id')
                ])->groupBy([
                    'vid',
                    'title',
                    'article_name',
                    'entity_name'
                ])
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => $data,
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

    public function getSumaryResult()
    {
        try {
            $eventVoting = VotingEvent::select('id', 'start_date', 'title', 'description', 'is_activate')
                                    ->with([
                                        'ScoreVoting' => function ($query) {
                                            $query->select([
                                                        'sample_id',
                                                        'voting_event_id',
                                                        DB::raw('voting_event_id AS event_id'),
                                                        DB::raw('sample_product_id AS vid'),
                                                        DB::raw('sample_products.article_name AS article_name'),
                                                        DB::Raw('AVG(score) AS total_score')
                                                    ])->with('Thumbnail')
                                                    ->leftJoin('sample_products', 'sample_products.id', '=', 'voting_scores.sample_product_id')
                                                    ->groupBy('sample_id', 'voting_event_id', 'article_name', 'event_id', 'vid')
                                                    ->orderByDesc('total_score');

                                        }
                                    ])->where('is_activate', '=', true)
                                    ->first();

            return response()->json([
                'status' => 'success',
                'data' => $eventVoting,
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

    private function turnOffEvent()
    {
        $userId = Auth::user()->id;
        $user = DB::table('users')->where('id', '=', $userId)->first();
        $dataEvent = VotingEvent::where('is_activate', '=', true)->first();

        if ($dataEvent) $dataEvent->update(['updated_by' => $user->name, 'is_activate' => false]);
    }

    private function turnOff($id)
    {
        $data = VotingSample::where([['voting_event_id', '=', function ($query) use ($id) {
            $query->select('voting_event_id')
                ->from('voting_samples')
                ->where('id', '=', $id)
                ->first();
        }], [
            'show', '=', true
        ]])->first();

        if ($data) $data->update(['show' => false]);
    }

    private function inputSample($sampleId, $eventId)
    {
        $explodeSampleId = explode(',', $sampleId);

        collect($explodeSampleId)->map(function ($item, $index) use ($eventId) {
            VotingSample::create([
                'voting_event_id' => $eventId,
                'sample_product_id' => $item,
                'queue' => $index + 1
            ]);
        });
    }

    private function inputMember($memberAttendanceId, $eventId)
    {
        $explodeMemberAttendanceId = explode(',', $memberAttendanceId);

        collect($explodeMemberAttendanceId)->map(function ($item) use ($eventId) {
            $attendanceId = $this->checkUser($item);

            VotingMember::create([
                'voting_event_id' => $eventId,
                'attendance_id' => $attendanceId,
            ]);
        });
    }

    private function checkUser($attendanceId)
    {
        $dataUser = User::where('attendance_id', '=', $attendanceId)->first();

        $userSIP = UserSIP::select('username', 'password', 'attendance_id', 'sub_section_id', 'seksi', 'data_karyawans.nip', 'data_karyawans.img_karyawan')
                                ->leftJoin('detail_users', 'detail_users.id', '=', 'users.detail_user_id')
                                ->leftJoin('data_karyawans', 'data_karyawans.id', '=', 'detail_users.data_karyawan_id')
                                ->where('users.attendance_id', '=', $attendanceId)
                                ->first();

        if($dataUser == null) {
            User::create([
                'name' => $userSIP->username,
                'email' => "$userSIP->username@mutif.atpo",
                'password' => $userSIP->password,
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

    private function updateRequestEvent($request, $id)
    {
        $userId = Auth::user()->id;
        $user = DB::table('users')->select('name')->where('id', '=', $userId)->first();

        $votingEvent = VotingEvent::find($id);

        $requests = [
            'start_date' => ($request->start_date) ? $request->start_date : $votingEvent->start_date,
            'title' => ($request->title) ? $request->title : $votingEvent->title,
            'description' => ($request->description) ? $request->description : $votingEvent->description,
            'updated_by' => $user->name,
        ];

        $data = compact('votingEvent', 'requests');

        return $data;
    }

    private function deleteInvitation($id)
    {
        $dataInvitation = VotingMember::select('id')->where('voting_event_id', '=', $id)->get();

        if ($dataInvitation) {
            collect($dataInvitation)->map(function ($index, $item) {
                $invitation = VotingMember::find($index->id);

                $invitation->delete();
            });
        }
    }

    private function deleteSample($id)
    {
        $dataSample = VotingSample::select('id')->where('voting_event_id', '=', $id)->get();

        if ($dataSample) {
            collect($dataSample)->map(function ($index, $item) {
                $sample = VotingSample::find($index->id);

                $sample->delete();
            });
        }
    }
}
