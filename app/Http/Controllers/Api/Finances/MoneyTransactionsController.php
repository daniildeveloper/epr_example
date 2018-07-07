<?php

namespace App\Http\Controllers\Api\Finances;

use App\Models\MoneyRequest;
use App\Models\MoneyTransaction;
use App\Models\Purse;
use Illuminate\Http\Request;
use JWTAuth;
use App\Http\Controllers\Controller;

class MoneyTransactionsController extends Controller
{
    /**
     * @param Request $request
     */
    public function getAllMoneyTransactions(Request $request) {
        $transactions = MoneyTransaction::paginate(15);

        foreach ($transactions as $t) {
            $t->purseFrom = $t->purseFrom;
            $t->purseTo = $t->purseTo;

            if ($t->proposal_id !== null) {
                $t->proposal = $t->proposal;
            }
        }

        return response()->json($transactions, 200);
    }

    /**
     * @param Request $request
     */
    public function getAllNotConfirmedRequests(Request $request)
    {
        $requests = MoneyRequest::where('confirmed', false)->get();

        return response()->json($requests, 200);
    }

    /**
     * @param Request $request
     */
    public function storeRequest(Request $request)
    {
        $user                        = JWTAuth::toUser($request->header('Authorization'));
        $moneyReqeust                = new MoneyRequest();
        $moneyReqeust->purse_from_id = Purse::where('slug', 'sale_oborot')->first()->id;
        $moneyReqeust->purse_to_id   = Purse::where('slug', 'chemie_purse')->first()->id;
        $moneyReqeust->sum           = $request->sum;
        $moneyReqeust->argument      = $request->argument;
        $moneyReqeust->save();

        // Action::create([9, 'Запрос на сумму ' . $request->sum, $user->id]);

        return response()->json($moneyReqeust, 200);
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function answerToRequest(
        Request $request,
        $id
    ) {
        $moneyRequest    = MoneyRequest::find($id);
        $user            = JWTAuth::toUser($request->header('Authorization'));
        $responseSuccess = $request->success;

        if ($moneyRequest->confirmed === true) {
            return response()->json($moneyRequest, 200);
        }

        if ($responseSuccess === true) {
            $moneySend                = new MoneyTransactions();
            $moneySend->purse_from_id = $moneyRequest->purse_from_id;
            $moneySend->purse_to_id   = $moneyRequest->purse_to_id;
            $moneySend->sum           = $moneyRequest->sum;
            $moneySend->argument      = $request->argument;
            $moneySend->save();

            $moneyRequest->confirmed = true;
            $moneyRequest->save();

            // Action::create([9, 'Отправлено ' . $request->sum . "c кошелька " . $moneySend->purse_from_id . " на кошелек " . $moneySend->purse_to_id, $user->id]);

            return response()->json($moneySend, 200);
        } else {
            $moneyRequest->confirmed = true;
            $moneyRequest->save();
            return response()->json(['message' => 'declined'], 200);
        }
    }
}
