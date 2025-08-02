@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />


  <style>
    .myPanelOption {
      display: none;
    }
  </style>
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')

@section('bread-crumb-title', ' ویرایش پروفایل ')
<div class="main-content mb-5 mt-3">
  @if (!$doctor->profile_completed && count($incompleteSections) > 0)
    <div class="alert alert-warning text-center">
      <span class="fw-bold">
        پروفایل شما کامل نیست. لطفا بخش‌های زیر را تکمیل کنید:
        <ul class="w-100 d-flex gap-4">
          @foreach ($doctor->getIncompleteProfileSections() as $section)
            <li class="badge badge-danger p-2">{{ $section }}</li>
          @endforeach
        </ul>
      </span>
    </div>
  @endif
  <div class="d-flex justify-content-center align-items-center flex-column col-12">
    <div class="top-profile-info p-2 col-xs-12 col-sm-12  col-md-12 col-lg-8 d-flex">
      <div class="d-flex justify-content-between w-100 font-size-13">
        <div class="d-flex align-items-start">
          <div>
            <img src="{{ asset('mc-assets/panel/img/pro.jpg') }}" class="avatar___img-main">
          </div>
          <div class="mx-2 mt-3">
            <span class="d-block fw-bold font-size-15 profile-header-name text-dark">
              @if (Auth::guard('doctor')->check())
                {{ $academicDegreeTitle ? $academicDegreeTitle . ' ' : '' }}{{ Auth::guard('doctor')->user()->first_name . ' ' . Auth::guard('doctor')->user()->last_name }}
              @elseif(Auth::guard('secretary')->check())
                {{ $academicDegreeTitle ? $academicDegreeTitle . ' ' : '' }}{{ Auth::guard('secretary')->user()->doctor->first_name . ' ' . Auth::guard('secretary')->user()->doctor->last_name }}
              @endif
            </span>
            <span class="badge badge-light p-2 border-radius-8 mt-3 mx-3 font-size-13 cursor-pointer">
              {{ $firstSpecialtyName ?: $specialtyName }}
            </span>
          </div>
        </div>
        <div class="show-profile-badge">
          <a href="" class="d-flex align-items-center">
            <img src="{{ asset('mc-assets/icons/eye.svg') }}" alt="" srcset="">
            <span class="mx-1">مشاهده پروفایل</span>
          </a>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8" id="personal-data"
      style="overflow:hidden; position:relative;">
      <div class="d-flex justify-content-between align-items-center personal-data-clicked">
        <div>

          <img src="{{ asset('mc-assets/icons/user.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1">اطلاعات فردی</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles personal-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div class="">
          <form action="{{ route('mc-update-profile') }}" method="POST" id="profileEdit">
            @csrf
            <div class="mt-2">
              <label for="name" class="label-top-input">نام</label>
              <input type="text" class="my-form-control w-100 border-radius-6 mt-3"
                value="@if (Auth::guard('doctor')->check()) {{ Auth::guard('doctor')->user()->first_name }}@elseif(Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->first_name }} @endif"
                name="first_name" readonly>
              @error('first_name')
                <span class="text-danger fw-bold mt-2">{{ $message }}</span>
              @enderror
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">نام خانوادگی</label>
              <input type="text" class="my-form-control w-100 border-radius-6 mt-3"
                value="@if (Auth::guard('doctor')->check()) {{ Auth::guard('doctor')->user()->last_name }}@elseif(Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->last_name }} @endif"
                name="last_name" readonly>
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">کدملی</label>
              <input type="text"
                value="@if (Auth::guard('doctor')->check()) {{ Auth::guard('doctor')->user()->national_code ?? '' }}@elseif(Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->national_code ?? '' }} @endif"
                class="my-form-control-light h-50 w-100 border-radius-6 mt-3 text-right" name="national_code">
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">شماره نظام پزشکی</label>
              <input type="text"
                value="@if (Auth::guard('doctor')->check()) {{ Auth::guard('doctor')->user()->license_number ?? '' }}@elseif(Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->license_number ?? '' }} @endif"
                class="my-form-control-light h-50 w-100 border-radius-6 mt-3 text-right" name="license_number">
            </div>
            <div class="d-flex justify-content-between mt-4 gap-4 position-relative">
              <label for="name" class="label-top-input-special-takhasos">شماره موبایل</label>
              <input class="my-form-control h-50 col-lg-11 col-xs-10 col-md-11 col-sm-11 text-right disabled "
                placeholder="شماره موبایل" name="mobile" value="{{ Auth::guard('doctor')->user()->mobile ?? '' }}"
                disabled>
              <button
                class="btn btn-dark h-50 col-lg-1 col-xs-2 col-md-1 col-sm-1 d-flex justify-content-center align-items-center fs-6 add-form-item"
                type="button" id="editButton" data-bs-toggle="modal" data-bs-target="#mobileEditModal">

                <img src="{{ asset('mc-assets/icons/pencil-edit.svg') }}" alt="" srcset="">

              </button>
            </div>
            <div class="mt-4 position-relative">
              <label for="province_id" class="label-top-input-city">استان</label>
              <select name="province_id" id="province_id"
                class="form-control h-50 w-100 border-radius-6 mt-3 tom-select" autocomplete="off">
                <option value="">انتخاب استان</option>
                @foreach (\App\Models\Zone::provinces()->get() as $province)
                  <option value="{{ $province->id }}"
                    @if (Auth::guard('doctor')->check()) {{ Auth::guard('doctor')->user()->province_id == $province->id ? 'selected' : '' }}
                    @elseif(Auth::guard('secretary')->check())
                      {{ Auth::guard('secretary')->user()->doctor->province_id == $province->id ? 'selected' : '' }} @endif>
                    {{ $province->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="mt-4 position-relative">
              <label for="city_id" class="label-top-input-city">شهر</label>
              <select name="city_id" id="city_id" class="form-control h-50 w-100 border-radius-6 mt-3 tom-select"
                autocomplete="off">
                <option value="">ابتدا یک استان انتخاب کنید</option>
                @if ($doctor->city_id)
                  @php
                    $city = \App\Models\Zone::find($doctor->city_id);
                  @endphp
                  <option value="{{ $city->id }}" selected>{{ $city->name }}</option>
                @endif
              </select>
            </div>
            <div class="mt-4">
              <label for="name" class="fw-bold font-size-13"> بیوگرافی و توضیحات</label>
              <textarea class="ckeditor form-control" name="description" class="form-control" id="description">
                {{ trim($doctor->bio ?? '') }}
              </textarea>
            </div>
            <div class="w-100">
              <button type="submit"
                class="w-100 btn my-btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
                <span class="button_text">ذخیره تغیرات</span>
                <div class="loader"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    {{-- mobileedit modal --}}
    <div class="modal fade" id="mobileEditModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-radius-6">
          <div class="modal-header">
            <h5 class="modal-title">ویرایش شماره موبایل</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body position-relative">
            <div id="mobileInputStep1">
              <label class="label-top-input-modal">شماره موبایل جدید</label>
              <input type="text" id="newMobileNumber" maxlength="11" class="form-control w-100 h-50 text-right"
                name="mobile">
              <div class="d-flex mt-2">
                <button onclick="sendOtpCode()"
                  class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                  <span class="button_text">ارسال کد تایید</span>
                  <div class="loader"></div>
                </button>
              </div>
            </div>
            <div id="otpInputStep" style="display:none;">
              <label class="label-top fw-bold">کد تایید 4 رقمی را وارد کنید</label>
              <div class="d-flex justify-content-center gap-10 mt-2" dir="ltr">
                <input type="text" maxlength="1" inputmode="numeric" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" inputmode="numeric" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" inputmode="numeric" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" inputmode="numeric" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
              </div>
              <div class="d-flex mt-3">
                <button onclick="verifyOtpCode()"
                  class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                  <span class="button_text">تایید کد</span>
                  <div class="loader"></div>
                </button>
              </div>
              <div class="text-center mt-2">
                <small id="resendOtpTimer"></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- mobileedit modal --}}
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8" id="specialty-section"
      style="position:relative; overflow:visible;">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('mc-assets/icons/medal.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1"> تخصص</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles takasos-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="text-left mt-3 remove-form-item">
            <img src="{{ asset('mc-assets/icons/times.svg') }}" alt="" srcset="">

          </div>
          <div class="mt-2">
            <form action="{{ route('mc-specialty-update') }}" method="POST" id="specialtyEdit">
              @csrf
              <div class="d-flex justify-content-between gap-4 flex-xs-wrap flex-xs-column">
                <div class="mt-2 w-100">
                  <label for="name" class="label-top-input">درجه علمی</label>
                  <select name="academic_degree_id" id="academic_degree_id"
                    class="form-control h-50  border-radius-6 mt-3 col-12 position-relative daraje">
                    @foreach ($academic_degrees as $academic_degree)
                      <option value="{{ $academic_degree->id }}"
                        {{ (Auth::guard('doctor')->user()->academic_degree_id ?? '') == ($academic_degree->id ?? '') ? 'selected' : '' }}>
                        {{ $academic_degree->title ?? '' }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="mt-2 w-100">
                  <label for="name" class="label-top-input">تخصص</label>
                  <select name="specialty_id" id="specialties_list"
                    class="form-control h-50 border-radius-6 mt-3 col-12 position-relative takhasos-input">
                    @foreach ($specialties as $specialtyOption)
                      <option value="{{ $specialtyOption->id }}"
                        {{ $specialtyOption->id == ($currentSpecialty->specialty_id ?? '') ? 'selected' : '' }}>
                        {{ $specialtyOption->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="position-relative">
                <label for="name" class="label-top-input-special-takhasos">عنوان تخصص</label>
                <input type="text" value="{{ $specialtyName }}" name="specialty_title"
                  class="form-control h-50 w-100 border-radius-6 mt-3  ">
              </div>
              <div id="additionalInputs">
                <h6 class="fw-bold mt-3">تخصص های اضافی</h6>
                <div class="alert alert-warning mt-2">
                  تخصص های اضافه شده شما قابل ویرایش نیستند اگه قصد تغییر یا ویرایش تخصص را دارید ابتدا آن را پاک کنید و
                  مجدد
                  تخصص جدید بسازید
                </div>
                <!-- تخصص‌های اضافه شده از دیتابیس -->
                @foreach ($doctor_specialties as $index => $specialty)
                  @if ($index > 0)
                    <div class="w-100 mt-3 specialty-item" data-specialty-id="{{ $specialty->id }}">
                      <div class="text-left mt-3 remove-form-item" onclick="removeInput(this)">

                        <img src="{{ asset('mc-assets/icons/times.svg') }}" alt="" srcset="">

                      </div>
                      <div>
                        <div class="mt-2">
                          <div class="d-flex justify-content-between gap-4">
                            <div class="w-100">
                              <label for="degree{{ $index + 1 }}" class="label-top-input">درجه علمی</label>
                              <select name="degrees[{{ $index }}]" id="degree{{ $index + 1 }}"
                                class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje"
                                disabled>
                                @foreach ($academic_degrees as $academic_degree)
                                  <option value="{{ $academic_degree->id }}"
                                    {{ $specialty->academic_degree_id == $academic_degree->id ? 'selected' : '' }}>
                                    {{ $academic_degree->title }}
                                  </option>
                                @endforeach
                              </select>
                            </div>
                            <div class="w-100">
                              <label for="specialty{{ $index + 1 }}" class="label-top-input">تخصص</label>
                              <select name="specialties[{{ $index }}]" id="specialty{{ $index + 1 }}"
                                class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative takhasos-input"
                                disabled>
                                @foreach ($specialties as $specialtyOption)
                                  <option value="{{ $specialtyOption->id }}"
                                    {{ $specialty->specialty_id == $specialtyOption->id ? 'selected' : '' }}>
                                    {{ $specialtyOption->name }}
                                  </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div>
                            <label for="title{{ $index + 1 }}"
                              class="label-top-input-special-takhasos-elem-create">عنوان
                              تخصص</label>
                            <input type="text" name="titles[{{ $index }}]" id="title{{ $index + 1 }}"
                              class="form-control h-50 w-100 border-radius-6 mt-3"
                              value="{{ $specialty->specialty_title }}" disabled>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif
                @endforeach
              </div>
              <div class="d-flex justify-content-between mt-2 gap-4">
                <button type="submit" id="saveChangesButton"
                  class="btn my-btn-primary h-50 col-lg-11 col-xs-10 col-md-11 col-sm-11 d-flex justify-content-center align-items-center">
                  <span class="button_text">ذخیره تغیرات</span>
                  <div class="loader"></div>
                </button>
                <button
                  class="btn btn-dark h-50 col-lg-1 col-xs-2 col-md-1 col-sm-1 d-flex justify-content-center align-items-center fs-6 add-form-item "
                  type="button" id="addButton"
                  {{ $existingSpecialtiesCount >= 3 || $existingSpecialtiesCount < 1 ? 'disabled' : '' }}>

                  <img src="{{ asset('mc-assets/icons/plus.svg') }}" alt="" srcset="">

                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8" id="uuid-section"
      style="overflow:hidden; position:relative;">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('mc-assets/icons/note.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1">ویرایش آی دی </span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles takasos-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="mx-auto mt-2">
            <div class="bg-white rounded p-3">
              <ul>
                <li><small> آی دی ، عدد یا واژه ایست که با تایپ مستقیم پس از / benobe.ir صفحه اینترنتی شما را نشان میدهد
                  </small></li>
                <li><small> میتوانید برای انتخاب آی‌دی مورد علاقه‌ی خود از حروف انگلیسی نیز استفاده نمایید </small></li>
                <li><small> لطفاً توجه نمایید ، آی دی هر مطب باید به نام مطب و یا نام پزشک اشاره کند و نمی ‌توان از نام
                    های کلی همانند tehrandoctor و یا pezeshk 1 استفاده کرد. </small></li>
              </ul>
              <form action="{{ route('mc-uuid-update') }}" method="POST" id="uuid-form">
                @csrf
                <div class="row">
                  <div class="col-12 position-relative">
                    <h4 class="text-left"><span class="color-999">benobe.ir/ </span><span class="color-nobat">
                        {{ $doctor->uuid ?? '1997' }}
                      </span></h4>
                    <div class="mt-3 w-100">
                      <label class="label-top-input-special-takhasos"> آی دی خود را وارد نمایید : </label>
                      <input class="form-control mt-2 h-50" type="text" value="{{ $doctor->uuid }}"
                        name="uuid">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="w-100">
                    <button type="submit" id="uuid-btn"
                      class="w-100 btn my-btn-primary h-50 col-lg-12 col-xs-12 col-md-12 col-sm-12 d-flex justify-content-center align-items-center">
                      <span class="button_text">ذخیره تغیرات</span>
                      <div class="loader"></div>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8 " id="messengers-section"
      style="overflow:hidden; position:relative;">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('mc-assets/icons/message.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1"> پیام رسان ها</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">
        </div>
      </div>
      <div class="drop-toggle-styles messangers-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="alert alert-warning mt-2 text-center">
            <span class="text-sm fw-bold d-block font-size-15">لطفا شماره و نام کاربری پیام رسان ایتا، تلگرام یا نام
              کاربری اینستاگرام خود را وارد کنید (اختیاری).</span>
            <span class="font-size-15 mt-1">اطلاعات پیام‌رسان‌ها در صورت نیاز در دسترس بیمار قرار می‌گیرد.</span>
          </div>
          <form id="messengersForm">
            @csrf
            @method('PUT')
            <div>
              <h6 class="text-right fw-bold d-block font-size-13">پیام رسان های داخلی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20">
              <div
                class="d-flex justify-content-start gap-1 align-items-center  border border-solid py-2 px-4 rounded-lg">
                <img src="{{ asset('mc-assets/icons/eitaa-icon-colorful.svg') }}" alt=""><span
                  class="text-sm mx-1">ایتا</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="ita_phone" class="form-control h-50 border-radius-4"
                    placeholder="شماره موبایل" maxlength="11"
                    value="{{ $messengers->where('messenger_type', 'ita')->first()->phone_number ?? '' }}">
                </div>
                <div class="mt-2 w-100">
                  <input type="text" name="ita_username" class="form-control h-50 border-radius-4 mt-2"
                    placeholder="نام کاربری ایتا"
                    value="{{ $messengers->where('messenger_type', 'ita')->first()->username ?? '' }}">
                </div>
              </div>
            </div>
            <div class="mt-2">
              <h6 class="text-right fw-bold d-block font-size-13">پیام رسان های خارجی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
              <div
                class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                <img src="{{ asset('mc-assets/icons/telegram.svg') }}" alt="">
                <span class="text-sm mx-1 font-size-13">تلگرام</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="telegram_phone" class="form-control h-50 border-radius-4 col-12"
                    placeholder="شماره موبایل (اختیاری)" maxlength="11"
                    value="{{ $messengers->where('messenger_type', 'telegram')->first()->phone_number ?? '' }}">
                </div>
                <div class="mt-2 w-100">
                  <input type="text" name="telegram_username" class="form-control h-50 border-radius-4 mt-2"
                    placeholder="نام کاربری تلگرام (اختیاری)"
                    value="{{ $messengers->where('messenger_type', 'telegram')->first()->username ?? '' }}">
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
              <div
                class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                <img src="{{ asset('mc-assets/icons/instagram.svg') }}" alt="">
                <span class="text-sm mx-1 font-size-13">اینستاگرام</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="instagram_username" class="form-control h-50 border-radius-4 col-12"
                    placeholder="نام کاربری اینستاگرام (اختیاری)"
                    value="{{ $messengers->where('messenger_type', 'instagram')->first()->username ?? '' }}">
                </div>
              </div>
            </div>
            <div class="mt-2">
              <h6 class="text-right fw-bold d-block font-size-13"> تماس امن</h6>
            </div>
            <div
              class="d-flex gap-4 justify-content-between align-items-center p-3 border border-solid rounded-lg border-slate-200 mt-2">
              <div>
                <span class="text-responsive font-size-13 fw-bold">تماس امن به عنوان راه ارتباط جانبی در کنار
                  هر یک از
                  پیام‌رسان‌ها قرار می‌گیرد.</span>
                <img src="{{ asset('mc-assets/icons/help.svg') }}" alt="">

              </div>
              <div class="flex flex-col gap-2">
                <div class="flex items-center rounded-lg elative MuiBox-root muirtl-0">
                  <div class="password_toggle__AXK9v">
                    <input type="checkbox" id="secure_call" name="secure_call" value="1"
                      {{ ($messengers->where('messenger_type', 'ita')->first()->is_secure_call ?? false) ||
                      ($messengers->where('messenger_type', 'telegram')->first()->is_secure_call ?? false)
                          ? 'checked'
                          : '' }}>
                    <label for="secure_call">Toggle</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit"
                class="btn my-btn-primary w-100 h-50 border-radius-4 d-flex justify-content-center align-items-center">
                <span class="button_text">ثبت تغییرات</span>
                <div class="loader"></div>
              </button>
            </div>
          </form>
          <div>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <img src="{{ asset('mc-assets/icons/lock.svg') }}" alt="">
          <span class="txt-card-span mx-1">رمز عبور ثابت</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="">
        </div>
      </div>
      <div class="drop-toggle-styles password-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="accordion_content__bS0xm">
            <form class="w-100" action="{{ route('mc-static-password-update') }}" method="POST"
              id="staticPasswordForm">
              @csrf
              <div class="d-flex align-items-center mt-2">
                <div class="password_toggle__AXK9v d-flex align-items-center">
                  <input type="checkbox" id="static_password_enabled" name="static_password_enabled" value="1"
                    {{ $doctor->static_password_enabled ? 'checked' : '' }}>
                  <label for="static_password_enabled">Toggle</label>
                  <span id="static_password_status" class="mx-1">
                    {{ $doctor->static_password_enabled ? 'رمز عبور ثابت فعال است' : 'رمز عبور ثابت غیرفعال است' }}
                  </span>
                </div>
              </div>
              <div class="w-100 d-flex justify-content-between gap-4 flex-xs-wrap flex-xs-column mt-3">
                <!-- فیلد رمز عبور -->
                <div class="w-100 position-relative">
                  <label for="password" class="label-top-input">کلمه عبور</label>
                  <div class="input-group">
                    <input type="password" class="form-control h-50 w-100 border-radius-6 mt-3" id="password"
                      name="password" value=""
                      placeholder="{{ $doctor->static_password_enabled ? 'رمز عبور تنظیم شده است' : 'رمز عبور' }}"
                      {{ $doctor->static_password_enabled ? '' : 'disabled' }}>
                    <span
                      class="input-group-text bg-transparent border-0 position-absolute end-0 translate-middle-y ms-2 top-64">
                      <img onclick="togglePassword('password')" class="show-pass cursor-pointer"
                        src="{{ asset('mc-assets/icons/show-pass.svg') }}" alt="نمایش/مخفی"
                        style="width: 20px; height: 20px;">
                    </span>
                  </div>
                  <div class="text-danger validation-error mt-1 font-size-13"></div>
                </div>
                <!-- فیلد تکرار رمز عبور -->
                <div class="w-100 position-relative">
                  <label for="password_confirmation" class="label-top-input">تکرار کلمه عبور</label>
                  <div class="input-group">
                    <input type="password" class="form-control h-50 w-100 border-radius-6 mt-3"
                      id="password_confirmation" name="password_confirmation" value=""
                      placeholder="{{ $doctor->static_password_enabled ? 'رمز عبور تنظیم شده است' : 'تکرار رمز عبور' }}"
                      {{ $doctor->static_password_enabled ? '' : 'disabled' }}>
                    <span
                      class="input-group-text bg-transparent border-0 position-absolute end-0 translate-middle-y ms-2 top-64">
                      <img onclick="togglePassword('password_confirmation')" class="show-pass cursor-pointer"
                        src="{{ asset('mc-assets/icons/show-pass.svg') }}" alt="نمایش/مخفی"
                        style="width: 20px; height: 20px;">
                    </span>
                  </div>
                  <div class="text-danger validation-error mt-1 font-size-13"></div>
                </div>
              </div>
              <div class="w-100 mt-3">
                <button type="submit"
                  class="btn my-btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                  id="btn-save-pass" {{ $doctor->static_password_enabled ? '' : 'disabled' }}>
                  <span class="button_text">ذخیره تغییرات</span>
                  <div class="loader"></div>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <img src="{{ asset('mc-assets/icons/lock.svg') }}" alt="" srcset="">
          <span class="txt-card-span mx-1">فعال‌سازی گذرواژه دو مرحله‌ای</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">
        </div>
      </div>
      <div class="drop-toggle-styles two-factor-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="accordion_content__bS0xm">
            <div class="d-flex align-items-center mt-3">
              <div class="d-flex align-items-center mt-3">
                <div class="password_toggle__AXK9v d-flex align-items-center">
                  <input type="checkbox" id="two_factor_secret_enabled" name="two_factor_secret_enabled"
                    value="1" {{ $doctor->two_factor_secret_enabled ? 'checked' : '' }}>
                  <label for="two_factor_secret_enabled">Toggle</label>
                </div>
                <span id="two_factor_status" class="mx-1">
                  {{ $doctor->two_factor_secret_enabled ? 'گذرواژه دو مرحله‌ای فعال است' : 'گذرواژه دو مرحله‌ای غیرفعال است' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8" id="faq-section">
      <div class="d-flex justify-content-between align-items-center faq-section-clicked">
        <div>
          <img src="{{ asset('mc-assets/icons/help.svg') }}" alt="">
          <span class="txt-card-span mx-1">سوالات متداول</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="">
        </div>
      </div>
      <div class="drop-toggle-styles faq-section-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="alert alert-info mt-2 text-center">
            <span class="text-sm fw-bold d-block font-size-15">سوالات متداول شما در پروفایل عمومی نمایش داده
              می‌شود.</span>
            <span class="font-size-15 mt-1">این بخش به بیماران کمک می‌کند تا سوالات رایج را مشاهده کنند.</span>
          </div>

          <!-- بخش افزودن سوال جدید - Dropdown -->
          <div class="mt-4">
            <div class="faq-add-toggle">
              <div class="d-flex align-items-center justify-content-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg" class="me-2">
                  <path d="M12 5V19M5 12H19" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                <span class="fw-bold text-primary">افزودن سوال متداول جدید</span>
              </div>
              <div class="faq-add-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M6 9L12 15L18 9" stroke="#6b7280" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>
            </div>

            <!-- فرم افزودن سوال جدید - مخفی شده -->
            <div class="faq-add-form">
              <form id="faqForm" class="mt-3 p-3">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <label for="question" class="label-top-input">سوال</label>
                    <input type="text" id="question" name="question"
                      class="form-control h-50 w-100 border-radius-6 mt-3" placeholder="سوال خود را وارد کنید..."
                      maxlength="255">
                    <div class="text-danger validation-error mt-1 font-size-13"></div>
                  </div>
                  <div class="col-md-6">
                    <label for="order" class="label-top-input">ترتیب نمایش</label>
                    <input type="number" id="order" name="order"
                      class="form-control h-50 w-100 border-radius-6 mt-3" placeholder="0" min="0"
                      value="0">
                    <div class="text-danger validation-error mt-1 font-size-13"></div>
                  </div>
                </div>
                <div class="mt-3">
                  <textarea id="answer" name="answer" class="form-control w-100 border-radius-6 mt-3" rows="3"
                    placeholder="پاسخ خود را وارد کنید..."></textarea>
                  <div class="text-danger validation-error mt-1 font-size-13"></div>
                </div>
                <div class="mt-3 d-flex gap-2">
                  <button type="submit"
                    class="btn my-btn-primary flex-grow-1 h-50 border-radius-4 d-flex justify-content-center align-items-center">
                    <span class="button_text">افزودن سوال متداول</span>
                    <div class="loader"></div>
                  </button>
                  <button type="button" class="btn btn-outline-secondary h-50 border-radius-4 px-3 faq-add-cancel">
                    انصراف
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- لیست سوالات متداول -->
          <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="text-right fw-bold d-block font-size-13 mb-0">سوالات متداول شما</h6>
              <span class="badge bg-primary">{{ count($doctorFaqs) }} سوال</span>
            </div>

            <div id="faqsList" class="faqs-container">
              @forelse($doctorFaqs as $faq)
                <div class="faq-item-compact" data-faq-id="{{ $faq->id }}">
                  <div class="faq-item-header-compact">
                    <div class="faq-item-main">
                      <div class="faq-item-title">
                        <h6 class="faq-question-text">{{ $faq->question }}</h6>
                        <div class="faq-item-badges">
                          <span class="badge {{ $faq->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">
                            {{ $faq->is_active ? 'فعال' : 'غیرفعال' }}
                          </span>
                          <span class="badge bg-info badge-sm">ترتیب: {{ $faq->order }}</span>
                        </div>
                      </div>
                      <div class="faq-item-actions-compact">
                        <button type="button" class="btn btn-sm btn-light edit-faq-btn"
                          data-faq-id="{{ $faq->id }}" title="ویرایش">
                          <img src="{{ asset('mc-assets/icons/edit.svg') }}" alt="ویرایش"
                            style="width: 14px; height: 14px;">
                        </button>
                        <button type="button" class="btn btn-sm btn-light delete-faq-btn"
                          data-faq-id="{{ $faq->id }}" title="حذف">
                          <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف"
                            style="width: 14px; height: 14px;">
                        </button>
                      </div>
                    </div>
                    <div class="faq-item-preview">
                      <p class="faq-answer-preview">{{ Str::limit($faq->answer, 80) }}</p>
                    </div>
                  </div>
                </div>
              @empty
                <div class="faq-empty-state">
                  <img src="{{ asset('mc-assets/icons/help.svg') }}" alt="" class="faq-empty-icon">
                  <p class="faq-empty-text">هنوز سوال متداولی اضافه نکرده‌اید.</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <img src="{{ asset('mc-assets/icons/cogs.svg') }}" alt="" srcset="">
          <span class="txt-card-span mx-1"> تنظیمات نسخه نویسی</span>
          <span class="badge bg-danger text-white mx-2" style="font-size: 13px;">به زودی</span>
        </div>
        <div>
          <img src="{{ asset('mc-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <fieldset disabled style="opacity:0.6;">
        <div class="drop-toggle-styles noskheh-data-drop-toggle">
          <div class="loading-spinner d-none"></div>
          <div>
            <div class="mt-3">
              <div class="d-flex align-items-center space-s-3">
                <div class="settings_toggle__NBCHl">
                  <div class="password_toggle__AXK9v d-flex align-items-center">
                    <label for="switch">Toggle</label>
                  </div>
                </div><span class="mx-2">غیرفعال کردن SMS نهایی سازی نسخه</span>
              </div>
              <div class="d-flex align-items-center mt-3">
                <div class="settings_toggle__NBCHl">
                  <div class="password_toggle__AXK9v d-flex align-items-center">
                    <label for="switch">Toggle</label>
                  </div>
                </div><span class="mx-2"> ویرایش اطلاعات احراز هویت</span>
              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/profile/edit-profile.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
</script>
@include('mc.panel.profile.option.profile-option')
@endsection
