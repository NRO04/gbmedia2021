<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingRequest extends FormRequest
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
        $rules = [
            'title' => 'required|string|max:100',
            'description' => 'required',
            'cover' => 'required|mimes:jpeg,png,gif,jpg|max:2048',
            'role_ids' => 'required'
        ];

        foreach ($this->request->input("questions") as $key => $val){
            $rules['questions.'.$key.'.question'] = 'sometimes|required|max:100';
            $rules['questions.'.$key.'.options'] = 'sometimes|required|array|min:4';
            $rules['questions.'.$key.'.correctAnswer'] = 'sometimes|required|numeric';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'title.required' => 'Por favor, escriba un titulo',
            'description.required' => 'Por favor, escriba un contenido',
            'role_ids.required' => 'Por favor, escoja los roles que veran este capacitacion',
            'cover.required' => 'Por favor, cargue un cover',
            'cover.mimes' => 'Por favor, cargue una imagen con formato .jpg, .png, .gif, .svg',
        ];

        foreach($this->request->input('questions') as $key => $val){
            $messages['questions.'.$key.'.required'] = 'El campo "questions'.$key.'.question" es obligatorio';
            $messages['questions.'.$key.'.required'] = 'El campo "questions'.$key.'.options" es obligatorio';
            $messages['questions.'.$key.'.required'] = 'El campo "questions'.$key.'.correctAnswer" es obligatorio';
        }

        return $messages;
    }
}
