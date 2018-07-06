<?php

namespace App\Http\Controllers\Api\Stock;

use App\Models\ProposalWare;
use App\Models\Ware;
use App\Models\RestFrameworkAccountingPeriod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockAccountingController extends Controller
{
    public function getActualWareWaste(Request $request) {
      // get latest accounting period
      $latestPeriod = RestFrameworkAccountingPeriod::orderBy('id', 'desc')->first();
      
      $wares = Ware::all();

      foreach ($wares as $ware) {
        $ware->involved = $this->getInvolvedInProductionCount($ware->id, $latestPeriod->created_at);
      }

      return response()->json($wares);
    }

    public function setWareRests(Request $reqeust) {
      
    }

    /**
     * Get at latest period involved proposal wares
     * @param  [type] $wareID           [description]
     * @param  [type] $latestPeriodDate [description]
     * @return [type]                   [description]
     */
    public function getInvolvedInProductionCount($wareID, $latestPeriodDate) {
      // get latest proposal wares
      $proposalWares = ProposalWare::where(function ($query) use ($wareID, $latestPeriodDate) {
        $query->where('created_at', '>', $latestPeriodDate)
        ->where('ware_id', $wareID);
      })->get();

      // result to return
      $result = 0;

      // calculate involved count
      foreach ($proposalWares as $ware) {
        $result += (int) $ware->count;
      }

      // return count
      return $result;
    }
}
