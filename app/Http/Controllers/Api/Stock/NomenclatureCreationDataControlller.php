<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Departament;
use App\Models\Framework;
use App\Models\Packaging;
use App\Models\RestFramework;
use App\Models\Sticker;
use App\Models\Ware;
use Illuminate\Http\Request;

class NomenclatureCreationDataControlller extends Controller
{
    /**
     * @param Request $request
     */
    public function get(Request $request)
    {
        return response()->json(['data' => array(
            'frameworks'      => Framework::all(),
            'rest_frameworks' => RestFramework::all(),
            'packagings'      => Packaging::all(),
            'sticker'         => Sticker::all(),
            'departaments'    => Departament::all(),
            'wares'           => Ware::all(),
        )]);
    }

    /**
     * @param Request $request
     */
    public function getFrameworks(Request $request)
    {
        return response()->json(['data' => Framework::all()]);
    }

    /**
     * @param Request $request
     */
    public function getRestFrameworks(Request $request)
    {
        return response()->json(['data' => RestFramework::all()]);
    }

    /**
     * @param Request $request
     */
    public function getWares(Request $request)
    {
        $wares = Ware::all();

        $result = [];

        foreach ($wares as $ware) {
            $tmp['id']            = $ware->id;
            $tmp['name']          = $ware->name;
            $tmp['framework']     = $ware->framework;
            $tmp['packaging']     = $ware->packaging;
            $tmp['sticker']       = $ware->sticker;
            $tmp['minimal_price'] = $tmp['framework']->price + $tmp['packaging']->price + $tmp['sticker']->price;
            $tmp['is_show']       = $ware->is_show;
            $result[]             = $tmp;
        }

        return response()->json(['data' => $result], 200);
    }

    /**
     * Packagings
     * @return mixed json response with data
     */
    public function getPackagings()
    {
        return response()->json(['data' => Packaging::all()]);
    }

    public function getStickers()
    {
        return response()->json(['data' => Sticker::all()]);
    }
}
