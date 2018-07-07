<?php

namespace App\Http\Controllers\Api;

use App\Action;
use App\Models\MoneyTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActionsController extends Controller
{
    /**
     * @api {GET} /api/action/data GetRecentActions
     * @apiName Get recent actions
     * @apiVersion 0.0.1
     * @apiGroup Action
     * @param Request $request
     */
    public function index(Request $request)
    {
        $actions = Action::orderBy('id', 'desc')->paginate(30);

        foreach ($actions as $action) {
            $action->action_type = $action->actionType;
            $action->user        = $action->user;
        }

        return response()->json($actions, 200);
    }

    /**
     * @param Request $request
     */
    public function getMoneyTrasnactions(Request $request)
    {
        $moneyTransactions = MoneyTransaction::orderBy('id', 'desc')->paginate(30);

        foreach ($moneyTransactions as $t) {
            $t->purseFrom = $t->purseFrom;
            $t->purseTo   = $t->purseTo;
            $t->proposal  = $t->proposal;
        }

        return response()->json($moneyTransactions, 200);
    }
}
