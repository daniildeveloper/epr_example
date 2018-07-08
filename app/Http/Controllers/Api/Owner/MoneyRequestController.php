<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\MoneyRequest;
use App\Models\MoneySend;
use Illuminate\Http\Request;

class MoneyRequestController extends Controller
{
    /**
     * @api {POST} /api/msr/mr/store CreateMoneRequest
     * @apiGroup MoneyRequest
     * @apiVersion 0.0.1
     * @param Request $request
     */
    public function createMoneyRequest(Request $request)
    {
        $r           = new MoneyRequest();
        $r->sum      = (int) $request->sum;
        $r->argument = $request->argument;
        $r->save();

        return response()->json(['data' => $r], 200);
    }

    /**
     * @api {POST} /api/msr/ms/send SendMoney
     * @apiGroup MoneyRequest
     * @apiVersion 0.0.1
     * @param Request $request
     */
    public function createMoneySend(Request $request)
    {
        $send             = new MoneySend();
        $send->sum        = (int) $request->sum;
        $send->request_id = (int) $request->$request_id;
        $send->save();

        return response()->json(['data' => $send], 200);
    }

    /**
     * @api {GET} /api/msr/mr GetAllMoneyRequests
     * @apiVersion 0.0.1
     * @apiGroup MoneyRequest
     * @return [type] [description]
     */
    public function getAllMoneyRequests()
    {
        $requests = MoneyRequest::where('confirmed', false)->get();

        return response()->json($requests, 200);
    }

    /**
     * @api {POST} /api/msr/revoke RevokeMoneyRequest
     * @apiGroup MoneyRequest
     * @apiVersion 0.0.1
     * @param Request $request
     * @param $id
     */
    public function revokeMoneyRequest(
        Request $request,
        $id
    ) {
        // request to revoke
        $r            = MoneyRequest::find($id);
        $r->confirmed = true;
        $r->save();

        return response()->json(['message' => 'revoked', 'r' => $r], 200);
    }

    /**
     * @api {POST} /api/msr/money-get-confirm ConfirmMoneyGet
     * @apiVersion 0.0.1
     * @apiGroup MoneyRequest
     * @param Request $request
     */
    public function confirmMoneyGet(Request $request)
    {
        $send            = MoneySend::find($request->id);
        $send->confirmed = true;
        $send->save();

        return reponse()->json($send, 200);
    }
}
