<?php

namespace App\Http\Controllers\Api\Stock;

use Action;
use ActionType;
use App\Http\Controllers\Controller;
use App\Models\Ware;
use App\Models\WareOrder;
use Illuminate\Http\Request;
use JWTAuth;

class WareController extends Controller
{
    /**
     * @api {GET} /api/stock/ware GetAllWares
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Display a listing of the resource.
     */
    public function index()
    {
        $wares = Ware::all();

        return response()->json($wares, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @api {POST} /api/stock/ware Store Ware
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ware               = new Ware();
        $ware->name         = $request->name;
        $ware->framework_id = $request->framework_id;
        $ware->packaging_id = $request->packaging_id;
        $ware->sticker_id   = $request->sticker_id;
        $ware->save();

        $wareOrder = new WareOrder();
        $wareOrder->ware_id = $ware->id;
        $wareOrder->save();

        return response()->json(['message' => 'created', 'status' => 'ok', 'id' => $ware->id], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * @api {PUT} /api/stock/ware Update Ware
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Update the specified resource in storage.
     */
    public function update(
        Request $request,
        $id
    ) {
        $ware               = Ware::find($id);
        $ware->name         = $request->name;
        $ware->framework_id = $request->framework_id;
        $ware->packaging_id = $request->packaging_id;
        $ware->sticker_id   = $request->sticker_id;
        $ware->save();

        // get action does user
        $user = JWTAuth::toUser($request->header('Authorization'));

        Action::do(2, 'Изменение товара ' . $ware->name, $user->id);

        return response()->json(['message' => 'updated', 'status' => 'ok', 'id' => $ware->id], 200);
    }

    /**
     * @api {DELETE} /api/stock/ware/:id Delete ware
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Ware::find($id)->delete();

        WareOrder::where('ware_id', $id)->delete();

        return response()->json(['message' => 'deleted'], 200);
    }

    /**
     * @api {POST} /api/stock/wareshow/hide Hide ware
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription Hide ware
     */
    public function hideVisible(Request $request, $id) {

        $ware = Ware::find($id);
        $ware->is_show = false;
        $ware->save();

        $user = JWTAuth::toUser($request->header('Authorization'));

        Action::do(ActionType::where('slug', 'hide_ware')->first()->id, 'Скрыт товар ' . $ware->name, $user->id);

        return response()->json($ware, 200);
    }

    /**
     * @api {POST} /api/stock/wareshow/show ShowWare
     * @apiName ShowWare
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiDescription  Show hidden ware
     */
    public function showVisible(Request $request,$id) {
        $ware = Ware::find($id);

        $user = JWTAuth::toUser($request->header('Authorization'));

        if ($ware->is_show) {
            return response()->json(['message' => 'Ware is already published'], 301);
        } else {
            $ware->is_show = true;
            $ware->save();

            Action::do(ActionType::where('slug', 'show_ware')->first()->id, 'Опубликован товар ' . $ware->name, $user->id);

            return response()->json($ware, 200);
        }
    }
}
