<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use App\Models\PackagingManipulation;
use App\Models\RestFramework as Rest;
use App\Models\RestFrameworkManipulation;
use App\Models\StickerManipulation;
use App\Models\Ware;
use DB;
use Illuminate\Http\Request;
use Log;

class StockController extends Controller
{
    /**
     * @api {GET} /api/open/stock/ware-rests/all All available ware rests
     * @apiName Ware rests
     * @apiGroup OpenApi
     * @apiVersion 0.0.1
     * @apiSuccess {JSON} Json with available wares and rests:
     *
     * @param Request $request
     */
    public function allWareRests(Request $request)
    {
        $result = [];

        $rests = Rest::all();

        foreach ($rests as $rest) {
            $component = $rest->id;

            // Вычиляем последнюю инвенторизацию
            $lastInventory = DB::table('inventories')
                ->where(function ($query) use ($component) {
                    $query
                        ->where('component_id', $component)
                        ->where('component_type', 'rest_frameworks');
                })
                ->orderBy('id', 'desc')
                ->first();
            $ware = Ware::where('framework_id', $rest->id)->first();
            if ($ware->is_show) {
                $ware->packaging = $ware->packaging;
                $ware->sticker   = $ware->sticker;

                $stickersLastInventory = DB::table('inventories')
                    ->where(function ($query) use ($ware) {
                        $query
                            ->where('component_id', $ware->sticker->id)
                            ->where('component_type', 'stickers');
                    })
                    ->orderBy('id', 'desc')
                    ->first();
                $packagingsLastInventory = DB::table('inventories')
                    ->where(function ($query) use ($ware) {
                        $query
                            ->where('component_id', $ware->packaging->id)
                            ->where('component_type', 'packagings');
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                $componentRest = [
                    'ware_id'          => $ware->id,
                    'ware_name'        => $ware->name,
                    'framework_id'     => $rest->id,
                    'framework_name'   => $rest->name,
                    'rests'            => $this->findSingleWareRests($request->type, $rest->id, $lastInventory->created_at, $lastInventory->real_rest),
                    'sticker_rests'    => $ware->sticker->name . ' (' . $this->getStickerRests($ware->sticker->id, $stickersLastInventory->created_at, $stickersLastInventory->real_rest) . ')',
                    'packaging_rests'  => $ware->packaging->name . ' (' . $this->getPackagingRests($ware->packaging->id, $packagingsLastInventory->created_at, $packagingsLastInventory->real_rest) . ')',
                    'minimal_in_stock' => $rest->minimal_in_stock,
                ];
                $result[] = $componentRest;
            }
        }
        // dd($result);
        return response()->json($result, 200);
    }

    /**
     * @param $type
     * @param $id
     * @param $lastInventory
     * @param array $lastInventoryDate
     */
    private function findSingleWareRests(
        $type,
        $id,
        $lastInventory,
        $realRest
    ) {
        // Берем все транзакции в конечный компонент
        $transactionsTo = RestFrameworkManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('framework_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 1);
        })->get();

        // все транзакции из конечного компонента
        $transactionsFrom = RestFrameworkManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('framework_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 0);
        })->get();

        // looping and calculate all sum for toTansactions
        $transactionsToSum = 0;
        foreach ($transactionsTo as $tt) {
            $transactionsToSum += $tt->count;
        }

        // looping and calculate all FromTransactions
        $transactionsFromSum = 0;
        foreach ($transactionsFrom as $tt) {
            $transactionsFromSum += $tt->count;
        }
        // вычисляем
        $rest = $realRest + $transactionsToSum - $transactionsFromSum;

        return $rest;
    }

    /**
     * @param $id
     * @param $lastInventory
     * @param $realRest
     */
    private function getStickerRests(
        $id,
        $lastInventory,
        $realRest
    ) {
        // Log::info('sticker id:' . $id);
        // Берем все транзакции в конечный компонент
        $transactionsTo = StickerManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('sticker_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 1);
        })->get();

        // все транзакции из конечного компонента
        $transactionsFrom = StickerManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('sticker_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 0);
        })->get();

        // looping and calculate all sum for toTansactions
        $transactionsToSum = 0;
        foreach ($transactionsTo as $tt) {
            $transactionsToSum += $tt->count;
        }

        // looping and calculate all FromTransactions
        $transactionsFromSum = 0;
        foreach ($transactionsFrom as $tt) {
            $transactionsFromSum += $tt->count;
        }
        // вычисляем
        $rest = $realRest + $transactionsToSum - $transactionsFromSum;

        return $rest;
    }

    /**
     * @param $id
     * @param $lastInventory
     * @param $realRest
     * @return mixed
     */
    private function getPackagingRests(
        $id,
        $lastInventory,
        $realRest
    ) {
        // Log::info('Packaging id:' . $id);
        // Берем все транзакции в конечный компонент
        $transactionsTo = PackagingManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('packaging_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 1);
        })->get();

        // все транзакции из конечного компонента
        $transactionsFrom = PackagingManipulation::where(function ($query) use ($id, $lastInventory) {
            $query
                ->where('packaging_id', $id)
                ->where('created_at', '>', $lastInventory)
                ->where('refill', 0);
        })->get();

        // looping and calculate all sum for toTansactions
        $transactionsToSum = 0;
        foreach ($transactionsTo as $tt) {
            $transactionsToSum += $tt->count;
        }

        // looping and calculate all FromTransactions
        $transactionsFromSum = 0;
        foreach ($transactionsFrom as $tt) {
            $transactionsFromSum += $tt->count;
        }
        // вычисляем
        $rest = $realRest + $transactionsToSum - $transactionsFromSum;

        return $rest;
    }

    public function getWaresNames()
    {
        $wares = Ware::all();

        $result = [];

        foreach ($wares as $ware) {
            $ware['framework'] = Rest::find($ware->framework);
        }
    }
}
