<?php

namespace App\Http\Controllers\Api\Stock;

use Action;
use App\Http\Controllers\Controller;
use App\Models\Sticker;
use Illuminate\Http\Request;
use JWTAuth;

class StickerController extends Controller
{

    /**
     * @api {GET} /api/stock/sticker All sticker
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccessResponse {JSON} json array of sticker
     * @apiSuccessExample JSON:
     *     {
     *         [
     *             {
     *                 "name": "Some name",
     *                 "minimal_in_stock": 100,
     *                 "price": 1200
     *             },
     *             ...
     *         ]
     *     }
     * @apiDescription Display a listing of the resource.
     */
    public function index()
    {
        $stickers = Sticker::all();

        return response()->json($stickers, 200);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @api {POST} /api/stock/sticker Store sticker
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccess JSON response with created sticker
     * @apiSuccessExample JSON:
     *     {
     *         "message": "Created",
     *         "id": 1,
     *     }
     * @apiDescription Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $sticker                   = new Sticker();
        $sticker->name             = $request->name;
        $sticker->price            = $request->price;
        $sticker->minimal_in_stock = $request->minimal_in_stock;
        $sticker->save();

        return reыponse()->json(['message' => "Created", 'id' => $sticker->id], 200);
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
     * @api {PUT} /api/stock/sticker Update sticker
     * @apiDescription Update the specified resource in storage.
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccessExample JSON:
     *     {
     *         "message": "updated"
     *     }
     */
    public function update(
        Request $request,
        $id
    ) {
        $user                      = JWTAuth::toUser($request->header('Authorization'));
        $sticker                   = Sticker::find($id);
        $sticker->name             = $request->name;
        $sticker->minimal_in_stock = $request->minimal_in_stock;
        $sticker->price            = $request->price;
        $sticker->save();

        Action::do(2, 'Изменение наклейки ' . $sticker->name, $user->id);

        return response()->json(['message' => 'updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Sticker::find($id)->delete();
    }
}
