<?php

namespace App\Http\Controllers\Api\Stock;

use App\Models\WareOrder;
use App\Models\Ware;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;

class WareOrderController extends Controller
{
    /**
     * @param Request $request
     * @api {GET} /api/order-change GetAllOrders
     * @apiDescription Change all wares order
     * @apiGroup WareOrder
     * @apiVersion 0.0.1
     */
    public function index(Request $request)
    {
        $wareOrders = WareOrder::all();
        $wares = []; // list of wares ordered by order

        foreach ($wareOrders as $ware) {
          $wares[] = Ware::find($ware->ware_id);
        }

        return response()->json($wares, 200);
    }

    /**
     * @param Request $request
     * @api {POST} /api/order-change StoreOrder
     * @apiDescription StoreWaresOrderGiveAway
     * @apiGroup WareOrder
     * @apiVersion 0.0.1
     */
    public function store(Request $request)
    {
        $wareIDsArray = $request->data;
        // Log::info($request);

        foreach ($wareIDsArray as $key => $value) {
            $value = (int) $value;
            // Log::info("Value is $value, key is $key");

            $wareOrder          = WareOrder::find($key + 1);
            $wareOrder->ware_id = $value;
            $wareOrder->save();
        }

        return response()->json($wareIDsArray, 200);
    }
}
