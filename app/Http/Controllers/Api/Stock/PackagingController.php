<?php

namespace App\Http\Controllers\Api\Stock;

use Action;
use App\Http\Controllers\Controller;
use App\Models\Packaging;
use Illuminate\Http\Request;
use JWTAuth;

class PackagingController extends Controller
{
    /**
     * @api {GET} /api/stock/packaging All packaging
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccessResponse {JSON} json array of packaging
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
        $packagings = Packaging::all();

        return response()->json($packagings, 200);
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
     * @api {POST} /api/stock/packaging Store packaging
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccess JSON response with created packaging
     * @apiSuccessExample JSON:
     *     {
     *         "message": "Created",
     *         "id": 1,
     *     }
     * @apiDescription Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $packaging                   = new Packaging();
        $packaging->name             = $request->name;
        $packaging->price            = $request->price;
        $packaging->minimal_in_stock = $request->minimal_in_stock;
        $packaging->save();

        return response()->json(['message' => "Created", 'id' => $packaging->id], 200);
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
     * @api {PUT} /api/stock/packaging packaging
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
        $user                        = JWTAuth::toUser($request->header('Authorization'));
        $packaging                   = Packaging::find($id);
        $packaging->name             = $request->name;
        $packaging->minimal_in_stock = $request->minimal_in_stock;
        $packaging->price            = $request->price;
        $packaging->save();

        Action::do(2, 'Изменение упаковки ' . $packaging->name, $user->id);

        return response()->json(['message' => 'updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Packaging::find($id)->delete();
    }
}
