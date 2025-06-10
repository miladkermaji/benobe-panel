@extends('admin.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('admin-assets/panel/css/users/users.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | پنل مدیریت' }}
@endsection

@section('content')
@section('bread-crumb-title', 'ویرایش پزشک')
@livewire('admin.panel.doctors.doctor-edit', ['id' => $id])

<div class="form-group">
  <label for="province_id">استان <span class="text-danger">*</span></label>
  <select wire:model.live="province_id" class="form-control" id="province_id">
    <option value="">انتخاب کنید</option>
    @foreach ($provinces as $province)
      <option value="{{ $province->id }}">{{ $province->name }}</option>
    @endforeach
  </select>
  @error('province_id')
    <span class="text-danger">{{ $message }}</span>
  @enderror
</div>

<div class="form-group">
  <label for="city_id">شهر <span class="text-danger">*</span></label>
  <select wire:model="city_id" class="form-control" id="city_id">
    <option value="">انتخاب کنید</option>
    @foreach ($cities as $city)
      <option value="{{ $city->id }}">{{ $city->name }}</option>
    @endforeach
  </select>
  @error('city_id')
    <span class="text-danger">{{ $message }}</span>
  @enderror
</div>
@endsection
