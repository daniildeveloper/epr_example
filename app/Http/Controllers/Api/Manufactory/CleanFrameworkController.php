<?php

namespace App\Http\Controllers\Api\Manufactory;

use App\\Models\Manufactory\CleanFramework;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CleanFrameworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $c = CleanFramework::all();

        return response()->json($c, 200);
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
     * @api {POST} /api/manufactory/clean-framework StoreCleanFramework
     * @apiDescription store clean framework
     * @apiGroup Manufactory
     * @apiVersion 0.0.1
     */
    public function store(Request $request)
    {
        $clean_framework = new CleanFramework();
        $clean_framework->name = $request->name;
        $clean_framework->save();

        return response()->json($clean_framework, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $clean_framework = CleanFramework::findOrFail($id);

        return response()->json($clean_framework, 200);
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
    public function update(Request $request, $id)
    {
        $clean_framework = CleanFramework::findOrFail($id);
        $clean_framework->name = $request->name;
        $clean_framework->save();

        return response()->json($clean_framework, 200);
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
