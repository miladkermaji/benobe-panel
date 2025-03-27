<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorSpecialtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'academic_degree_id' => 'required|exists:academic_degrees,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'specialty_title'    => 'required|string',
            'degrees.*'          => 'sometimes|exists:academic_degrees,id',
            'specialties.*'      => 'sometimes|exists:specialties,id',
            'titles.*'           => 'sometimes|string'
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_degree_id.required' => 'انتخاب مدرک تحصیلی الزامی است.',
            'academic_degree_id.exists'   => 'مدرک تحصیلی انتخاب‌شده معتبر نیست.',
            'specialty_id.required'       => 'انتخاب تخصص الزامی است.',
            'specialty_id.exists'         => 'تخصص انتخاب‌شده معتبر نیست.',
            'specialty_title.required'    => 'عنوان تخصص الزامی است.',
            'specialty_title.string'      => 'عنوان تخصص باید یک رشته باشد.',
            'degrees.*.exists'            => 'یکی از مدارک تحصیلی انتخاب‌شده معتبر نیست.',
            'specialties.*.exists'        => 'یکی از تخصص‌های انتخاب‌شده معتبر نیست.',
            'titles.*.string'             => 'هر عنوان باید یک رشته باشد.',
        ];
    }
}
