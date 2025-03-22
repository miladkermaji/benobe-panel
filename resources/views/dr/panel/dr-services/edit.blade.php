@extends('dr.panel.layouts.master')

@section('styles')
 <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
 <style>
  .myPanelOption {
   display: none
  }
 </style>
@endsection

@section('site-header')
 {{ 'به نوبه | ویرایش خدمت ' }}
@endsection

@section('bread-crumb-title', ' ویرایش خدمات')

@section('content')
 <div class="container my-4">
  <div class="card shadow-sm">
   <div class="card-header w-100 d-flex justify-content-between">
    <div>
     <h4>ویرایش خدمت</h4>

    </div>
    <div>
     <a href="{{ route('dr-services.index') }}" class="btn btn-info text-white">بازگشت</a>
    </div>
   </div>
   <div class="card-body">
    <form action="{{ route('dr-services.update', $service->id) }}" method="POST">
     @csrf
     @method('PUT')
     <div class="position-relative">
      <input type="hidden" name="doctor_id" id="doctor_id" class="form-control h-50"
       value="{{ Auth::guard('doctor')->user()->id }}">
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="name">نام خدمت</label>
      <input type="text" name="name" id="name" class="form-control h-50" placeholder="نام خدمت"
       value="{{ old('name', $service->name) }}" required>
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="description">توضیحات</label>
      <textarea name="description" id="description" class="form-control h-50" rows="3" placeholder="توضیحات خدمت">{{ old('description', $service->description) }}</textarea>
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="duration">مدت زمان خدمت (دقیقه)</label>
      <input type="number" name="duration" id="duration" class="form-control h-50" placeholder="مثلاً 60"
       value="{{ old('duration', $service->duration) }}" required>
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="price">قیمت</label>
      <input type="number" min="0" max="90000000000" step="0.01" name="price" id="price"
       class="form-control h-50" placeholder="قیمت خدمت" value="{{ old('price', $service->price) }}" required>
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="discount">تخفیف اختیاری</label>
      <input type="number" step="0.01" name="discount" id="discount" class="form-control h-50"
       placeholder="تومان (در صورت وجود)" value="{{ old('discount', $service->discount) }}">
     </div>
     <div class="position-relative mb-5">
      <label class="label-top-input-special-takhasos" for="status">وضعیت</label>
      <select name="status" id="status" class="form-control h-50" required>
       <option value="active" {{ old('status', $service->status) == 1 ? 'selected' : '' }}>فعال</option>
       <option value="inactive" {{ old('status', $service->status) == 0 ? 'selected' : '' }}>غیرفعال
       </option>
      </select>
     </div>
     <div class="position-relative mb-2">
      <label class="label-top-input-special-takhasos" for="parent_id">زیرگروه (در صورت وجود)</label>
      <select name="parent_id" id="parent_id" class="form-control h-50">
       <option value="">-- انتخاب خدمت --</option>
       @foreach ($parentServices as $parentService)
        <option value="{{ $parentService->id }}"
         {{ old('parent_id', $service->parent_id) == $parentService->id ? 'selected' : '' }}>
         {{ $parentService->name }}
        </option>
       @endforeach
      </select>
     </div>
     <button type="submit" class="btn btn-primary w-100 mt-2 h-50">ویرایش خدمت</button>
    </form>
   </div>
  </div>
 </div>
@endsection

@section('scripts')
 <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
 <script></script>
@endsection
