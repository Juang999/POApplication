<?php

namespace App\Http\Controllers\Api\Admin\Event;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Event, Session, DetailSession, Product};
use App\Http\Requests\Admin\Event\{CreateEventRequest, UpdateEventRequest, CreateSessionRequest, CreateDetailSessionRequest};

class EventController extends Controller
{
    public function getEvent()
    {
        try {
            $searchEvent = request()->searchevent;

            $event = Event::select('id', 'event_name', 'start_date', 'end_date', 'is_active')
                        ->when($searchEvent, function ($query) use ($searchEvent) {
                            $query->where('event_name', 'like', "&$searchEvent&");
                        })->paginate(10);

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

    public function getListEvent()
    {
        try {
            $event = Event::select('id', 'event_name')->get();

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

    public function createEvent(CreateEventRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $event = Event::create([
                'event_name' => $request->event_name,
                'event_desc' => $request->event_desc,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => false
            ]);

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

    public function updateEvent(UpdateEventRequest $request, $id)
    {
        try {
            $req = $this->checkRequest($request, $id);

            Event::where('id', '=', $id)
                ->update([
                    'event_name' => $req['event_name'],
                    'event_desc' => $req['event_desc'],
                    'start_date' => $req['start_date'],
                    'end_date' => $req['end_date'],
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
            Event::where('id', '=', $id)->delete();

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

    private function checkRequest($request, $id)
    {
        $event = Event::where('id', '=', $id)->first();

        return [
            'event_name' => ($request->event_name) ? $request->event_name : $event->event_name,
            'event_desc' => ($request->event_desc) ? $request->event_desc : $event->event_desc,
            'start_date' => ($request->start_date) ? $request->start_date : $event->start_date,
            'end_date' => ($request->end_date) ? $request->end_date : $event->end_date,
        ];
    }

    public function getDetailEvent($id)
    {
        try {
            $event = Event::select('id', 'event_name', 'event_desc', 'start_date', 'end_date', 'is_active')->where('id', '=', $id)
                            ->with(['Product' => fn ($query) => $query->select('id', 'entity_name', 'article_name', 'is_active', 'group_article')])
                            ->first();

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

    public function activateProduct($id)
    {
        try {
            $dataProduct = $this->dataProduct($id);

            Product::where([
                    ['id', '=', $id],
                    ['group_article', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true)]
                ])->update(['is_active' => ($dataProduct->is_active == true) ? false : true ]);

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

    public function createSession(CreateSessionRequest $request)
    {
        try {
            DB::beginTransaction();
                $session = Session::create([
                    'event_id' => $request->event_id,
                    'session_desc' => $request->session_desc,
                    'is_active' => false
                ]);

                if ($request->product != null) {
                    $this->createDetailSession($request, $session->id);
                }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $session,
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

    public function inputDetailSession(CreateDetailSessionRequest $request)
    {
        try {
            $detailSession = DetailSession::create([
                'session_id' => $request->session_id,
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $detailSession,
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

    public function deleteSession($id)
    {
        try {
            Session::where('id', '=', $id)->delete();

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

    public function deleteDetailSession($id)
    {
        try {
            DetailSession::where('id', '=', $id)->delete();

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

    public function activateEvent($id)
    {
        try {
            $this->inactiveEvent();

            Event::where('id', '=', $id)
                ->update([
                    'is_active' => true
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

    public function currentEvent()
    {
        try {
            $dataEvent = Event::select('id', 'event_name', 'event_desc', 'start_date', 'end_date')->where('is_active', '=', true)
                            ->with([
                                    'Product' => fn ($query) => $query->select('products.id', 'entity_name', 'article_name', 'is_active', 'group_article', DB::raw('partnumber_products.partnumber AS partnumber'))
                                                                    ->leftJoin('partnumber_products', 'partnumber_products.product_id', '=', 'products.id')
                                                                    ->with(['Thumbnail' => fn ($query) => $query->select('id', 'product_id', 'photo')])
                                ])
                            ->first();

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

    private function inactiveEvent()
    {
        Event::where('is_active', '=', true)
            ->update([
                'is_active' => false
            ]);
    }

    private function createDetailSession($request, $sessionId)
    {
        $inputDetailSession = collect($request->product)->map(function ($data) use ($sessionId) {
            $decodedData = json_decode($data, true);

            return [
                'session_id' => $sessionId,
                'product_id' => $decodedData['product_id'],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];
        })->toArray();

        DB::table('detail_sessions')->insert($inputDetailSession);
    }

    private function dataProduct($id)
    {
        $product = Product::where([
            ['id', '=', $id]
        ])->first();

        return $product;
    }
}
