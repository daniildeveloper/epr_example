<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\MoneyTransactions;
use App\Models\Packaging;
use App\Models\PackagingBy;
use App\Models\PackagingManipulation;
use App\Models\Purse;
use App\Models\Sticker;
use App\Models\StickerBy;
use App\Models\StickerManipulation;
use Illuminate\Http\Request;
use JWTAuth;
use Log;

class StickersAndPackagingsManipulationController extends Controller
{
    /**
     * @param Request $request
     * @api {POST} /api/crud-nomenclatures/manipulations/sticker Store sticker manipulation
     * @apiVersion 0.0.1
     * @apiDescription Store sticekr manipulation
     * @apiGroup Stock
     */
    public function storeSticker(Request $request)
    {
        $user = JWTAuth::toUser($request->header('Authorization'));
        // return response()->json($request)
        Log::info($request);

        $sticker = Sticker::find($request->sticker_id);
        $sum     = $sticker->price * (int) $request->count;

        // если пополняем запасы - списываем деньги на покупку химии, то переводим деньги с покупки химии
        Log::info('Сумма для покупки наклейки ' . $sum);

        // Кошелек для снятия денег
        $purseFromID = Purse::where('slug', 'sale_packagings')->first()->id;

        // calculate rests for this purse to see how much i can translate
        if (Purse::restCalculate($purseFromID) < $sum) {
            return response()->json(['message' => 'Недостаточно средств для покупки'], 402);
        }

        $transaction                = new MoneyTransactions();
        $transaction->purse_from_id = $purseFromID;
        $transaction->sum           = $sum;
        $transaction->argument      = "Перевод денег на покупку химии $sticker->name";
        $transaction->save();

        $stickerBy             = new StickerBy();
        $stickerBy->sticker_id = $request->sticker_id;
        $stickerBy->total      = $request->count;
        $stickerBy->save();

        return response()->json($stickerBy, 200);
    }

    /**
     * @param Request $request
     */
    public function storePackaging(Request $request)
    {
        $user = JWTAuth::toUser($request->header('Authorization'));

        $packaging = Packaging::find($request->packaging_id);
        Log::info($packaging->name);
        $sum = $packaging->price * (int) $request->count;

        Log::info('Сумма для покупки упаковки ' . $sum);

        // Кошелек для снятия денег
        $purseFromID = Purse::where('slug', 'sale_packagings')->first()->id;

        // calculate rests for this purse to see how much i can translate
        if (Purse::restCalculate($purseFromID) < $sum) {
            return response()->json(['message' => 'Недостаточно средств для покупки'], 402);
        }

        $transaction                = new MoneyTransactions();
        $transaction->purse_from_id = $purseFromID;
        $transaction->sum           = $sum;
        $transaction->argument      = "Перевод денег на покупку упаковки $packaging->name";
        $transaction->save();

        $packagingBy               = new PackagingBy();
        $packagingBy->packaging_id = $packaging->id;
        $packagingBy->total        = (int) $request->count;
        $packagingBy->save();

        return response()->json($packagingBy, 200);
    }

    /**
     * @param Request $request
     */
    public function confirmStickersSupply(Request $request)
    {
        $stickerBy         = StickerBy::findOrFail($request->by_id);
        $stickerBy->closed = true;
        $stickerBy->save();

        $manipulation             = new StickerManipulation();
        $manipulation->sticker_id = $stickerBy->sticker_id;
        $manipulation->count      = $stickerBy->total;
        $manipulation->refill     = true;
        $manipulation->save();

        return response()->json($stickerBy, 200);
    }

    /**
     * @param Request $reqeust
     */
    public function confirmPackagingsSupply(Request $request)
    {
        $packagingBy         = PackagingBy::findOrFail($request->by_id);
        $packagingBy->closed = true;
        $packagingBy->save();

        $manipulation               = new PackagingManipulation();
        $manipulation->packaging_id = $packagingBy->packaging_id;
        $manipulation->count        = $packagingBy->total;
        $manipulation->refill       = true;
        $manipulation->save();

        return response()->json($packagingBy, 200);
    }

    /**
     * @param Request $request
     */
    public function declineStickersSupply(Request $request)
    {
        $stickerBy         = StickerBy::findOrFail($request->by_id);
        $stickerBy->closed = true;
        $sticekrBy->save();

        $user = JWTAuth::toUser($request->header('Authorization'));

        $stickerBy->sticker = $stickerBy->sticker;
        $sum                = $stickerBy->sticker->price * (int) $stickerBy->total;
        // Кошелек для снятия денег
        $purseToID = Purse::where('slug', 'sale_packagings')->first()->id;

        $transaction              = new MoneyTransactions();
        $transaction->purse_to_id = $purseToID;
        $transaction->sum         = $sum;
        $transaction->argument    = "Возвращаем деньги после неудачной поставки " . $stickerBy->sticker->name . "х $stickerBy->total";
        $transaction->save();
    }

    /**
     * @param Request $request
     */
    public function declinePackagingsSupply(Request $request)
    {
        $packagingBy         = PackagingBy::findOrFail($request->by_id);
        $packagingBy->closed = true;
        $packagingBy->save();

        $packagingBy->packaging = $packagingBy->packaging;
        $sum                    = $packagingBy->packaging->price * $packagingBy->total;

        // purse to id
        $purseToID = Purse::where('slug', 'sale_packagings')->first()->id;

        $transaction              = new MoneyTransactions();
        $transaction->purse_to_id = $purseToID;
        $transaction->sum         = $sum;
        $transaction->argument    = "Возвращаем деньги после неудачной поставки " . $packagingBy->packaging->name . " х $packagingBy->total";
        $transaction->save();
    }

    /**
     * @param Request $request
     */
    public function suppliesPlan(Request $request)
    {
        $packagingBies = PackagingBy::where('closed', 0)->get();

        $stickersBies = StickerBy::where('closed', 0)->get();

        foreach ($stickersBies as $s) {
            $s->sticker = $s->sticker;
        }

        foreach ($packagingBies as $p) {
            $p->packaging = $p->packaging;
        }

        return response()->json([
            'packagings' => $packagingBies,
            'stickers'   => $stickersBies,
        ], 200);
    }
}
