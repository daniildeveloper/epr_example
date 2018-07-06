<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\RestFrameworkManipulation;
use Illuminate\Http\Request;
use App\Models\MoneyTransactions;
use App\Models\Purse;
use App\Models\RestFramework;
use Log;
use JWTAuth;
use Action;

class RestFrameworkmanipulationsController extends Controller
{
    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $user = JWTAuth::toUser($request->header('Authorization'));
        // return response()->json($request);
        $manipulation               = new RestFrameworkManipulation();
        $manipulation->framework_id = $request->framework_id;
        $manipulation->count        = $request->count;
        $manipulation->refill       = $request->refill;
        $manipulation->proposal_id  = $request->proposal_id;
        
        

        $framework = RestFramework::find($request->framework_id);
        $sum = $framework->price * (int) $request->count;


        if ($manipulation->refill === true) {
          // если пополняем запасы - списываем деньги на покупку химии, то переводим деньги с покупки химии
          Log::info('Сумма для покупки химии ' . $sum);

          // Кошелек для снятия денег
          $purseFromID = Purse::where('slug', 'chemie_purse')->first()->id;

          // calculate rests for this purse to see how much i can translate
          if (Purse::restCalculate($purseFromID) < $sum) {
            return response()->json(['message' => 'Недостаточно средств для покупки'], 402);
          }

          $transaction = new MoneyTransactions();
          $transaction->purse_from_id = $purseFromID;
          $transaction->sum = $sum;
          $transaction->argument = "Перевод денег на покупку химии $framework->name";
          $transaction->save();
          $manipulation->save();

          // Action::do()
        } else {
          // если переводим запасы в деньги - пополняем
          Log::info('Сумма для перевода химии в деньги ' . $sum);
          $purseToID = Purse::where('slug', 'chemie_purse')->first()->id;

          $transaction = new MoneyTransactions();
          $transaction->purse_to_id = $purseToID;
          $transaction->sum = $sum;
          $transaction->argument = "Перевод химии в деньги $framework->name в $sum";
          $transaction->save();
          $manipulation->save();
        }

        return response()->json($manipulation, 200);
    }

    /**
     * @param Request $request
     * @param $framework_id
     */
    public function getLatest(
        Request $request,
        $framework_id
    ) {
        $manipulations = RestFrameworkManipulation::where('framework_id', $framework_id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return response()->json($manipulations, 200);
    }
}
