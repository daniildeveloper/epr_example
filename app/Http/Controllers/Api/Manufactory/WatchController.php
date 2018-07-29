<?php

namespace App\Http\Controllers\Api\Manufactory;

use App\Http\Controllers\Controller;
use App\Models\Manufactory\Watch;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Pusher\Pusher;

class WatchController extends Controller
{
    /**
     * Pusher object
     * @var Pusher\Pusher
     */
    private $pusher;

    public function __construct() {
        $options = array(
            'cluster'   => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        );
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
    }
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

    private function calculateWatchFinaces($montly_payment, $created_at, $payment, $watch_id)
    {
        $created_date = Carbon::parse($created_at);
        $now          = Carbon::now();

        $worked_days = $created_date->diffInDays($now);

        $payment = $worked_days * ($montly_payment / 30);

        // get all watch payments
        $payments = WatchMoneyTransaction::where('watch_id', $watch_id)->get();
        // add or decrease watch payments
        foreach ($payments as $p) {
            if ($p->refill === 1) {
                $payment += (int) $p->sum;
            } elseif ($p->refill === 0) {
                $payment -= (int) $p->sum;
            }
        }

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

    public function getOpenWatches(Request $request)
    {
        $watcher_id = $request->watcher_id;

        $watches = Watch::where(function ($query) use ($watcher_id) {
            $query->where('watcher_id', $watcher_id)
                ->where('');
        })->get();

        return response()->json($watches, 200);
    }

    /**
     * @api {POST} /api/manufactory/watch/end EndWatch
     * @apiVersion 0.0.1
     * @apiGroup Manufactory
     * @apiRequestExample JSON:
     *     {
     *         "watch_id": 1,
     *     }
     */
    public function endWatch(Request $request) {
        // 1. Get watch
        $watch = Watch::findOrFail($request->watch_id);
        // 2. Calculate watchers profit
        $watch_end_payment = $this->calculateWatchFinaces($watch->montly_payment, $watch->created_at, $watch->payment, $watch->id);
        // 3. Write watch profit payment end
        $watch->watch_end_payment = $watch_end_payment;
        $watch->save();
        // 5. Trigger push event
        $this->pusher->trigger('watch', 'wtch.closed', ['message' => 'watch is closed']);
        // 4. Return watch object
        return response()->json($watch, 200);
    }
}
