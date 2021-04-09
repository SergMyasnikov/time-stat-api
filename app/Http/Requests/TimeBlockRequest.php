<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeBlockRequest extends FormRequest
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
        $req = $this->isMethod('post') ? 'required|' : '';
        return [
            'description' => $req . 'min:1|max:255',
            'block_length' => $req . 'integer|min:1|max:1440',
            'block_date' => $req . 'date',
            'category_id' => 'integer'
        ];
    }
    
    public function messages() 
    {
        return [
            'description.required' => "Не введено описание",
            'description.min' => "Слишком короткое описание",
            'description.max' => "Слишком длинное описание",
            'block_length.required' => "Не задана продолжительность",
            'block_length.integer' => "Некорректное значение продолжительности",
            'block_length.min' => "Минимальное значение для поля 'Продолжительность' равно 1",
            'block_length.max' => "Максимальное значение для поля 'Продолжительность' равно 1440",
            'block_date.required' => "Не задана дата",
            'block_date.date' => "Некорректное значение даты",
        ];
    }    
}
