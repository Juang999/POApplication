<?php

namespace App\Http\Controllers\Api\Admin\Event;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Product, PartnumberProduct, BufferProduct, Photo, PriceList};
use App\Http\Requests\Admin\Clothes\{CreateClothesRequest, UpdateClothesRequest, InputPriceListRequest};

class ProductController extends Controller
{
    public function getAllProduct()
    {
        try {
            $searchname = request()->searchname;

            $clothes = Product::select([
                                    'products.id',
                                    'products.entity_name',
                                    'products.article_name',
                                    'products.color',
                                    'products.material',
                                    'products.combo',
                                    'products.special_feature',
                                    'products.keyword',
                                    'products.description',
                                    'products.slug',
                                    'products.group_article',
                                    'products.type_id',
                                    'products.is_active',
                                    DB::raw("(SELECT photo FROM images WHERE clothes_id = products.id LIMIT 1 OFFSET 0) AS photo"),
                                    DB::raw("CASE WHEN partnumber_products.partnumber IS NULL THEN '-' ELSE partnumber_products.partnumber END AS partnumber")
                                ])->when($searchname, fn($query) =>
                                    $query->where('article_name', 'LIKE', "%$searchname%")
                                )
                                ->leftJoin('partnumber_products', 'partnumber_products.product_id', '=', 'products.id')
                                ->paginate(10);

            foreach ($clothes as $clothing) {
                $clothing->combo = explode(', ', $clothing->combo);
            }

            return response()->json([
                'status' => 'success',
                'data' => $clothes,
                'error' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ]);
        }
    }

    public function getDetailProduct($id)
    {
        try {
            $product = Product::select(
                                    'products.id',
                                    'products.entity_name',
                                    'products.article_name',
                                    'products.color',
                                    'products.material',
                                    'products.combo',
                                    'products.special_feature',
                                    'products.keyword',
                                    'products.description',
                                    'products.slug',
                                    'products.group_article',
                                    DB::raw("CASE WHEN types.type IS NULL THEN '-' ELSE types.type END AS type"),
                                    'partnumber_products.partnumber',
                                    'products.is_active'
                                )->leftJoin('types', 'types.id', '=', 'products.type_id')
                                ->leftJoin('partnumber_products', 'partnumber_products.product_id', '=', 'products.id')
                                ->where('products.id', '=', $id)
                                ->with([
                                        'Photo' => fn ($query) => $query->select('id', 'product_id', 'photo'),
                                        'BufferProduct' => fn ($query) => $query->select('buffer_products.clothes_id','sizes.size','buffer_products.size_id','buffer_products.qty_avaliable',)->leftJoin('sizes', 'sizes.id', '=', 'buffer_products.size_id'),
                                        'PriceList' => fn ($query) => $query->select('price_lists.id', 'clothes_id', DB::raw('sizes.size'), 'price')->leftJoin('sizes', 'sizes.id', '=', 'price_lists.size_id')
                                    ])
                                ->first();


            $product->combo = explode(', ', $product->combo);

            return response()->json([
                'status' => 'success',
                'data' => $product,
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

    public function storeProduct(CreateClothesRequest $request)
    {
        try {
            DB::beginTransaction();
                $slug = implode('-', explode(' ', $request->article_name));

                $clothes = Product::create([
                    'entity_name' => $request->entity_name,
                    'article_name' => $request->article_name,
                    'color' => $request->color,
                    'material' => $request->material,
                    'combo' => $request->combo,
                    'special_feature' => $request->special_feature,
                    'keyword' => $request->keyword,
                    'description' => $request->description,
                    'slug' => $slug,
                    'group_article' => $request->group_article,
                    'type_id' => $request->type_id,
                    'is_active' => 1,
                    'price' => $request->price
                ]);

                $this->inputPartnumber($clothes->id, $request->partnumber);
                $this->inputBufferStock($request->stock, $clothes->id);
                $this->helperInputPriceList($request->price, $clothes->id);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $clothes,
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

    public function updateProduct(UpdateClothesRequest $request, $id)
    {
        try {
            $req = $this->checkRequest($request, $id);

            Product::where('id', '=', $id)->update([
                'entity_name' => $req['requestProduct']['entity_name'],
                'article_name' => $req['requestProduct']['article_name'],
                'color' => $req['requestProduct']['color'],
                'material' => $req['requestProduct']['material'],
                'combo' => $req['requestProduct']['combo'],
                'special_feature' => $req['requestProduct']['special_feature'],
                'keyword' => $req['requestProduct']['keyword'],
                'description' => $req['requestProduct']['description'],
                'slug' => $req['requestProduct']['slug'],
                'group_article' => $req['requestProduct']['group_article'],
                'type_id' => $req['requestProduct']['type_id'],
                'price' => $req['requestProduct']['price'],
                'is_active' => $req['requestProduct']['is_active'],
            ]);

            $this->updateBuffer($req['requestBuffer'], $id);

            return response()->json([
                'status' => 'success',
                'data' => true,
                'error' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ]);
        }
    }

    public function inputImage(Request $request)
    {
        try {
            $inputPhoto = collect($request->input_photo)->map(function ($data) {
                $decodeData = json_decode($data, true);

                return [
                    "product_id" => $decodeData['product_id'],
                    "photo" => $decodeData['photo'],
                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                ];
            })->toArray();

            DB::table('photos')->insert($inputPhoto);

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

    public function inputPriceList(InputPriceListRequest $request)
    {
        try {
            $this->helperInputPriceList($request->price, $request->product_id);

            return response()->json([
                'status'  => 'success',
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

    public function deletePrice($id)
    {
        try {
            PriceList::where('id', '=', $id)->delete();

            return response()->json([
                'status' => 'succcess',
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
        $clothes = Product::where('id', '=', $id)->first();
        $buffer_product = BufferProduct::where('clothes_id', '=', $id)->first();
        $slug = implode('-', explode(' ', strtolower(($request->article_name) ? $request->article_name : $clothes->article_name)));

        $requestProduct = [
            "entity_name" => ($request->entity_name) ? $request->entity_name : $clothes->entity_name,
            "article_name" => ($request->article_name) ? $request->article_name : $clothes->article_name,
            "color" => ($request->color) ? $request->color : $clothes->color,
            "material" => ($request->material) ? $request->material : $clothes->material,
            "combo" => ($request->combo) ? $request->combo : $clothes->combo,
            "special_feature" => ($request->special_feature) ? $request->special_feature : $clothes->special_feature,
            "keyword" => ($request->keyword) ? $request->keyword : $clothes->keyword,
            "description" => ($request->description) ? $request->description : $clothes->description,
            "slug" => ($request->article_name) ? $slug : $clothes->slug,
            "price" => ($request->price) ? $request->price : $clothes->price,
            "group_article" => ($request->group_article) ? $request->group_article : $clothes->group_article,
            "type_id" => ($request->type_id) ? $request->type_id : $clothes->type_id,
            "is_active" => ($request->is_active) ? $request->is_active : $clothes->is_active,
        ];

        $requestBuffer = [
            "qty_avaliable" => ($request->qty) ? $request->qty : $buffer_product->qty_avaliable,
            "qty_buffer" => ($request->qty) ? $request->qty : $buffer_product->qty_buffer,
        ];

        return compact('requestProduct', 'requestBuffer');
    }

    private function updateBuffer($request, $id)
    {
        BufferProduct::where('clothes_id', '=', $id)->update([
            'qty_avaliable' => $request['qty_avaliable'],
            'qty_buffer' => $request['qty_buffer'],
        ]);
    }

    private function inputPartnumber($clothesId, $partnumber)
    {
        PartnumberProduct::create([
            'product_id' => $clothesId,
            'partnumber' => $partnumber
        ]);
    }

    private function inputBufferStock($stock, $clothesId)
    {
        BufferProduct::create([
            'clothes_id' => $clothesId,
            'qty_avaliable' => $stock,
            'qty_process' => 0,
            'qty_buffer' => 0
        ]);
    }

    private function helperInputPriceList($requestPrice, $id)
    {
        $rawData = json_decode($requestPrice, true);

        $data = collect($rawData)->map(function ($item, $index) use ($id) {
            return [
                'clothes_id' => $id,
                'size_id' => $item['size_id'],
                'price' => $item['price'],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];
        })->toArray();

        DB::table('price_lists')->insert($data);
    }
}
