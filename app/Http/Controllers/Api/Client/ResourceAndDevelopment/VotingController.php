<?php

namespace App\Http\Controllers\Api\Client\ResourceAndDevelopment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{DB, Auth};
use App\Models\{VotingEvent, VotingScore, SampleProduct};
use App\Http\Requests\Client\ResourceAndDevelopment\{VoteSampleRequest, UpdateVotingRequest};

class VotingController extends Controller
{
    public function showSampleForClient()
    {
        try {
            $user = Auth::user();

            $dataSample = SampleProduct::select(DB::raw('voting_events.id AS voting_event_id'), DB::raw('voting_samples.id AS sample_id'), DB::raw('sample_products.id AS id'), 'date','article_name','style_id', DB::raw('styles.style_name'),'entity_name','material','size','accessories','note_and_description')
                                ->leftJoin('voting_samples', 'voting_samples.sample_product_id', '=', 'sample_products.id')
                                ->leftJoin('voting_events', 'voting_events.id', '=', 'voting_samples.voting_event_id')
                                ->leftJoin('styles', 'styles.id', '=', 'sample_products.style_id')
                                ->with([
                                    'PhotoSampleProduct' => fn ($query) => $query->select('id', 'sample_product_id', 'photo'),
                                    'MaterialSample' => fn ($query) => $query->select('id', 'sample_product_id', 'description', 'photo')->orderBy('sequence', 'ASC'),
                                    'MaterialAdditional' => fn ($query) => $query->select('id', 'sample_product_id', 'description', 'photo')->orderBy('sequence', 'ASC'),
                                    'Accessories' => fn ($query) => $query->select('id', 'sample_product_id', 'description', 'photo')->orderBy('sequence', 'ASC'),
                                    'AccessoriesProduct' => fn ($query) => $query->select('id', 'sample_product_id', 'description', 'photo')->orderBy('sequence', 'ASC'),
                                    'SampleDesign' => fn ($query) => $query->select('id', 'sample_product_id', 'design_photo')
                                ])->where('sample_products.id', '=', function ($query) {
                                    $query->select('sample_product_id')
                                        ->from('voting_samples')
                                        ->where([
                                            ['show', '=', true],
                                            ['voting_event_id', '=', function ($query) {
                                                $query->select('id')
                                                    ->from('voting_events')
                                                    ->where('is_activate', '=', true)
                                                    ->first();
                                            }]
                                        ])->first();
                                })->first();

            $dataEvent = $this->eventIsActive();

            $score = VotingScore::where([['attendance_id', '=', $user->attendance_id], ['voting_event_id', '=', $dataEvent->id]])->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'dataSample' => ($score) ? null : $dataSample,
                    'dataEvent' => $dataEvent,
                    'voted' => ($score) ? true : false
                ],
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

    public function voteSample(VoteSampleRequest $request)
    {
        try {
            $user = Auth::user();

            $dataVoting = VotingScore::create([
                'voting_event_id' => $request->voting_event_id,
                'sample_id' => $request->sample_id,
                'sample_product_id' => $request->product_id,
                'attendance_id' => $user->attendance_id,
                'score' => $request->score,
                'note' => $request->note,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $dataVoting,
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

    public function getHistoryVote()
    {
        try {
            $user = Auth::user();
            $eventId = $this->eventIsActive();

            $history = VotingScore::select(
                                        'voting_scores.id',
                                        'voting_scores.sample_product_id',
                                        DB::raw('voting_events.title'),
                                        DB::raw('voting_events.start_date'),
                                        DB::raw('sample_products.article_name'),
                                        DB::raw('sample_products.entity_name'),
                                        'voting_scores.score',
                                        'voting_scores.note',
                                        'voting_scores.created_at'
                                    )->leftJoin('voting_events', 'voting_events.id', '=', 'voting_scores.voting_event_id')
                                ->leftJoin('sample_products', 'sample_products.id', '=', 'voting_scores.sample_product_id')
                                ->with(['Thumbnail' => fn ($query) => $query->select('sample_product_photos.id', 'sample_product_photos.photo')])
                                ->where([
                                        ['voting_scores.attendance_id', '=', $user->attendance_id],
                                        ['voting_scores.voting_event_id', '=', $eventId->id]
                                    ])
                                ->orderByDesc('voting_scores.created_at')
                                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $history,
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

    public function updateVote(UpdateVotingRequest $request, $id)
    {
        try {
            $requestUpdateVote = $this->requestUpdateVote($request, $id);

            $dataScore = $requestUpdateVote['dataScore'];
            $requests = $requestUpdateVote['requests'];

            $dataScore->update([
                'score' => $requests['score'],
                'note' => $requests['note']
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

    public function eventActive()
    {
        try {
            $data = $this->eventIsActive();

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

    private function requestUpdateVote($request, $id)
    {
        $dataScore = VotingScore::where('id', '=', $id)->first();

        $requests = [
            'score' => ($request->score) ? $request->score : $dataScore->score,
            'note' => ($request->note) ? $request->note : $dataScore->note
        ];

        $compact = compact('dataScore', 'requests');

        return $compact;
    }

    private function eventIsActive()
    {
        $dataEvent = VotingEvent::select('id', 'start_date', 'title', 'description')->where('is_activate', '=', true)->first();

        return $dataEvent;
    }
}
