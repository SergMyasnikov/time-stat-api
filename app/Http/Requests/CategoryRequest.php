<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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

    public function rules()
    {
        $req = $this->isMethod('post') ? 'required|' : '';
        return [
            'name' => $req . 'min:2|max:50',
            'target_percentage' => $req . 'integer|min:0|max:100',
        ];
    }    
    
    public function messages() 
    {
        return [
            'name.required' => "Не введено название",
            'name.min' => "Слишком короткое название",
            'name.max' => "Слишком длинное название",
            'target_percentage.required' => "Не выбран целевой %",
            'target_percentage.min' => "Минимальное значение для поля 'Целевой %' равно 0",
            'target_percentage.max' => "Максимальное значение для поля 'Целевой %' равно 100",
        ];
    }    
}
