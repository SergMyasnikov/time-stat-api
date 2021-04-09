<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;

class CategoryService {
    public static function deleteCategory(Category $model)
    {
        if (count($model->timeBlocks) > 0) {
            throw new \App\Exceptions\RemovingCategoryHasChildTimeBlocksException();
        }
        $model->delete();
    }
    
    /*
     * Для того, чтобы статистика формировалась правильно, сумма значений
     * целевого процента по всем категориям пользователя должна равняться 100.
     * Данная функция проверяет это условие и возвращает true, если оно выполняется,
     * false в обратном случае
     *      */

    public static function checkCategorySum($userId)
    {
        $categorySum = Category::forUser($userId)->sum('target_percentage');
        return ($categorySum == 100);
    }
    
    public static function checkCategoryExists($userId)
    {
        $categoryCount = Category::forUser($userId)->count();
        return ($categoryCount > 0);
    }
    
    public static function checkCategoryNameExists($userId, $categoryName)
    {
        return Category::forUser($userId)
                ->where('name', '=', $categoryName)->exists();
    }
   
}
