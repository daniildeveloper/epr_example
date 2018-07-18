<?php

namespace App\Http\Controllers\Api;

use App\Models\MoneyTransaction;
use App\Models\Proposal;
use App\Models\Purse;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use App\Models\RestFramework;
use App\Models\Ware;
use App\User;
use App\Models\WareDataChange;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function getMoneyData()
    {
        // 1. список всех продаж
        // $sales =
        // 2. список всех денежных переводов
        // 3. засунуть все в массив
        // 4. пройтись по массиву и выделить общие данные в отдельный массив
        // 5. вычислить прибыль
        // 6. вернуть все данные в json
    }

    /**
     * @api {POST} /api/report/money MoneyTransactions
     * @apiDescription Получаем все денежные переводы за определенный период.
     * @apiGroup Reports
     * @apiVersion 0.0.1
     * @apiParamExample RequestJson:
     *   {
     *     "period_begin": "date",
     *     "period_end": "date"
     *   }
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getMoneyTransactions(Request $request)
    {
        $periodBegin = $request->period_begin;
        $periodEnd   = $request->period_end;

        if ($periodBegin === null) {
            $periodBegin = Carbon::now()->subMonths(1);
        }

        if ($periodEnd === null) {
            $periodEnd = Carbon::now();
        }

        $transactions = MoneyTransaction::where(function ($query) use ($periodBegin, $periodEnd) {
            $query->where('created_at', '>', $periodBegin)
                ->where('created_at', '<', $periodEnd);
        })->get();

        $fileName = 'Финансы ' . $periodBegin . ' - ' . $periodEnd;

        $file = Excel::create($fileName, function ($excel) use ($transactions) {
            $excel->setTitle('Отчет по финансам за период ');
            $excel->sheet('Лист 1', function ($sheet) use ($transactions) {
                foreach ($transactions as $transaction) {
                    // Чтобы вычислить кошельки, мы проверяем на пустую строку
                    $purseFrom = $transaction->purse_from_id != '' ? Purse::find($transaction->purse_from_id)->name : '';
                    $purseTo   = $transaction->purse_to_id != '' ? Purse::find($transaction->purse_to_id)->name : '';
                    $sheet->appendRow(array(
                        $transaction->id,
                        $purseFrom,
                        $purseTo,
                        $transaction->proposal_id,
                        $transaction->sum,
                        $transaction->argument,
                    ));
                }
            });
        });

        $file = $file->string('csv');

        $response = [
            'name' => $fileName,
            'file' => $file,
        ];

        return response()->json($response);
    }

    /**
     * @param Request $request
     */
    public function getWares(Request $request)
    {
        // get all proposals in period
        $periodBegin = $request->period_begin;
        $periodEnd   = $request->period_end;

        if ($periodBegin === null) {
            $periodBegin = Carbon::now()->subMonths(1);
        }

        if ($periodEnd === null) {
            $periodEnd = Carbon::now();
        }

        $proposals = Proposal::where(function ($query) use ($periodBegin, $periodEnd) {
            $query->where('created_at', '>', $periodBegin)
                ->where('updated_at', '<', $periodEnd);
        })->get();

        // array to create data
        $proposalArray = [];
        // get all wares
        foreach ($proposals as $proposal) {
            $proposal->wares = $proposal->wares;
            // configure - proposal, wareName, wareprice, warecount
            foreach ($proposal->wares as $ware) {
                $ware->ware      = $ware->ware;
                $tmp             = [$proposal->code, $ware->ware->name, $ware->price_per_count, $ware->count];
                $proposalArray[] = $tmp;
            }
        }

        $fileName = 'Товары ' . $periodBegin . ' - ' . $periodEnd;

        $file = Excel::create($fileName, function ($excel) use ($proposalArray) {
            $excel->setTitle('Отчет по товарам за период ');
            $excel->sheet('Лист 1', function ($sheet) use ($proposalArray) {
                $sheet->fromArray($proposalArray);
            });
        });

        $file = $file->string('csv');

        $response = [
            'name' => $fileName,
            'file' => $file,
        ];

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @api {GET} /api/reports/proposal-wares GetProposalWares
     * @apiGroup Reports
     * @apiParamExample RequestJson:
     *   {
     *     "period_begin": "date",
     *     "period_end": "date"
     *   }
     */
    public function getChemieReport(Request $request)
    {
        // get all proposals in period
        $periodBegin = $request->period_begin;
        $periodEnd   = $request->period_end;

        if ($periodBegin === null) {
            $periodBegin = Carbon::now()->subMonths(1);
        }

        if ($periodEnd === null) {
            $periodEnd = Carbon::now();
        }

        // 1. Весь список по химиям
        $wares = Ware::all();

        // 2. Все заявки за период
        $proposals = Proposal::where(function ($query) use ($periodBegin, $periodEnd) {
            $query->where('created_at', '>', $periodBegin)
                ->where('updated_at', '<', $periodEnd);
        })->get();

        foreach ($proposals as $proposal) {
            $proposal->wares = $proposal->wares;
        }

        // 3. Массив для резульатов
        $result = [];

        // 4. Пройтись по заявкам и засовывать каждый товар в результрующий массив
        foreach ($wares as $ware) { // push to result initial data
            $r = [];
            $r['ware_id'] = $ware->id;
            $r['ware_name'] = $ware->name;
            $r['in_proposals'] = 0; // in how many proposals
            $r['total_incomes'] = 0; // total incomes by this ware
            $r['total_count'] = 0; // total count of saled wares
            $result[$ware->id] = $r;
        }

        foreach ($proposals as $proposal) {
            if ($proposal->warranty_case != true) {
                foreach ($proposal->wares as $proposalWare) {
                    $result[$proposalWare->ware_id]['in_proposals']++;
                    $result[$proposalWare->ware_id]['total_incomes'] += $proposalWare->price_per_count * $proposalWare->count;
                    $result[$proposalWare->ware_id]['total_count'] += $proposalWare->count;
                }
            }
        }

        // 5. Вернуть массив
        $fileName = 'Товары ' . $periodBegin . ' - ' . $periodEnd;

        $file = Excel::create($fileName, function ($excel) use ($result) {
            $excel->setTitle('Отчет по товарам за период ');
            $excel->sheet('Лист 1', function ($sheet) use ($result) {
                $sheet->fromArray($result);
            });
        });

        $file = $file->string('csv');

        $response = [
            'name' => $fileName,
            'file' => $file,
        ];

        return response()->json($response);
    }

    /**
     * Get all sales data per period
     * @param  Request $request [description]
     * @return [type]           [description]
     * @api {GET} /api/reports/sales SalesReport
     * @apiDescription Get sales json
     * @apiGroup Reports
     * @apiVersion 0.0.1
     */
    public function getSalesData(Request $request) {
        // 1. Получаем все заявки
        // get all proposals in period
        $periodBegin = $request->period_begin;
        $periodEnd   = $request->period_end;

        if ($periodBegin === null) {
            $periodBegin = Carbon::now()->subMonths(1);
        }

        if ($periodEnd === null) {
            $periodEnd = Carbon::now();
        }

        // 2. Все заявки за период
        $proposals = Proposal::where(function ($query) use ($periodBegin, $periodEnd) {
            $query->where('created_at', '>', $periodBegin)
                ->where('status_id', '!=', 9)
                ->where('status_id', '>=', 3)
                ->where('warranty_case', 0)
                ->where('updated_at', '<', $periodEnd);
        })->get();

        foreach ($proposals as $proposal) {
            $proposal->wares = $proposal->wares;
        }

        // 2. Создаем массив с пользователями
        $users = User::all();
        $resultArray = [];

        foreach ($users as $user) {
            $u = [];
            $u['id'] = $user->id;
            $u['name'] = $user->name;
            $u['proposals'] = 0;
            $u['total_incomes'] = 0;
            $resultArray[$user->id] = $u;
        }

        // 3. идем по заявкам и суем их по продажам
        foreach ($proposals as $proposal) {
            if ($proposal->warranty_case != true) {
                $resultArray[$proposal->creator_id]['proposals']++;
                foreach ($proposal->wares as $proposalWare) {
                    $resultArray[$proposal->creator_id]['total_incomes'] += $proposalWare->price_per_count * $proposalWare->count;
                }
                
            }
        }
        // 4. возвращаем резултат
        $resultArray = array_filter($resultArray, function ($item) {
            if ($item['proposals'] > 0) {
                return $item;
            }
        });

        return array_values($resultArray);
    }

    public function getWareSales(Request $request) {
        $wareData = WareDataChange::where('ware_id', $request->ware_id)->orderBy('id', 'desc')->paginate(20);

        return response()->json($wareData, 200);
    }

    private function getWorkingCapital() {
        // 1. Берем все деньги
    }
}
