<?php

namespace App\Http\Controllers\Api\Owner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RestsController extends Controller
{
    public function getWorkersMoney(Request $request) {
      // 1. Получение остатков на начало периода
      // 2. Сумма всех транзакций
      // 3. - Сумма всех закупок
      // 4. + Сумма всех доходов
      // 5. Вывод результата
    }
}
