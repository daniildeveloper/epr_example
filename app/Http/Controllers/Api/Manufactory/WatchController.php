<?php

namespace App\Http\Controllers\Api\Manufactory;

use App\Http\Controllers\Controller;
use App\Models\Manufactory\Watch;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;

class WatchController extends Controller
{
    /**
     * @api {GET} /api/manufactory/watch/watchers GetManufactoryWatchers
     * @apiGroup Manufactory
     * @apiVersion 0.0.1
     */
    public function getWatchers(Request $request)
    {
        $users    = User::all();
        $watchers = []; // all watchers

        foreach ($users as $user) {
            if ($user->hasRole('worker')) {
                $watchers[] = $user;
            }
        }

        return response()->json($watchers, 200);
    }

    /**
     * @api {GET} /api//manufactory/watch/watcher-watches/:watcher_id GetWatchersWatches
     */
    public function getWatchesByWatcher(Request $request, $watcher_id)
    {
        $watches = Watch::where('watcher_id', $watcher_id)
            ->limit(20)
            ->with('watcher', 'watch_money_transactions')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($watches, 200);
    }

    /**
     * @api {POST} /api/manufactory/watch StoreWatch
     * @apiGroup Manufactory
     * @apiVersion 0.0.1
     */
    public function store(Request $request)
    {
        $watch                 = new Watch();
        $watch->watcher_id     = $request->watcher_id;
        $watch->begin_date     = Carbon::now()->format('Y-m-d');
        $watch->montly_payment = $request->montly_payment;
        $watch->save();

        return response()->json($watch, 200);
    }

    /**
     * @api {POST} /api/manufactory/watch/add-money AddMineyForWatch
     * @apiGroup Manufactory
     * @apiVersion 0.0.1
     */
    public function addMoney(Request $request)
    {
        $t           = new WatchMoneyTransaction();
        $t->watch_id = $request->watch_id;
        $t->sum      = $request->sum;
        $t->refill   = true;
        $t->save();

        return response()->json($t, 200);
    }

    /**
     * @api {POST} /api/manufactory/watch/minus-money MinusMoneyForWatch
     * @apiGroup Manufactory
     * @apiVersion 0.0.1
     */
    public function decreaseMoney(Request $request)
    {
        $t           = new WatchMoneyTransaction();
        $t->watch_id = $request->watch_id;
        $t->sum      = $request->sum;
        $t->refill   = false;
        $t->save();

        return response()->json($t, 200);
    }

    private function calculateWatchFinaces($montly_payment, $created_at, $payment)
    {
        $created_date = Carbon::parse($created_at);
        $now          = Carbon::now();

        $worked_days = $created_date->diffInDays($now);

        $payment = $worked_days * ($montly_payment / 30);

        return $payment;
    }

    /**
     * @api {GET} /api/manufactory/watch/my GetMyWatches
     * @apiVersion 0.0.1
     * @apiGroup Manufactory
     */
    public function getMyWatches(Request $request)
    {
        $user = JWTAuth::toUser($request->header('Authorization'));

        $watches = Watch::where('watcher_id', $user->id)->limit(20)->with('watcher', 'watch_money_transactions')->get();
    }
}
