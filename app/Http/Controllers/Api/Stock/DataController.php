<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Framework;
use App\Models\Packaging;
use App\Models\RestFramework;
use App\Models\Sticker;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * @api {GET} /api/stock/data/supply GetSupplyData
     * @apiVersion 0.0.1
     * @apiGroup Supply
     * @apiSuccessExample JSON:
     *     {
     *         "types" : [
     *             {
     *                 "name": "Основа",
     *                 "type": "framework"
     *             },...
     *         ],
     *         "frameworks": [],
     *         "packagings": [],
     *         "stickers": [],
     *         "suppliers": [],
     *     }
     * @apiDescription Protected with supply middleware
     */
    public function getSupplyData(Request $request)
    {

        $frameworks = Framework::all();
        $packagings = Packaging::all();
        $stickers   = Sticker::all();

        return response()->json([
            'types'      => [
                [
                    'name'  => 'Основа',
                    'table' => 'frameworks',
                ], [
                    'name'  => 'Упаковка',
                    'table' => 'packagings',
                ], [
                    'name'  => 'Наклейки',
                    'table' => 'stickers',
                ],
            ],
            'frameworks' => $frameworks,
            'packagings' => $packagings,
            'stickers'   => $stickers,
            'suppliers'  => $suppliers,
        ], 200);

    }

    /**
     * @api {GET} /api/stock/data/ware GetWareData
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Data to create Ware
     * @apiSuccessExample JSON:
     *     {
     *         "frameworks": [],
     *         "packagings": [],
     *         "stickers": [],
     *     }
     */
    public function getWareData(Request $request)
    {
        $frameworks = RestFramework::all();
        $packagings = Packaging::all();
        $stickers   = Sticker::all();

        return response()->json([
            'frameworks' => $frameworks,
            'packagings' => $packagings,
            'stickers'   => $stickers,
        ], 200);
    }
}
