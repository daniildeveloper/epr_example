<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\DepartamentsBinding;
use App\Models\Framework;
use App\Models\Inventory;
use App\Models\InventorySubmit as Submit;
use App\Models\Packaging;
use App\Models\RestFramework;
use App\Models\Sticker;
use Illuminate\Http\Request;
use JWTAuth;
use DB;

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
        $user = JWTAuth::toUser($request->header('Authorization'));

        $departament_id            = DepartamentsBinding::first()->workers_id;
        $inventory                 = new Inventory();
        $inventory->departament_id = $departament_id;
        $inventory->answered_id    = $user->id;
        $inventory->component_type = $request->component_type;
        $inventory->component_id   = $request->component_id;
        $inventory->expected_rests = $this->calclulateExpectedRests($departament_id, $request->component_type, $request->component_id)['expected_rests'];
        $inventory->expected_sum   = $this->calclulateExpectedRests($departament_id, $request->component_type, $request->component_id)['expected_sum'];
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
        //
    }

    /**
     * Calculate expected rests for each departament
     * @param  [type] $departament_id [description]
     * @return [type]                 [description]
     */
    public function calclulateExpectedRests(
        $departament_id,
        $component_type,
        $component_id
    ) {
        return 1000 * 1;
    }

    public function data()
    {
        return reponse()->json([
            'frameworks'      => Framework::all(),
            'rest_frameworks' => RestFramework::all(),
            'packagings'      => Packaging::all(),
            'sticker'         => Sticker::all(),
        ], 200);
    }
}
