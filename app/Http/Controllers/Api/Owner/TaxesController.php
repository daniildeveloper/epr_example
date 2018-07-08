<?php

namespace App\Http\Controllers\Api\Owner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tax;

class TaxesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     * @api {GET} /api/owner/taxes GetTaxes
     * @apiName GetTaxes
     * @apiDescription Get all available taxes
     * @apiGroup Owner
     * @apiVersion 0.0.1
     */
    public function index()
    {
        $taxes = Tax::all();

        return response()->json($taxes, 200);
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
        $tax = new Tax();

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
     * 
     * @api {PUT} /api/owner/taxes/update/{id} UpdateTax
     * @apiVersion 0.0.1
     * @apiDescription Update tax procent
     * @apiGroup Owner
     */
    public function update(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);

        // update tax
        $tax->tax = $request->tax;
        $tax->save();

        return response()->json($tax, 200);
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
}
