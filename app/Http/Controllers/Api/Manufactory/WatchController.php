<?php

namespace App\Http\Controllers\Api\Manufactory;

use App\Http\Controllers\Controller;
use App\Models\Manufactory\Watch;
use App\User;
use Illuminate\Http\Request;

class WatchController extends Controller
{
    /**
     * @api {GET} /api/manufactory/watchers GetManufactoryWatchers
     * @apiGroup Manufactory
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

    public function getWatchesByWatcher(Request $request)
    {
        $watcher_id = $request->user_id;
        $watches    = Watch::where('watcher_id', $watcher_id)->limit(20)->get();

        return response()->json($watches, 200);
    }

    public function store(Request $request)
    {}

    public function addMoney(Request $request)
    {}

    public function decreaseMoney(Request $request)
    {}

    private function calculateWatchFinaces($montly_payment, $created_at, $payment)
    {}

    public function getMyWatch(Request $request)
    {}
}
