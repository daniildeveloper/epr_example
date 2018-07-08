<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use Searchy;
use App\Models\Client;
use App\Http\Controllers\Controller;

class GlobalSearchController extends Controller
{
    /**
     * @param Request $request
     */
    public function search(Request $request)
    {
        $string = $request->search;

        $proposals = Searchy::proposals('code', 'workers_deadline', 'created_at', 'notes', 'client', 'client_phone', 'object')->query($string)->get();

        return response()->json([
            'data' => [
                'proposals' => $proposals,
            ],
        ], 200);
    }
}
