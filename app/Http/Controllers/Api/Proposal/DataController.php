<?php

namespace App\Http\Controllers\Api\Proposal;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\Tax;
use App\Models\Ware;
use App\Models\WareOrder;
use DB;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Get proposal creation data
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getProposalCreationData(Request $request)
    {
        $wareOrders = WareOrder::all();
        $wares = []; // list of wares ordered by order

        foreach ($wareOrders as $ware) {
          $wares[] = Ware::find($ware->ware_id);
        }

        $taxes = Tax::all();

        foreach ($taxes as $tax) {
            $tax->name .= " ($tax->tax%)";
        }

        $custom = array(
            'id'   => 3,
            'name' => 'В ручную',
            'tax'  => 0.0,
        );

        $taxes[] = $custom;

        return response()->json(['data' => [
            'wares' => $wares,
            'taxes' => $taxes,
        ]]);
    }

    /**
     * @param Request $request
     */
    public function searchClients(Request $request)
    {
        $s   = $request['query'];
        $res = DB::table('clients')->where('name', 'LIKE', "%$s%")->get();

        return response()->json($res, 200);
    }

    /**
     * @param Request $request
     */
    public function searchObjects(Request $request)
    {
        $q         = $request['query'];
        $client_id = $request['client_id'];

        $res = DB::table('objects')->where([
            ['address', 'LIKE', "%$q%"],
            ['client_id', $client_id],
        ])->get();

        return response()->json($res, 200);
    }

    /**
     * @param Request $request
     */
    public function getStatusesList(Request $request)
    {
        $statuses = ProposalStatus::all()->toArray();

        $statuses = array_filter($statuses, function ($status) {
            if ($status['slug'] != 'declined') {
                return $status;
            }
        });

        return response()->json($statuses, 200);
    }

    public function getTaxData()
    {
        $taxes = Tax::all();

        $custom = array(
            'name' => 'В ручную',
            'tax'  => 0.0,
        );

        $taxes[] = $custom;

        return response()->json($taxes, 200);
    }
}
