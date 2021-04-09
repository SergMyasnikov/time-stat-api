<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;

use App\Models\User;

class SettingsController extends Controller {
    public function index()
    {
        $user = User::find(Auth::id());
        
        return response([
            'settings' => [
                'stat_period_length' => $user->stat_period_length,
                'stat_period_start_date' => $user->stat_period_start_date                
            ],
            'message' => 'Retrieved successfully'], 200);
    } 
    
    public function update(SettingsRequest $request) {
        $user = User::find(Auth::id());
        $user->update($request->only('stat_period_length', 'stat_period_start_date'));
        return response([
            'settings' => [
                'stat_period_length' => $user->stat_period_length,
                'stat_period_start_date' => $user->stat_period_start_date                
            ],
            'message' => 'Update successfully'], 200);
    }
}
