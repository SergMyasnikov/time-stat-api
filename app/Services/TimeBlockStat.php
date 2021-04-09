<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Category;
use App\Models\TimeBlock;

class TimeBlockStat {
    public static function getStat($userId) {
        $userModel = User::find($userId);
        if (!$userModel) {
            throw new \Exception('User not found');
        }
        $dates = self::calcDates($userModel);
        
        $objs = DB::table('categories')
                ->leftJoin('time_blocks', function($join) use ($dates) {
                    $join->on('categories.id', '=', 'time_blocks.category_id')
                            ->where('time_blocks.block_date', '>=', $dates['start']);
                })
                ->groupBy('categories.name', 'categories.target_percentage')
                ->where('categories.target_percentage', '>', '0')        
                ->where('categories.user_id', '=', $userModel->id)        
                ->select('categories.name', 'categories.target_percentage', 
                        DB::raw('SUM(time_blocks.block_length) as category_sum'))
                ->get();

        $timeSum = 0;
        foreach ($objs as $obj) {
                $timeSum += $obj->category_sum;
        }
        
        $rows = [];
        if ($timeSum > 0) {
            foreach ($objs as $obj) {
                    $row = [];
                    $categorySum = $obj->category_sum ?? 0;
                    $row['category_name'] = $obj->name;
                    $row['category_sum'] = $categorySum;
                    $row['target'] = $obj->target_percentage;
                    $row['fact'] = round(100 * $categorySum / $timeSum);
                    $row['congruence'] = round(
                            10000 * $categorySum / 
                            ($timeSum * $obj->target_percentage));
                    $row['delta'] = round(
                            ($row['fact'] - $row['target']) * $timeSum / 100);
                    $rows []= $row;
            }        
            usort($rows, function ($row1, $row2) {
                return $row2['congruence'] <=> $row1['congruence'];
            });
        }
        
        return [
            'dates' => $dates,
            'rows' => $rows];        
        
    }
    
    private static function calcDates(User $userModel) 
    {
        $dates = [
                'start' => $userModel->stat_period_start_date,
                'end' => date("Y-m-d")
                ];
        
        if (is_null($dates['start'])) {
            $dates['start'] = date(
                    "Y-m-d", 
                    strtotime('-' . $userModel->stat_period_length . ' months'));
        }
        return $dates;
    }
}
