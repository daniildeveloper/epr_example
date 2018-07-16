<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Packaging;
use App\Models\RestFramework;
use App\Models\Sticker;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $is = Inventory::with('answered')->orderBy('id', 'desc')->paginate(40);

        $types = [
            [
                'slug' => 'Основы',
                'name' => 'rest_frameworks',
            ], [
                'slug' => 'Наклейки',
                'name' => 'stickers',
            ], [
                'slug' => 'Упаковки',
                'name' => 'packagings',
            ],
        ];

        $components = [
            'rest_frameworks' => RestFramework::all(),
            'packagings'      => Packaging::all(),
            'stickers'        => Sticker::all(),
        ];

        return response()->json([
            'inventories' => $is,
            'types'       => $types,
            'components'  => $components,
        ], 200);
    }
}
