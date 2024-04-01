<?php

use App\Events\NotificationCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'Api\UserController@login');
Route::post('/voting-login', 'Api\UserController@loginVoting');

Route::middleware('jwt.verify')->group(function () {
    Route::get('user', 'Api\UserController@getAuthenticatedUser');
    Route::get('userName', 'Api\UserController@getUserName');

    // main route
    Route::apiResources([
        'area' => 'Api\Admin\AreaController',
        'image' => 'Api\Admin\PhotoController',
        'agent' => 'Api\Admin\AgentController',
        'clothes' => 'Api\Admin\ClothesController',
        'partner-group' => 'Api\PartnerGroupController',
        'mutif-store-admin' => 'Api\Admin\MutifStoreMasterController',
    ]);

    Route::prefix('partnumber')->group(function () {
        Route::post('/', 'Api\Admin\PartnumberController@store');
        Route::delete('/{partnumber}/delete', 'Api\Admin\PartnumberController@destroy');
    });

    Route::prefix('users')->group(function () {
        Route::post('/create', 'Api\UserController@createUser');
        Route::get('/user-sip', 'Api\UserController@getUserSIP');
        Route::get('/{attendanceId}/check', 'Api\UserController@checkUser');
        Route::get('/verification', 'Api\UserController@checkLogin');
    });

    // route atpo v2
    Route::prefix('v2')->group(function () {
        Route::prefix('agent')->group(function () {
            Route::get('/get', 'Api\Admin\Event\AgentController@index'); //diubah
            Route::post('/create', 'Api\Admin\Event\AgentController@store'); //diubah
            Route::get('/{id}/detail', 'Api\Admin\Event\AgentController@show');
            Route::put('/{id}/update', 'Api\Admin\Event\AgentController@update');
            Route::get('/list-client', 'Api\Admin\Event\AgentController@getClient');
        });

        Route::prefix('distributor')->group(function () {
            Route::get('/get', 'Api\Admin\Event\DistributorController@index'); //diubah
            Route::post('/create', 'Api\Admin\Event\DistributorController@store'); //diubah
            Route::get('/{id}/detail', 'Api\Admin\Event\DistributorController@show');
            Route::put('/{id}/update', 'Api\Admin\Event\DistributorController@update');
            Route::get('/list', 'Api\Admin\Event\DistributorController@getListDistributor');
        });

        Route::prefix('product')->group(function () {
            Route::get('/get', 'Api\Admin\Event\ProductController@getAllProduct'); //diubah
            Route::post('/input-price', 'Api\Admin\Event\ProductController@inputPriceList');
            Route::post('/create', 'Api\Admin\Event\ProductController@storeProduct');
            Route::post('/input-photo', 'Api\Admin\Event\ProductController@inputImage');
            Route::put('/{id}/update', 'Api\Admin\Event\ProductController@updateProduct');
            Route::get('/{id}/detail', 'Api\Admin\Event\ProductController@getDetailProduct');
        });

        Route::prefix('buffer-product')->group(function () {
            Route::post('/create', 'Api\Admin\Event\BufferProductController@createBufferProduct');
            Route::patch('/{buffer_product}/increase-amount', 'Api\Admin\Event\BufferProductController@increaseAmount');
        });

        Route::prefix('event')->group(function () {
            Route::get('/get', 'Api\Admin\Event\EventController@getEvent'); //diubah
            Route::get('/list-event', 'Api\Admin\Event\EventController@getListEvent');
            Route::post('/create', 'Api\Admin\Event\EventController@createEvent'); //diubah
            Route::delete('/{id}/delete-event', 'Api\Admin\Event\EventController@deleteEvent');
            Route::delete('/{id}/delete-session', 'Api\Admin\Event\EventController@deleteSession');
            Route::get('/{id}/detail', 'Api\Admin\Event\EventController@getDetailEvent');
            Route::post('/input-session', 'Api\Admin\Event\EventController@createSession');
            Route::put('/{id}/update-event', 'Api\Admin\Event\EventController@updateEvent');
            Route::patch('/{id}/activate-event', 'Api\Admin\Event\EventController@activateEvent');
            Route::delete('/{id}/delete-detail-session', 'Api\Admin\Event\EventController@deleteDetailSession');
            Route::post('/input-detail-session', 'Api\Admin\Event\EventController@inputDetailSession');
            Route::get('/current-event', 'Api\Admin\Event\EventController@currentEvent');
        });

        Route::prefix('report')->group(function () {
            Route::get('/{eventId}/highest-order', 'Api\Admin\Event\ReportController@highestOrder');
            Route::get('/{evetnId}/client/{clientId}/detail', 'Api\Admin\Event\ReportController@detailOrder');
            Route::get('/{eventId}/highest-order-distributor', 'Api\Admin\Event\ReportController@highestOrderDistributor');
            Route::get('/{eventId}/highest-order-product', 'Api\Admin\Event\ReportController@highestOrderProduct');
            Route::get('/{id}/detail-order', 'Api\Admin\Event\ReportController@ReportPerProduct');
            Route::get('/{eventId}/ordered-product', 'Api\Admin\Event\ReportController@getOrderedProduct');
            Route::get('/all-report', 'Api\Admin\Event\ReportController@getAllReport');
            Route::get('/report-per-distributor', 'Api\Admin\Event\ReportController@getReportDistributor');
            Route::get('/sum-ordered-article', 'Api\Admin\Event\ReportController@sumOrderedProduct');
            Route::get('/{id}/detail-order-distributor', 'Api\Admin\Event\ReportController@getDetailOrderDistributor');
        });
    });

    Route::prefix('resource-and-development')->group(function () {
        Route::prefix('sample')->group(function () {
            // Sample's Route
            Route::get('/', 'Api\Admin\ResourceAndDevelopment\SampleProductController@index');
            Route::post('/create', 'Api\Admin\ResourceAndDevelopment\SampleProductController@store');
            Route::get('/{id}/detail', 'Api\Admin\ResourceAndDevelopment\SampleProductController@show');
            Route::put('/{id}/update', 'Api\Admin\ResourceAndDevelopment\SampleProductController@update');
            Route::delete('/{id}/delete', 'Api\Admin\ResourceAndDevelopment\SampleProductController@destroy');
            Route::get('/{id}/history', 'Api\Admin\ResourceAndDevelopment\SampleProductController@getHistorySample');
            Route::get('/all-history', 'Api\Admin\ResourceAndDevelopment\SampleProductController@getAllHistory');

            // Photo's Route
            Route::post('/input-photo', 'Api\Admin\ResourceAndDevelopment\SampleProductController@insertSamplePhoto');
            Route::delete('/{id}/{sampleProductId}/delete-photo', 'Api\Admin\ResourceAndDevelopment\SampleProductController@deletePhoto');

            // Style's Route
            Route::get('/style', 'Api\Admin\ResourceAndDevelopment\StyleController@index');
            Route::post('/style/create', 'Api\Admin\ResourceAndDevelopment\StyleController@store');
            Route::get('/style/sub-style', 'Api\Admin\ResourceAndDevelopment\StyleController@getSubStyle');
            Route::get('/style/sub-style/create', 'Api\Admin\ResourceAndDevelopment\StyleController@createSubStyle');
            Route::get('/style/sub-style/{id}/delete', 'Api\Admin\ResourceAndDevelopment\StyleController@deleteSubStyle');

            // Fabric Texture's Route
            Route::post('/input-fabric-texture', 'Api\Admin\ResourceAndDevelopment\SampleProductController@inputFabricTexture');
            Route::delete('/{id}/{sampleProductId}/delete-fabric', 'Api\Admin\ResourceAndDevelopment\SampleProductController@deleteFabricTexture');

            // input data sample design
            Route::post('/input-sample-design', 'Api\Admin\ResourceAndDevelopment\SampleProductController@inputSampleDesign');
            Route::delete('/{id}/delete-sample-design', 'Api\Admin\ResourceAndDevelopment\SampleProductController@deleteSampleDesign');
        });

        Route::prefix('master-material')->group(function () {
            Route::get('/', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@index');
            Route::post('/create', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@store');
            Route::delete('/{id}/delete', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@destroy');
            Route::get('/material-function', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@getMaterialFunction');
            Route::get('/additional-material', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@getAdditionalMaterial');
            Route::get('/product-accessories', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@getProductAccessories');
            Route::get('/additional-accessories', 'Api\Admin\ResourceAndDevelopment\MasterMaterialController@getAdditionalAccessories');
        });

        Route::prefix('staff-rnd')->group(function () {
            Route::get('/designer', 'Api\Admin\ResourceAndDevelopment\DesignerController@getDesigner');
            Route::get('/merchandiser', 'Api\Admin\ResourceAndDevelopment\DesignerController@getMerchandiser');
            Route::get('/leader-designer', 'Api\Admin\ResourceAndDevelopment\DesignerController@getLeaderDesigner');
        });

        Route::prefix('voting')->group(function () {
            Route::get('/sample', 'Api\Admin\ResourceAndDevelopment\VotingController@getSample');
            Route::get('/data', 'Api\Admin\ResourceAndDevelopment\VotingController@getAllEvent');
            Route::delete('/{id}/delete-event', 'Api\Admin\ResourceAndDevelopment\VotingController@deleteEvent');
            Route::post('/create', 'Api\Admin\ResourceAndDevelopment\VotingController@createEvent');
            Route::post('/add-sample', 'Api\Admin\ResourceAndDevelopment\VotingController@addNewSample');
            Route::get('/{id}/detail', 'Api\Admin\ResourceAndDevelopment\VotingController@getDetailEvent');
            Route::post('/invite-member', 'Api\Admin\ResourceAndDevelopment\VotingController@inviteMember');
            Route::put('/{id}/update-event', 'Api\Admin\ResourceAndDevelopment\VotingController@updateEvent');
            Route::delete('/{id}/{sampleId}/remove-sample', 'Api\Admin\ResourceAndDevelopment\VotingController@removeSample');
            Route::patch('/{id}/activate-event', 'Api\Admin\ResourceAndDevelopment\VotingController@activateEvent');
            Route::patch('/{id}/activate', 'Api\Admin\ResourceAndDevelopment\VotingController@showingSampleForAdmin');
            Route::get('/{eventId}/{vid}/get-result', 'Api\Admin\ResourceAndDevelopment\VotingController@getResultVoting');
            Route::delete('/{id}/{attendanceId}/cancel-invitation', 'Api\Admin\ResourceAndDevelopment\VotingController@removeInvitation');
        });
    });

    Route::middleware('jwt.verify', 'voting-middleware')->prefix('resource-and-development')->group(function () {
        Route::prefix('voting-client')->group(function () {
            Route::post('/vote-sample', 'Api\Client\ResourceAndDevelopment\VotingController@voteSample');
            Route::patch('/{id}/update', 'Api\Client\ResourceAndDevelopment\VotingController@updateVote');
            Route::get('/history-sample', 'Api\Client\ResourceAndDevelopment\VotingController@getHistoryVote');
            Route::get('/get-sample', 'Api\Client\ResourceAndDevelopment\VotingController@showSampleForClient');
        });
    });

    // route with exception
    Route::apiResource('entity', 'Api\Admin\EntityController')
    ->parameters(['entity' => 'entity'])
    ->only('index', 'update');

    Route::apiResource('last-brand', 'Api\LastBrandController')
    ->parameters(['last-brand' => 'entity'])
    ->except('show', 'store');

    Route::apiResource('total-product', 'Api\TotalProductController')
    ->only('index');

    Route::apiResource('total-pre-order', 'Api\TotalPreOrderController')
    ->only('index', 'show');

    Route::apiResource('phone', 'Api\Admin\PhoneController')
    ->except('store');

    // single route
    Route::get('/logs', 'Api\Admin\Logger');
    Route::get('/status', 'Api\Admin\SessionStatus');
    Route::get('get-new-member', 'Api\AccRegistration');
    Route::post('logout', 'Api\UserController@logout');
    Route::get('/highest-order', 'Api\Admin\HighestOrder');
    Route::put('/is-active/{clothes}', 'Api\ActivateClothes');
    Route::post('single-agent', 'Api\Admin\CreateSingleAgent');
    Route::get('unregistered-MS-agent', 'Api\Admin\SingleAgent');
    Route::get('/search-agent/{search}', 'Api\Admin\SearchAgent');
    Route::get('/detail-transaction/{id}', 'Api\DetailTransaction');
    Route::get('/search-products/{search}', 'Api\Admin\SearchProduct');
    Route::get('/search-distributor/{search}', 'Api\Admin\SearchDistributor');
    Route::post('/photo/{clothes_id}', 'Api\Admin\ImageController@uploadPhoto');

    // API for report
    Route::get('/total', 'Api\Admin\TotalController@totalOrder');
    Route::get('/totalAgentWithDB', 'Api\Admin\TotalController@totalAgentWithDB');
    Route::get('/totalAllAgent', 'Api\Admin\TotalController@totalProductOrderClient');
    Route::get('detailTotalAgent/{id}', 'Api\Admin\TotalController@detailTotalAgentWithD');

});

// group registration route
Route::prefix('client')->group( function () {
    Route::prefix('auth')->group( function () {
        Route::post('login', 'Api\Client\Event\ClientController@login');
        Route::post('register', 'Api\Client\Event\ClientController@register');
        Route::get('partner-group', 'Api\Client\Event\ClientController@partnerGroupList');
        Route::post('register-distributor', 'Api\Client\Event\ClientController@registerDistributor');
    });

    Route::prefix('master')->group(function () {
        Route::get('current-event', 'Api\Client\Event\MasterController@activeEvent');
        Route::get('distributor', 'Api\Client\Event\MasterController@distributorList');
    });

    Route::prefix('SB')->middleware('client-check', 'check-event')->group(function () {
        Route::delete('/{id}/delete-chart', 'Api\Client\Event\OrderController@deleteDataChart');
        Route::post('chart-input', 'Api\Client\Event\OrderController@inputIntoChart');
        Route::get('/{eventId}/product', 'Api\Client\Event\OrderController@getProduct');
        Route::post('/{eventId}/order', 'Api\Client\Event\OrderController@createOrder');
        Route::get('/{eventId}/data-chart', 'Api\Client\Event\OrderController@getDataChart');
        Route::put('/{id}/update-chart', 'Api\Client\Event\OrderController@updateDataChart');
        Route::get('/{eventId}/count-data-chart', 'Api\Client\Event\OrderController@countDataChart');
        Route::get('/{eventId}/history', 'Api\Client\Event\OrderController@historyOrder');
    });

    Route::post('verification', 'Api\Client\Event\ClientController@verification');
    Route::get('event-active', 'Api\Client\ResourceAndDevelopment\VotingController@eventActive');
});

Route::prefix('exapro')->group(function () {
    Route::get('/{partnumber}/image', 'Api\Client\ImageController@show');
    Route::get('/{partnumber}/description', 'Api\Client\ProductController@show');
    Route::get('/image-catalog', 'Api\Client\ImageController@getImageCatalog');
});


// testing
