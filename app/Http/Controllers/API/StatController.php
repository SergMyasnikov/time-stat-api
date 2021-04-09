<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\TimeBlockStat;

/**
 * Description of StatController
 *
 * @author user
 */
class StatController extends Controller {
    public function index()
    {
        $message = 'OK';
        if (!CategoryService::checkCategorySum(Auth::id())) {
            $message = 'Внимание! Сумма значений целевого процента по всем категориям пользователя не равняется 100!';
        }
        return response([ 
            'stat' => TimeBlockStat::getStat(Auth::id()),
            'message' => $message], 200);
    }
}
