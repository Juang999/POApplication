<?php

namespace App\Http\Controllers\Api\Client\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, Chart, Order, Distributor};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\Client\Order\{InputOrderRequest, UpdateOrderRequest};

class OrderController extends Controller
{
    public function getProduct()
    {
        try {
            $searchName = request()->search_name;
            $phoneNumber = request()->header('phone');
            $dataBuyer = $this->getDataBuyer($phoneNumber);
            $statusOrder = $this->getStatusOrder($phoneNumber);

            $products = Product::select(
                                'products.id',
                                'entity_name',
                                'article_name',
                                'color',
                                // 'combo',
                                'material',
                                'type_id',
                                'types.type as type_name',
                                'keyword',
                                'description',
                                'price',
                                DB::raw("$dataBuyer->discount AS discount")
                            )->leftJoin('types', 'types.id', '=', 'products.type_id')
                            ->where([
                                ['products.is_active', '=', true],
                                ['products.group_article', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true)]
                            ])->whereNotIn('products.id', fn ($query) => $query->select('product_id')->from('charts')->where([
                                            [
                                                'client_id', '=', fn ($query) => $query->select('id')->from('distributors')->where('phone', '=', $phoneNumber)
                                            ],[
                                                'event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true)
                                            ]
                            ]))->when($searchName, function ($query) use ($searchName) {
                                $query->where('products.article_name', 'like', "%$searchName%");
                            })->with([
                                'DataMaterial' => fn ($query) => $query->select('id', 'product_id', 'material_name', 'material_type', 'description', 'photo'),
                                'MaterialAdditional' => fn ($query) => $query->select('id', 'product_id', 'material_name', 'material_type', 'description', 'photo'),
                                'Accessories' => fn ($query) => $query->select('id', 'product_id', 'material_name', 'material_type', 'description', 'photo'),
                                'AccessoriesProduct' => fn ($query) => $query->select('id', 'product_id', 'material_name', 'material_type', 'description', 'photo'),
                                'Photo' => fn ($query) => $query->select('id', 'product_id', 'photo'),
                                'PriceList' => function ($query) use ($dataBuyer) {
                                    $query->select('price_lists.id', 'clothes_id', 'size_id', DB::raw('sizes.size AS size'), DB::raw('price AS normal_price'))
                                        ->leftJoin('sizes', 'sizes.id', '=', 'price_lists.size_id');
                                }
                            ])->paginate(1);

            // foreach ($products as $product) {
            //     $product->combo = explode(', ', $product->combo);
            // }

            return response()->json([
                'status' => 'success',
                'data' => ($statusOrder == false) ? $products : [],
                'status_order' => $statusOrder,
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

    public function inputIntoChart(InputOrderRequest $request)
    {
        try {
            $headerPhone = request()->header('phone');
            $request = $this->checkRequestCreate($request, $headerPhone);

            $inputChart = Chart::create([
                'client_id' => $request['client_id'],
                'event_id' => $request['event_id'],
                'session_id' => $request['session_id'],
                'product_id' => $request['product_id'],
                'size_S' => $request['size_S'],
                'size_M' => $request['size_M'],
                'size_L' => $request['size_L'],
                'size_XL' => $request['size_XL'],
                'size_XXL' => $request['size_XXL'],
                'size_XXXL' => $request['size_XXXL'],
                'size_2' => $request['size_2'],
                'size_4' => $request['size_4'],
                'size_6' => $request['size_6'],
                'size_8' => $request['size_8'],
                'size_10' => $request['size_10'],
                'size_12' => $request['size_12'],
                'size_27' => $request['size_27'],
                'size_28' => $request['size_28'],
                'size_29' => $request['size_29'],
                'size_30' => $request['size_30'],
                'size_31' => $request['size_31'],
                'size_32' => $request['size_32'],
                'size_33' => $request['size_33'],
                'size_34' => $request['size_34'],
                'size_35' => $request['size_35'],
                'size_36' => $request['size_36'],
                'size_37' => $request['size_37'],
                'size_38' => $request['size_38'],
                'size_39' => $request['size_39'],
                'size_40' => $request['size_40'],
                'size_41' => $request['size_41'],
                'size_42' => $request['size_42'],
                'size_other' => $request['size_other']
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $inputChart,
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

    public function getDataChart()
    {
        try {
            $phoneNumber = request()->header('phone');
            $dataBuyer = $this->getDataBuyer($phoneNumber);
            $statusOrder = $this->getStatusOrder($phoneNumber);

            $searchProduct = request()->searchproduct;

            $dataChart = Chart::select(
                'charts.id',
                'charts.product_id',
                'products.entity_name',
                'products.article_name',
                'products.color',
                'products.combo',
                'products.material',
                'products.keyword',
                'products.type_id',
                DB::raw("$dataBuyer->discount AS discount"),
                'types.type',
                'products.description',
                'products.price',
                'charts.size_S',
                'charts.size_M',
                'charts.size_L',
                'charts.size_XL',
                'charts.size_XXL',
                'charts.size_XXXL',
                'charts.size_2',
                'charts.size_4',
                'charts.size_6',
                'charts.size_8',
                'charts.size_10',
                'charts.size_12',
                'charts.size_27',
                'charts.size_28',
                'charts.size_29',
                'charts.size_30',
                'charts.size_31',
                'charts.size_32',
                'charts.size_33',
                'charts.size_34',
                'charts.size_35',
                'charts.size_36',
                'charts.size_37',
                'charts.size_38',
                'charts.size_39',
                'charts.size_40',
                'charts.size_41',
                'charts.size_42',
                'charts.created_at'
            )->join('products', 'products.id', '=', 'charts.product_id')
            ->join('types', 'types.id', '=', 'products.type_id')
            ->where('charts.client_id', '=', function($query) {
                $phoneNumber = request()->header('phone');
                $query->select('id')
                    ->from('distributors')
                    ->where('phone', '=', $phoneNumber)
                    ->first();
            })->where('event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true))
            ->when($searchProduct, function ($query) use ($searchProduct) {
                $query->where('products.article_name', 'LIKE', "%$searchProduct%");
            })->with([
                'Photo' => fn ($query) => $query->select('photo'),
                'PriceList' => fn ($query) => $query->select('clothes_id', 'sizes.size', 'price_lists.price')->leftJoin('sizes', 'sizes.id', '=', 'price_lists.size_id')
            ])->get();

            return response()->json([
                'status' => 'success',
                'order_status' => $statusOrder,
                'data' => $dataChart,
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

    public function countDataChart()
    {
        try {
            $searchProduct = request()->searchproduct;

            $dataChart = Chart::select(
                'charts.id',
                'products.entity_name',
                'products.article_name',
                'products.color',
                'products.combo',
                'products.material',
                'products.keyword',
                'products.type_id',
                'types.type',
                'products.description',
                'products.price',
                'charts.size_S',
                'charts.size_M',
                'charts.size_L',
                'charts.size_XL',
                'charts.size_XXL',
                'charts.size_XXXL',
                'charts.size_2',
                'charts.size_4',
                'charts.size_6',
                'charts.size_8',
                'charts.size_10',
                'charts.size_12',
                'charts.size_27',
                'charts.size_28',
                'charts.size_29',
                'charts.size_30',
                'charts.size_31',
                'charts.size_32',
                'charts.size_33',
                'charts.size_34',
                'charts.size_35',
                'charts.size_36',
                'charts.size_37',
                'charts.size_38',
                'charts.size_39',
                'charts.size_40',
                'charts.size_41',
                'charts.size_42',
                'charts.created_at'

            )->join('products', 'products.id', '=', 'charts.product_id')
            ->join('types', 'types.id', '=', 'products.type_id')
            ->where('charts.client_id', '=', function($query) {
                $phoneNumber = request()->header('phone');
                $query->select('id')
                    ->from('distributors')
                    ->where('phone', '=', $phoneNumber)
                    ->first();
            })->where('event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true))
            ->when($searchProduct, function ($query) use ($searchProduct) {
                $query->where('products.article_name', 'LIKE', "%$searchProduct%");
            })->count();

            return response()->json([
                'status' => 'success',
                'data' => $dataChart,
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

    public function updateDataChart(UpdateOrderRequest $request, $id)
    {
        try {
            $request = $this->checkRequestUpdate($request, $id);

            Chart::where([
                ['id', '=', $id],
                ['client_id', '=', function ($query) {
                    $query->select('id')
                        ->from('distributors')
                        ->where('phone', '=', request()->header('phone'))
                        ->first();
                }]
            ])->update([
                'size_S' => $request['size_S'],
                'size_M' => $request['size_M'],
                'size_L' => $request['size_L'],
                'size_XL' => $request['size_XL'],
                'size_XXL' => $request['size_XXL'],
                'size_XXXL' => $request['size_XXXL'],
                'size_2' => $request['size_2'],
                'size_4' => $request['size_4'],
                'size_6' => $request['size_6'],
                'size_8' => $request['size_8'],
                'size_10' => $request['size_10'],
                'size_12' => $request['size_12'],
                'size_27' => $request['size_27'],
                'size_28' => $request['size_28'],
                'size_29' => $request['size_29'],
                'size_30' => $request['size_30'],
                'size_31' => $request['size_31'],
                'size_32' => $request['size_32'],
                'size_33' => $request['size_33'],
                'size_34' => $request['size_34'],
                'size_35' => $request['size_35'],
                'size_36' => $request['size_36'],
                'size_37' => $request['size_37'],
                'size_38' => $request['size_38'],
                'size_39' => $request['size_39'],
                'size_40' => $request['size_40'],
                'size_41' => $request['size_41'],
                'size_42' => $request['size_42'],
                'size_other' => $request['size_other']
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

    public function deleteDataChart($id)
    {
        try {
            Chart::where([
                ['id', '=', $id],
                ['client_id', '=', function ($query) {
                    $query->select('id')
                        ->from('distributors')
                        ->where('phone', '=', request()->header('phone'))
                        ->first();
                }]
            ])->delete();

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

    public function createOrder()
    {
        try {
            $dataChart = $this->dataChart();

            DB::beginTransaction();
                DB::table('orders')->insert($dataChart);
                $this->deleteChart();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'success',
                'data' => false,
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function historyOrder()
    {
        try {
            $searchProduct = request()->searchproduct;

            $dataOrder = Order::select(
                'orders.id',
                'orders.product_id',
                'products.entity_name',
                'products.article_name',
                'products.color',
                'products.combo',
                'products.material',
                'products.keyword',
                'products.type_id',
                'types.type',
                'products.description',
                'products.price',
                'orders.size_S',
                'orders.size_M',
                'orders.size_L',
                'orders.size_XL',
                'orders.size_XXL',
                'orders.size_XXXL',
                'orders.size_2',
                'orders.size_4',
                'orders.size_6',
                'orders.size_8',
                'orders.size_10',
                'orders.size_12',
                'orders.size_27',
                'orders.size_28',
                'orders.size_29',
                'orders.size_30',
                'orders.size_31',
                'orders.size_32',
                'orders.size_33',
                'orders.size_34',
                'orders.size_35',
                'orders.size_36',
                'orders.size_37',
                'orders.size_38',
                'orders.size_39',
                'orders.size_40',
                'orders.size_41',
                'orders.size_42',
                'orders.created_at'

            )->join('products', 'products.id', '=', 'orders.product_id')
            ->join('types', 'types.id', '=', 'products.type_id')
            ->where('orders.client_id', '=', function($query) {
                $phoneNumber = request()->header('phone');
                $query->select('id')
                    ->from('distributors')
                    ->where('phone', '=', $phoneNumber)
                    ->first();
            })->where('event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true))
            ->when($searchProduct, function ($query) use ($searchProduct) {
                $query->where('products.article_name', 'LIKE', "%$searchProduct%");
            })->with(['Photo' => function ($query) {
                $query->select('photo');
            }])
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $dataOrder,
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

    private function checkRequestCreate($request, $headerPhone)
    {
        $dataClient = $this->getDataClient($headerPhone);
        $request = collect($request)->toArray();

        return [
            'client_id' => $dataClient->id,
            'event_id' => $request['event_id'],
            'session_id' => null,
            'product_id' => $request['product_id'],
            'size_S' => (array_key_exists('size_S', $request)) ? $request['size_S'] : 0,
            'size_M' => (array_key_exists('size_M', $request)) ? $request['size_M'] : 0,
            'size_L' => (array_key_exists('size_L', $request)) ? $request['size_L'] : 0,
            'size_XL' => (array_key_exists('size_XL', $request)) ? $request['size_XL'] : 0,
            'size_XXL' => (array_key_exists('size_XXL', $request)) ? $request['size_XXL'] : 0,
            'size_XXXL' => (array_key_exists('size_XXXL', $request)) ? $request['size_XXXL'] : 0,
            'size_2' => (array_key_exists('size_2', $request)) ? $request['size_2'] : 0,
            'size_4' => (array_key_exists('size_4', $request)) ? $request['size_4'] : 0,
            'size_6' => (array_key_exists('size_6', $request)) ? $request['size_6'] : 0,
            'size_8' => (array_key_exists('size_8', $request)) ? $request['size_8'] : 0,
            'size_10' => (array_key_exists('size_10', $request)) ? $request['size_10'] : 0,
            'size_12' => (array_key_exists('size_12', $request)) ? $request['size_12'] : 0,
            'size_27' => (array_key_exists('size_27', $request)) ? $request['size_27'] : 0,
            'size_28' => (array_key_exists('size_28', $request)) ? $request['size_28'] : 0,
            'size_29' => (array_key_exists('size_29', $request)) ? $request['size_29'] : 0,
            'size_30' => (array_key_exists('size_30', $request)) ? $request['size_30'] : 0,
            'size_31' => (array_key_exists('size_31', $request)) ? $request['size_31'] : 0,
            'size_32' => (array_key_exists('size_32', $request)) ? $request['size_32'] : 0,
            'size_33' => (array_key_exists('size_33', $request)) ? $request['size_33'] : 0,
            'size_34' => (array_key_exists('size_34', $request)) ? $request['size_34'] : 0,
            'size_35' => (array_key_exists('size_35', $request)) ? $request['size_35'] : 0,
            'size_36' => (array_key_exists('size_36', $request)) ? $request['size_36'] : 0,
            'size_37' => (array_key_exists('size_37', $request)) ? $request['size_37'] : 0,
            'size_38' => (array_key_exists('size_38', $request)) ? $request['size_38'] : 0,
            'size_39' => (array_key_exists('size_39', $request)) ? $request['size_39'] : 0,
            'size_40' => (array_key_exists('size_40', $request)) ? $request['size_40'] : 0,
            'size_41' => (array_key_exists('size_41', $request)) ? $request['size_41'] : 0,
            'size_42' => (array_key_exists('size_42', $request)) ? $request['size_42'] : 0,
            'size_other' => (array_key_exists('size_other', $request)) ? $request['size_other'] : 0,
            'discount' => $dataClient->discount
        ];
    }

    private function checkRequestUpdate($request, $chartId)
    {
        $dataChart = Chart::where('id', '=', $chartId)->first();
        $request = collect($request)->toArray();

        return [
            'size_S' => (array_key_exists('size_S', $request)) ? ($request['size_S'] !== NULL) ? $request['size_S'] : $dataChart->size_S : $dataChart->size_S,
            'size_M' => (array_key_exists('size_M', $request)) ? ($request['size_M'] !== NULL) ? $request['size_M'] : $dataChart->size_M : $dataChart->size_M,
            'size_L' => (array_key_exists('size_L', $request)) ? ($request['size_L'] !== NULL) ? $request['size_L'] : $dataChart->size_L : $dataChart->size_L,
            'size_XL' => (array_key_exists('size_XL', $request)) ? ($request['size_XL'] !== NULL) ? $request['size_XL'] : $dataChart->size_XL : $dataChart->size_XL,
            'size_XXL' => (array_key_exists('size_XXL', $request)) ? ($request['size_XXL'] !== NULL) ? $request['size_XXL'] : $dataChart->size_XXL : $dataChart->size_XXL,
            'size_XXXL' => (array_key_exists('size_XXXL', $request)) ? ($request['size_XXXL'] !== NULL) ? $request['size_XXXL'] : $dataChart->size_XXXL : $dataChart->size_XXXL,
            'size_2' => (array_key_exists('size_2', $request)) ? ($request['size_2'] !== NULL) ? $request['size_2'] : $dataChart->size_2 : $dataChart->size_2,
            'size_4' => (array_key_exists('size_4', $request)) ? ($request['size_4'] !== NULL) ? $request['size_4'] : $dataChart->size_4 : $dataChart->size_4,
            'size_6' => (array_key_exists('size_6', $request)) ? ($request['size_6'] !== NULL) ? $request['size_6'] : $dataChart->size_6 : $dataChart->size_6,
            'size_8' => (array_key_exists('size_8', $request)) ? ($request['size_8'] !== NULL) ? $request['size_8'] : $dataChart->size_8 : $dataChart->size_8,
            'size_10' => (array_key_exists('size_10', $request)) ? ($request['size_10'] !== NULL) ? $request['size_10'] : $dataChart->size_10 : $dataChart->size_10,
            'size_12' => (array_key_exists('size_12', $request)) ? ($request['size_12'] !== NULL) ? $request['size_12'] : $dataChart->size_12 : $dataChart->size_12,
            'size_27' => (array_key_exists('size_27', $request)) ? ($request['size_27'] !== NULL) ? $request['size_27'] : $dataChart->size_27 : $dataChart->size_27,
            'size_28' => (array_key_exists('size_28', $request)) ? ($request['size_28'] !== NULL) ? $request['size_28'] : $dataChart->size_28 : $dataChart->size_28,
            'size_29' => (array_key_exists('size_29', $request)) ? ($request['size_29'] !== NULL) ? $request['size_29'] : $dataChart->size_29 : $dataChart->size_29,
            'size_30' => (array_key_exists('size_30', $request)) ? ($request['size_30'] !== NULL) ? $request['size_30'] : $dataChart->size_30 : $dataChart->size_30,
            'size_31' => (array_key_exists('size_31', $request)) ? ($request['size_31'] !== NULL) ? $request['size_31'] : $dataChart->size_31 : $dataChart->size_31,
            'size_32' => (array_key_exists('size_32', $request)) ? ($request['size_32'] !== NULL) ? $request['size_32'] : $dataChart->size_32 : $dataChart->size_32,
            'size_33' => (array_key_exists('size_33', $request)) ? ($request['size_33'] !== NULL) ? $request['size_33'] : $dataChart->size_33 : $dataChart->size_33,
            'size_34' => (array_key_exists('size_34', $request)) ? ($request['size_34'] !== NULL) ? $request['size_34'] : $dataChart->size_34 : $dataChart->size_34,
            'size_35' => (array_key_exists('size_35', $request)) ? ($request['size_35'] !== NULL) ? $request['size_35'] : $dataChart->size_35 : $dataChart->size_35,
            'size_36' => (array_key_exists('size_36', $request)) ? ($request['size_36'] !== NULL) ? $request['size_36'] : $dataChart->size_36 : $dataChart->size_36,
            'size_37' => (array_key_exists('size_37', $request)) ? ($request['size_37'] !== NULL) ? $request['size_37'] : $dataChart->size_37 : $dataChart->size_37,
            'size_38' => (array_key_exists('size_38', $request)) ? ($request['size_38'] !== NULL) ? $request['size_38'] : $dataChart->size_38 : $dataChart->size_38,
            'size_39' => (array_key_exists('size_39', $request)) ? ($request['size_39'] !== NULL) ? $request['size_39'] : $dataChart->size_39 : $dataChart->size_39,
            'size_40' => (array_key_exists('size_40', $request)) ? ($request['size_40'] !== NULL) ? $request['size_40'] : $dataChart->size_40 : $dataChart->size_40,
            'size_41' => (array_key_exists('size_41', $request)) ? ($request['size_41'] !== NULL) ? $request['size_41'] : $dataChart->size_41 : $dataChart->size_41,
            'size_42' => (array_key_exists('size_42', $request)) ? ($request['size_42'] !== NULL) ? $request['size_42'] : $dataChart->size_42 : $dataChart->size_42,
            'size_other' => (array_key_exists('size_other', $request)) ? ($request['size_other'] !== NULL) ? $request['size_other'] : $dataChart->size_other : $dataChart->size_other
        ];
    }

    private function dataChart()
    {
        $rawDataChart = Chart::select(
            'client_id',
            'event_id',
            'session_id',
            'product_id',
            'size_S',
            'size_M',
            'size_L',
            'size_XL',
            'size_XXL',
            'size_XXXL',
            'size_2',
            'size_4',
            'size_6',
            'size_8',
            'size_10',
            'size_12',
            'size_27',
            'size_28',
            'size_29',
            'size_30',
            'size_31',
            'size_32',
            'size_33',
            'size_34',
            'size_35',
            'size_36',
            'size_37',
            'size_38',
            'size_39',
            'size_40',
            'size_41',
            'size_42',
            'size_other',
            'discount'
        )->where('charts.client_id', '=', function($query) {
            $phoneNumber = request()->header('phone');
            $query->select('id')
                ->from('distributors')
                ->where('phone', '=', $phoneNumber)
                ->first();
        })->where('event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true))
        ->get();

        $dataChart = collect($rawDataChart)->map(function ($data) {
            return [
                'client_id' => $data->client_id,
                'event_id' => $data->event_id,
                'session_id' => $data->session_id,
                'product_id' => $data->product_id,
                'size_S' => $data->size_S,
                'size_M' => $data->size_M,
                'size_L' => $data->size_L,
                'size_XL' => $data->size_XL,
                'size_XXL' => $data->size_XXL,
                'size_XXXL' => $data->size_XXXL,
                'size_2' => $data->size_2,
                'size_4' => $data->size_4,
                'size_6' => $data->size_6,
                'size_8' => $data->size_8,
                'size_10' => $data->size_10,
                'size_12' => $data->size_12,
                'size_27' => $data->size_27,
                'size_28' => $data->size_28,
                'size_29' => $data->size_29,
                'size_30' => $data->size_30,
                'size_31' => $data->size_31,
                'size_32' => $data->size_32,
                'size_33' => $data->size_33,
                'size_34' => $data->size_34,
                'size_35' => $data->size_35,
                'size_36' => $data->size_36,
                'size_37' => $data->size_37,
                'size_38' => $data->size_38,
                'size_39' => $data->size_39,
                'size_40' => $data->size_40,
                'size_41' => $data->size_41,
                'size_42' => $data->size_42,
                'size_other' => $data->size_other,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'discount' => $data->discount
            ];
        })->toArray();

        return $dataChart;
    }

    private function deleteChart()
    {
        $dataChart = Chart::select(
            'client_id',
            'event_id',
            'session_id',
            'product_id',
            'qty',
        )->join('products', 'products.id', '=', 'charts.product_id')
        ->where('charts.client_id', '=', function($query) {
            $phoneNumber = request()->header('phone');
            $query->select('id')
                ->from('distributors')
                ->where('phone', '=', $phoneNumber)
                ->first();
        })->where('event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true))
        ->delete();
    }

    private function getDataClient($clientPhoneNumber)
    {
        $distributor = Distributor::select('distributors.id', 'partner_groups.discount')->where('phone', '=', $clientPhoneNumber)
                                ->leftJoin('partner_groups', 'partner_groups.id', '=', 'distributors.partner_group_id')
                                ->first();

        return $distributor;
    }

    private function getStatusOrder($phoneNumber)
    {
        $dataOrder = Order::where([
            ['event_id', '=', fn ($query) => $query->select('id')->from('events')->where('is_active', '=', true)],
            ['client_id', '=', fn ($query) => $query->select('id')->from('distributors')->where('phone', '=', $phoneNumber)->first()]
        ])->count();

        return ($dataOrder > 0) ? true : false;
    }

    private function getDataBuyer($phoneNumber)
    {
        $dataBuyer = Distributor::select('distributors.id', 'name', 'phone', DB::raw('partner_groups.discount'))
                                ->leftJoin('partner_groups', 'partner_groups.id', '=', 'distributors.partner_group_id')
                                ->where('phone', '=', $phoneNumber)
                                ->first();

        return $dataBuyer;
    }
}
