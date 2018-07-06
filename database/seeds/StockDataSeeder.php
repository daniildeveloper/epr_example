<?php

use App\Models\Packaging;
use App\Models\RestFramework as RF;
use App\Models\Sticker;
use App\Models\Ware;
use Illuminate\Database\Seeder;

class StockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $packagingVedro                   = new Packaging();
        $packagingVedro->name             = 'Ведро';
        $packagingVedro->price            = 700;
        $packagingVedro->minimal_in_stock = 100;
        $packagingVedro->save();

        $packagingBootle                   = new Packaging();
        $packagingBootle->name             = "Бутыль";
        $packagingBootle->price            = 35;
        $packagingBootle->minimal_in_stock = 100;
        $packagingBootle->save();

        $packagingKanistra                   = new Packaging();
        $packagingKanistra->name             = 'Канистра';
        $packagingKanistra->price            = 200;
        $packagingKanistra->minimal_in_stock = 200;
        $packagingKanistra->save();

        $stickerTP                   = new Sticker();
        $stickerTP->name             = 'ОНК – ТП';
        $stickerTP->price            = 100;
        $stickerTP->minimal_in_stock = 100;
        $stickerTP->save();

        $stickerTC                   = new Sticker();
        $stickerTC->name             = 'ОНК - ТС';
        $stickerTC->price            = 100;
        $stickerTC->minimal_in_stock = 100;
        $stickerTC->save();

        $stickerTE                   = new Sticker();
        $stickerTE->name             = 'ОНК - ТЕ';
        $stickerTE->price            = 100;
        $stickerTE->minimal_in_stock = 100;
        $stickerTE->save();

        $stickerG1                   = new Sticker();
        $stickerG1->name             = 'ОНК – Г1';
        $stickerG1->price            = 100;
        $stickerG1->minimal_in_stock = 100;
        $stickerG1->save();

        $stickerG5                   = new Sticker();
        $stickerG5->name             = 'ОНК – Г5';
        $stickerG5->price            = 100;
        $stickerG5->minimal_in_stock = 100;
        $stickerG5->save();

        $stickerP1                   = new Sticker();
        $stickerP1->name             = 'ОНК – П1';
        $stickerP1->price            = 100;
        $stickerP1->minimal_in_stock = 100;
        $stickerP1->save();

        $stickerP5                   = new Sticker();
        $stickerP5->name             = 'ОНК – П5';
        $stickerP5->price            = 100;
        $stickerP5->minimal_in_stock = 100;
        $stickerP5->save();

        $stickerEL1                   = new Sticker();
        $stickerEL1->name             = 'ОНК – ЭЛ1';
        $stickerEL1->price            = 100;
        $stickerEL1->minimal_in_stock = 100;
        $stickerEL1->save();

        $stickerEL5                   = new Sticker();
        $stickerEL5->name             = 'ОНК – ЭЛ5';
        $stickerEL5->price            = 100;
        $stickerEL5->minimal_in_stock = 100;
        $stickerEL5->save();

        $stickerMP                   = new Sticker();
        $stickerMP->name             = 'ОНК - МП';
        $stickerMP->price            = 100;
        $stickerMP->minimal_in_stock = 100;
        $stickerMP->save();

        $stickerMC                   = new Sticker();
        $stickerMC->name             = 'ОНК - МС';
        $stickerMC->price            = 100;
        $stickerMC->minimal_in_stock = 100;
        $stickerMC->save();

        $stickerMZ                   = new Sticker();
        $stickerMZ->name             = 'ОНК - МЭ';
        $stickerMZ->price            = 100;
        $stickerMZ->minimal_in_stock = 100;
        $stickerMZ->save();

        $stickerKP                   = new Sticker();
        $stickerKP->name             = 'ОНК - КП';
        $stickerKP->price            = 100;
        $stickerKP->minimal_in_stock = 100;
        $stickerKP->save();

        $stickerKC                   = new Sticker();
        $stickerKC->name             = 'ОНК - КС';
        $stickerKC->price            = 100;
        $stickerKC->minimal_in_stock = 100;
        $stickerKC->save();

        $stickerGR                   = new Sticker();
        $stickerGR->name             = 'ОНК - ГР';
        $stickerGR->price            = 100;
        $stickerGR->minimal_in_stock = 100;
        $stickerGR->save();

        $gr        = new RF();
        $gr->name  = 'ГР';
        $gr->price = 14000;
        $gr->save();

        $kc        = new RF();
        $kc->name  = 'КС';
        $kc->price = 1370;
        $kc->save();

        $kp        = new RF();
        $kp->name  = 'КП';
        $kp->price = 5020;
        $kp->save();

        $me        = new RF();
        $me->name  = 'МЭ';
        $me->price = 1370;
        $me->save();

        $mc        = new RF();
        $mc->name  = 'МС';
        $mc->price = 1370;
        $mc->save();

        $mp        = new RF();
        $mp->name  = 'МП';
        $mp->price = 2020;
        $mp->save();

        $el1        = new RF();
        $el1->name  = 'ЭЛ1';
        $el1->price = 100;
        $el1->save();

        $p1        = new RF();
        $p1->name  = 'П1';
        $p1->price = 1150;
        $p1->save();

        $g1        = new RF();
        $g1->name  = 'Г1';
        $g1->price = 1050;
        $g1->save();

        $te        = new RF();
        $te->name  = 'ТЕ';
        $te->price = 1370;
        $te->save();

        $tsg        = new RF();
        $tsg->name  = 'ТЕ';
        $tsg->price = 1370;
        $tsg->save();

        $tss        = new RF();
        $tss->name  = 'ТСС';
        $tss->price = 1960;
        $tss->save();

        $tpg        = new RF();
        $tpg->name  = 'ТПЖ';
        $tpg->price = 2020;
        $tpg->save();

        $tps        = new RF();
        $tps->name  = 'ТПС';
        $tps->price = 2880;
        $tps->save();

        Ware::create(['name' => 'ТРАВЕРТИН ПРЕМИУМ СУХОЙ', 'framework_id' => $tps->id, 'sticker_id' => $stickerTP->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ТРАВЕРТИН ПРЕМИУМ ЖИДКИЙ', 'framework_id' => $tpg->id, 'sticker_id' => $stickerTP->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ТРАВЕРТИН СТАНДАРТ СУХОЙ', 'framework_id' => $tss->id, 'sticker_id' => $stickerTC->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ТРАВЕРТИН СТАНДАРТ ЖИДКИЙ', 'framework_id' => $tsg->id, 'sticker_id' => $stickerTC->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ТРАВЕРТИН ЭКОНОМ ЖИДКИЙ', 'framework_id' => $te->id, 'sticker_id' => $stickerTE->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ГРУНТОВКА', 'framework_id' => $g1->id, 'sticker_id' => $stickerG1->id, 'packaging_id' => $packagingBootle->id]);
        Ware::create(['name' => 'ПРОПИТКА', 'framework_id' => $p1->id, 'sticker_id' => $stickerP1->id, 'packaging_id' => $packagingBootle->id]);
        Ware::create(['name' => 'МОДИФИКАТОР БЕТОННЫХ РАСТВОРОВ', 'framework_id' => $el1->id, 'sticker_id' => $stickerEL1->id, 'packaging_id' => $packagingBootle->id]);
        Ware::create(['name' => 'МРАМОР ПРЕМИУМ ЖИДКИЙ', 'framework_id' => $mp->id, 'sticker_id' => $stickerMP->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'МРАМОР СТАНДАРТ ЖИДКИЙ', 'framework_id' => $mc->id, 'sticker_id' => $stickerMC->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'МРАМОР ЭКОНОМ ЖИДКИЙ', 'framework_id' => $me->id, 'sticker_id' => $stickerMZ->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'КИРПИЧ ПРЕМИУМ ЖИДКИЙ', 'framework_id' => $kp->id, 'sticker_id' => $stickerKP->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'КИРПИЧ СТАНДАРТ ЖИДКИЙ', 'framework_id' => $kc->id, 'sticker_id' => $stickerKC->id, 'packaging_id' => $packagingVedro->id]);
        Ware::create(['name' => 'ЖИДКИЙ ГРАНИТ', 'framework_id' => $gr->id, 'sticker_id' => $stickerGR->id, 'packaging_id' => $packagingVedro->id]);
    }
}
