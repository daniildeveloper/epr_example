<?php

namespace App\ParsingHelpers;

use App\Models\MoneyTransaction;
use App\Models\Packaging;
use App\Models\Proposal;
use App\Models\ProposalNotAllowedArgument as Argument;
use App\Models\Purse;
use App\Models\RestFramework;
use App\Models\Sticker;
use App\Models\Ware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProposalHelper
{
    /**
     * Store proposal data
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function store($data)
    {
        $proposal_id = DB::table('proposals')->insertGetId([
            [
                'created_at'       => $data['created_at'],
                'updated_at'       => $data['created_at'],
                'client'           => $data['client'],
                'client_phone'     => $data['phone'],
                'object'           => $data['object'],
                'is_with_docs'     => $data['is_with_docs'],
                'tax'              => $data['tax'],
                'client_deadline'  => $data['created_at'],
                'workers_deadline' => $data['created_at'],
                'partner_payment'  => $data['partner_payment'],
                'partner_notes'    => $data['partner_notes'],
            ],
        ]);

        foreach ($data['wares'] as $ware) {
            DB::table('proposal_wares')->insert([
                'proposal_id'     => $proposal_id,
                'ware_id'         => $ware['id'],
                'price_per_count' => $ware['price_per_count'],
                'count'           => $ware['count'],
                'color'           => $ware['color'],
                'color_price'     => $wre['color_price'],
                'created_at'      => $data['created_at'],
                'updated_at'      => $data['created_at'],
            ]);
        }

        return $proposal_id;
    }

    /**
     * @param $proposalID
     */
    public static function allowProposal($proposalID)
    {
        // find proposal by id
        $proposal = Proposal::findOrFail($proposalID);

        // get proposal wares
        $proposal->wares = $proposal->wares;

        $proposalWaresPrice = 0; // total incomes from wares
        // set tax and partners amount
        $waresSelfCost      = 0; // wares self cost
        $partnerAmount      = $proposal->partner_payment ? (int) $proposal->partner_payment : 0; // partner payment
        $workersColorProfit = 0;

        // send to purses to
        foreach ($proposal->wares as $proposal_ware) {
            $proposalWareSelfCost = 0;
            // calculte total incomes for this wares
            $ware = Ware::find($proposal_ware->id);

            $ware->packaging = $ware->packaging;
            $ware->framework = $ware->framework;
            $ware->sticker   = $ware->sticker;

            $chemieCost = $ware->framework->price * $proposal_ware->count;
            // send money to chemie price
            $chemieCostTransaction              = new MoneyTransaction();
            $chemieCostTransaction->purse_to_id = Purse::where('slug', 'sale_oborot')->first()->id;
            $chemieCostTransaction->sum         = $chemieCost;
            $chemieCostTransaction->proposal_id = $proposalID;
            $chemieCostTransaction->argument    = "Возвращаем в оборот деньги за химию " . RestFramework::find($ware->framework_id)->name;
            $chemieCostTransaction->save();

            // packaging cost transaction
            $packagingCost                         = $ware->packaging->price * $proposal_ware->count;
            $packagingCostTransaction              = new MoneyTransaction();
            $packagingCostTransaction->purse_to_id = Purse::where('slug', 'sale_packagings')->first()->id;
            $packagingCostTransaction->sum         = $packagingCost;
            $packagingCostTransaction->proposal_id = $proposalID;
            $packagingCostTransaction->argument    = 'Стоимость упаковки ' . $ware->packaging->name;
            $packagingCostTransaction->save();

            // sticker cost
            $stickerCost                         = $ware->sticker->price * $proposal_ware->count;
            $stickerCostTransaction              = new MoneyTransaction();
            $stickerCostTransaction->purse_to_id = Purse::where('slug', 'sale_packagings')->first()->id;
            $stickerCostTransaction->sum         = $stickerCost;
            $stickerCostTransaction->proposal_id = $proposalID;
            $stickerCostTransaction->argument    = 'Стоимость наклейки ' . $ware->sticker->name;
            $stickerCostTransaction->save();

            $proposalWareSelfCost += $packagingCost + $stickerCost + $chemieCost;
            $proposalWaresPrice += $proposal_ware->price_per_count * $proposal_ware->count;
            $waresSelfCost += $proposalWareSelfCost;

            if ($proposal_ware->color != null) {
                // total color incomes
                $colorTotal = $proposal_ware->color_price * $proposal_ware->count;

                // increase workers color profit
                $workersColorProfit += $colorTotal;

                // send to workers purse
                $colorProfitTransaction              = new MoneyTransaction();
                $colorProfitTransaction->purse_to_id = Purse::where('slug', 'workers_purse')->first()->id;
                $colorProfitTransaction->sum         = $colorTotal;
                $colorProfitTransaction->proposal_id = $proposalID;
                $colorProfitTransaction->argument    = "Отчисление за прибыль по цвету $proposal_ware->color";
                $colorProfitTransaction->save();
            }
        }

        if ($proposal->warranty_case != 1) {

            // define tax
            $tax = 0;

            if ($proposal->is_with_docs === 1) {
                $tax = $this->proposalTaxManipulate($proposal->tax, $proposalWaresPrice, $proposal->id, $proposal->code);
            }

            $this->partnersMoneyTransaction($partnerAmount, $proposal->id, $proposal->partner_notes);

            // send to total incomes purse
            $totalTransaction              = new MoneyTransaction();
            $totalTransaction->purse_to_id = Purse::where('slug', 'salers_money')->first()->id;
            $totalTransaction->sum         = $proposalWaresPrice + (($proposalWaresPrice * $proposal->tax) / 100) + $workersColorProfit;
            $totalTransaction->proposal_id = $proposal->id;
            $totalTransaction->argument    = "Общий доход за заявку $proposal->code";
            $totalTransaction->save();

            // calculate profit
            $profitTotal = $proposalWaresPrice - $waresSelfCost - $partnerAmount;
            Log::info('Profit from ' . $proposal->code . ' ' . $profitTotal);
            $this->profitCoordinate($profitTotal, $proposal->id, $proposal->tax_type);
        }
    }

    public static function parse_ware_name($name)
    {
        $id = 1;
        switch ($name) {
            case 'ДЗ':
                break;
            case 'СТС':
                break;
            case 'ПР':
                break;
            case 'СТЖ':
                break;
            case 'мрамор':

            default:
                // code...
                break;
        }
    }
}
