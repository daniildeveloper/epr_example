<?php

namespace App\Http\Controllers\Api\Finances;

use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndPurseDetail;
use App\Models\FinancialAccountingPurseRests;
use App\Models\Proposal;
use App\Models\ProposalWare;
use App\Models\Argument;
use App\Models\ProposalStatus;
use App\Models\Purse;
use DB;
use Illuminate\Http\Request;
use Log;
use App\Http\Controllers\Controller;

class FinanceAccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accountings = DB::table('financial_accountings')->paginate(12);

        foreach ($accountings as $acc) {
            $acc->purses = FinancialAccountingPurseRests::where('accounting_id', $acc->id)->get();
        }

        return response()->json($accountings, 200);
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
        $accounting = new AccountingPeriodEnd();
        $accounting->save();

        $purses = Purse::all();

        foreach ($purses as $purse) {
            $pp                = new AccountingPeriodEndPurseDetail();
            $pp->accounting_id = $accounting->id;
            $pp->purse_id      = $purse->id;
            $pp->rest          = Purse::restCalculate($purse->id);
            $pp->save();
        }

        return response()->json([
            'accounting' => $accounting,
        ], 200);
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
     * @param Request $request
     */
    public function getData(Request $request)
    {
        // Получаем последние транзакции
    }

    /**
     * get all unclosed proposals
     * @param  Request $request [description]
     * @return [type]           [description]
     * @api {GET} /api/purse/accounting/finances/unclosed-proposals GetUnclosedProposals
     * @apiDescription Get unclosed proposals
     * @apiGroup Accounting
     * @apiVersion 0.0.1
     */
    public function getUnclosed(Request $request)
    {
        $accountingPeriodEnd        = AccountingPeriodEnd::latest();
        $confirmedProposalsStatusId = ProposalStatus::where('slug', 'acepted_by_client')->first()->id;
        $proposals                  = Proposal::where(function ($query) use ($confirmedProposalsStatusId) {
            $query->where('closed', 0)
                ->where('status_id', $confirmedProposalsStatusId);
        })->get();

        // foreach ( $proposals as $proposal ) {
        //     Log::info("Unclosed proposal code " . $proposal->code);
        // }

        foreach ($proposals as $proposal) {
            $proposal->client = $proposal->client;
            if ($proposal->object_id != null) {
                $proposal->object = $proposal->object;
            }
            if ($proposal->status_id === 2) {
                $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
            }
            $proposal->creator = $proposal->creator;
        }

        return response()->json($proposals, 200);
    }

}
