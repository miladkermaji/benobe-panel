<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
    public function rules()
    {
        return [
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'national_code'  => 'sometimes|string|max:10',
            'license_number' => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'province_id'    => 'nullable|exists:zone,id,level,1', // فقط استان‌ها (level=1)
            'city_id'        => 'nullable|exists:zone,id,level,2', // فقط شهرها (level=2)
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'first_name.string' => 'نام باید یک رشته متنی باشد.',
            'first_name.max' => 'نام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'last_name.string' => 'نام خانوادگی باید یک رشته متنی باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'national_code.string' => 'کد ملی باید یک رشته متنی باشد.',
            'national_code.max' => 'کد ملی نمی‌تواند بیش از ۱۰ کاراکتر باشد.',
            'license_number.string' => 'شماره نظام پزشکی باید یک رشته متنی باشد.',
            'license_number.max' => 'شماره نظام پزشکی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'description.string' => 'توضیحات باید یک رشته متنی باشد.',
            'province_id.exists' => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.exists' => 'شهر انتخاب‌شده معتبر نیست.',
        ];
    }
}
