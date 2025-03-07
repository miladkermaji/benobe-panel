<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileRequest extends FormRequest
{
public function authorize(): bool
{
return true;
}

public function rules(): array
{
return [
'mobile' => [
'required',
'string',
'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/'
]
];
}

// اضافه کردن پیام‌های خطای سفارشی
public function messages(): array
{
return [
'mobile.required' => 'شماره موبایل الزامی است',
'mobile.string' => 'شماره موبایل باید رشته باشد',
'mobile.regex' => 'فرمت شماره موبایل معتبر نیست'
];
}
}