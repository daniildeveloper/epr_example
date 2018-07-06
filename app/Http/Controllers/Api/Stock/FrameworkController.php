<?php

namespace App\Http\Controllers\Api\Stock;

use Action;
use App\Http\Controllers\Controller;
use App\Models\Framework;
use Illuminate\Http\Request;
use JWTAuth;

class FrameworkController extends Controller
{
    /**
     * @api {GET} /api/stock/framework All Frameworks
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccessResponse {JSON} json array of frameworks
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
        $frameworks = Framework::all();

        return response()->json($frameworks, 200);
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
     * @api {POST} /api/stock/framework Store Framework
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiSuccess JSON response with created framework
     * @apiSuccessExample JSON:
     *     {
     *         "message": "Created",
     *         "id": 1,
     *     }
     * @apiDescription Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $framework                   = new Framework();
        $framework->name             = $request->name;
        $framework->price            = $request->price;
        $framework->minimal_in_stock = $request->minimal_in_stock;
        $framework->save();

        return response()->json(['message' => "Created", 'id' => $framework->id], 200);
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
     * @api {PUT} /api/stock/framework UpdateFramework
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
        $framework                   = Framework::find($id);
        $framework->name             = $request->name;
        $framework->minimal_in_stock = $request->minimal_in_stock;
        $framework->price            = $request->price;
        $framework->save();

        Action::do(2, 'Изменение основы ' . $framework->name, $user->id);

        return response()->json(['message' => 'updated'], 200);
    }

    /**
     * @apiDescription Remove the specified resource from storage.
     * @api {DELETE} /api/stock/framework Delete Framework
     * @apiVersion 0.0.1
     * @apiGroup Stock
     * @apiParam  id of framework to delete
     */
    public function destroy($id)
    {
        Framework::find($id)->delete();
    }
}
