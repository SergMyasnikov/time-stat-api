<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stat_period_length' => 'required|integer|min:1|:max:960',
            'stat_period_start_date' => 'nullable|date',
        ];
    }
    
    public function messages() 
    {
        return [
            'stat_period_length.required' => "Не задана длина периода",
            'stat_period_length.integer' => "Некорректное значение длины периода",
            'stat_period_length.min' => "Минимальная продолжительность равна 1",
            'stat_period_length.max' => "Максимальная продолжительность равна 960",
            'stat_period_start_date.required' => "Не задана начальная дата",
            'stat_period_start_date.date' => "Некорректное значение начальной даты",
        ];
    }    
}
