<?php

namespace App\Http\Controllers\Proposal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProposalNotAllowedArgument as Argument;

class NotAllowedArgumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $argument = new Argument();
        $argument->proposal_id = (int) $request->proposal_id;
        $argument->argument = $request->argument;
        $argument->save();

        // $argument->proposal = $argument->proposal;

        return response()->json($argument, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $argument = Argument::findOrFail($id);

        return response()->json($argument, 200);
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

    public function find($proposal_id) {
        $argument = Argument::where('proposal_id', $proposal_id)->orderBy('id', 'desc')->first();

        return response()->json($argument, 200);
    }
}
