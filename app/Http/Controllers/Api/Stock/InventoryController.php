<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventorySubmit as Submit;
use App\Models\Packaging;
use App\Models\RestFramework;
use App\Models\Sticker;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use Log;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $is = DB::table('inventories')->orderBy('id', 'desc')->paginate(40);

        $types = [
            [
                'slug' => 'Основы',
                'name' => 'rest_frameworks',
            ], [
                'slug' => 'Наклейки',
                'name' => 'stickers',
            ], [
                'slug' => 'Упаковки',
                'name' => 'packagings',
            ],
        ];

        return response()->json($is, 200);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'component_type' => 'required',
            'component_id'   => 'required|integer|min:1',
            'expected_rests' => 'required|integer|min:1',
            'expected_sum'   => 'required|integer|min:1',
            'real_sum'       => 'required|integer|min:1',
            'real_rest'     => 'required|integer|min:1',
        ];
        $input = $request->all();
        // validate data
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            Log::info($validator->errors()->all());
            return response()->json(['message' => 'Заполните все поля', 'errors' => $validator->errors(), 'status' => 'validator_errors'], 200);
        }

        $user = JWTAuth::toUser($request->header('Authorization'));

        $inventory                 = new Inventory();
        $inventory->answered_id    = $user->id;
        $inventory->component_type = $request->component_type;
        $inventory->component_id   = $request->component_id;
        $inventory->expected_rests = $request->expected_rests;
        $inventory->expected_sum   = $request->expected_sum;
        $inventory->real_sum       = $request->real_sum;
        $inventory->real_rest      = $request->real_rest;

        if ($request->real_rest != $inventory->expected_rests) {
            $inventory->diffirence     = $inventory->expected_rests - $request->real_rest;
            $inventory->diffirence_sum = $inventory->expected_sum - $request->real_sum;
            $inventory->save();

            $d               = new Submit();
            $d->inventory_id = $inventory->id;
            $d->diffirence   = $inventory->diffirence_sum;
            $d->answered_id  = $request->answered_id;
            $d->save();
        } else {
            $inventory->save();
        }

        return response()->json($inventory, 200);

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        $id
    ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // slinece is golden
    }

    public function data()
    {
        $types = [
            [
                'name' => 'Основы',
                'slug' => 'rest_frameworks',
            ], [
                'name' => 'Наклейки',
                'slug' => 'stickers',
            ], [
                'name' => 'Упаковки',
                'slug' => 'packagings',
            ],
        ];

        $data = [
            'rest_frameworks' => RestFramework::all(),
            'packagings'      => Packaging::all(),
            'stickers'        => Sticker::all(),
        ];
        return response()->json([
            'types' => $types,
            'data'  => $data]
            , 200);
    }

    public function inventory_submit(Request $request)
    {
        $inventory_submit = Submit::findOrFail($request->id);

        $inventory_submit->submited = true;
        $inventory_submit->save();

        return response()->json($inventory_submit, 200);
    }
}
