@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
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
<div class="main-content mb-5">
  @if (!$doctor->profile_completed && count($incompleteSections) > 0)
    <div class="alert alert-warning text-center">
      <span class="font-weight-bold">
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
            <img src="{{ asset('dr-assets/panel/img/pro.jpg') }}" class="avatar___img-main">
          </div>
          <div class="mx-2 mt-3">
            <span class="d-block font-weight-bold font-size-15 profile-header-name">
              {{ Auth::guard('doctor')->user()->first_name . ' ' . Auth::guard('doctor')->user()->last_name }}</span>
            <span class="badge badge-light p-2 border-radius-8 mt-3 mx-3 font-size-13 cursor-pointer">
              {{ $specialtyName }}
            </span>
          </div>
        </div>
        <div class="show-profile-badge">
          <a href="" class="d-flex">

            <img src="{{ asset('dr-assets/icons/eye.svg') }}" alt="" srcset="">

            <span class="mx-1">مشاهده پروفایل</span>
          </a>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8" id="personal-data">
      <div class="d-flex justify-content-between align-items-center personal-data-clicked">
        <div>

          <img src="{{ asset('dr-assets/icons/user.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1">اطلاعات فردی</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles personal-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div class="">
          <form action="{{ route('dr-update-profile') }}" method="POST" id="profileEdit">
            @csrf
            <div class="mt-2">
              <label for="name" class="label-top-input">نام</label>
              <input type="text" class="my-form-control w-100 border-radius-6 mt-3"
                value="{{ Auth::guard('doctor')->user()->first_name }}" name="first_name" readonly>
              @error('first_name')
                <span class="text-danger font-weight-bold mt-2">{{ $message }}</span>
              @enderror
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">نام خانوادگی</label>
              <input type="text" class="my-form-control w-100 border-radius-6 mt-3"
                value="{{ Auth::guard('doctor')->user()->last_name }}" name="last_name" readonly>
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">کدملی</label>
              <input type="text" value="{{ Auth::guard('doctor')->user()->national_code ?? '' }}"
                class="my-form-control-light h-50 w-100 border-radius-6 mt-3 text-right" name="national_code">
            </div>
            <div class="mt-2">
              <label for="name" class="label-top-input">شماره نظام پزشکی</label>
              <input type="text" value="{{ Auth::guard('doctor')->user()->license_number ?? '' }}"
                class="my-form-control-light h-50 w-100 border-radius-6 mt-3 text-right" name="license_number">
            </div>
            <div class="d-flex justify-content-between mt-4 gap-4 position-relative">
              <label for="name" class="label-top-input-special-takhasos">شماره موبایل</label>
              <input class="my-form-control h-50 col-lg-11 col-xs-10 col-md-11 col-sm-11 text-right disabled "
                placeholder="شماره موبایل" name="mobile" value="{{ Auth::guard('doctor')->user()->mobile ?? '' }}"
                disabled>
              <button
                class="btn btn-dark h-50 col-lg-1 col-xs-2 col-md-1 col-sm-1 d-flex justify-content-center align-items-center fs-6 add-form-item"
                type="button" id="editButton" data-toggle="modal" data-target="#mobileEditModal">

                <img src="{{ asset('dr-assets/icons/pencil-edit.svg') }}" alt="" srcset="">

              </button>
            </div>
            <div class="mt-4 position-relative">
              <label for="province_id" class="label-top-input-city">استان</label>
              <select name="province_id" id="province_id"
                class="form-control h-50 w-100 border-radius-6 mt-3 tom-select" autocomplete="off">
                <option value="">انتخاب استان</option>
                @foreach (\App\Models\Zone::provinces()->get() as $province)
                  <option value="{{ $province->id }}"
                    {{ Auth::guard('doctor')->user()->province_id == $province->id ? 'selected' : '' }}>
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
                @if (Auth::guard('doctor')->user()->city_id)
                  @php
                    $city = \App\Models\Zone::find(Auth::guard('doctor')->user()->city_id);
                  @endphp
                  <option value="{{ $city->id }}" selected>{{ $city->name }}</option>
                @endif
              </select>
            </div>
            <div class="mt-4">
              <label for="name" class="font-weight-bold font-size-13"> بیوگرافی و توضیحات</label>
              <textarea class="ckeditor form-control" name="description" class="form-control" id="description">
            {{ trim(Auth::guard('doctor')->user()->bio ?? '') }}
        </textarea>
            </div>
            <div class="w-100">
              <button type="submit"
                class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
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
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                  class="btn btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                  <span class="button_text">ارسال کد تایید</span>
                  <div class="loader"></div>
                </button>
              </div>
            </div>
            <div id="otpInputStep" style="display:none;">
              <label class="label-top font-weight-bold">کد تایید 4 رقمی را وارد کنید</label>
              <div class="d-flex justify-content-center gap-10 mt-2" dir="ltr">
                <input type="text" maxlength="1" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
                <input type="text" maxlength="1" class="form-control otp-input text-center"
                  style="width:70px;height:60px">
              </div>
              <div class="d-flex mt-3">
                <button onclick="verifyOtpCode()"
                  class="btn btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
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
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8" id="specialty-section">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('dr-assets/icons/medal.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1"> تخصص</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles takasos-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="text-left mt-3 remove-form-item">
            <img src="{{ asset('dr-assets/icons/times.svg') }}" alt="" srcset="">

          </div>
          <div class="mt-2">
            <form action="{{ route('dr-specialty-update') }}" method="POST" id="specialtyEdit">
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
                <h6 class="font-weight-bold mt-3">تخصص های اضافی</h6>
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

                        <img src="{{ asset('dr-assets/icons/times.svg') }}" alt="" srcset="">

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
                  class="btn btn-primary h-50 col-lg-11 col-xs-10 col-md-11 col-sm-11 d-flex justify-content-center align-items-center">
                  <span class="button_text">ذخیره تغیرات</span>
                  <div class="loader"></div>
                </button>
                <button
                  class="btn btn-dark h-50 col-lg-1 col-xs-2 col-md-1 col-sm-1 d-flex justify-content-center align-items-center fs-6 add-form-item "
                  type="button" id="addButton"
                  {{ $existingSpecialtiesCount >= 3 || $existingSpecialtiesCount < 1 ? 'disabled' : '' }}>

                  <img src="{{ asset('dr-assets/icons/plus.svg') }}" alt="" srcset="">

                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8" id="uuid-section">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('dr-assets/icons/note.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1">ویرایش آی دی </span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles takasos-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="mx-auto mt-2">
            <div class="bg-white py-3 rounded">
              <ul>
                <li><small> آی دی ، عدد یا واژه ایست که با تایپ مستقیم پس از / benobe.ir صفحه اینترنتی شما را نشان میدهد
                  </small></li>
                <li><small> میتوانید برای انتخاب آی‌دی مورد علاقه‌ی خود از حروف انگلیسی نیز استفاده نمایید </small></li>
                <li><small> لطفاً توجه نمایید ، آی دی هر مطب باید به نام مطب و یا نام پزشک اشاره کند و نمی ‌توان از نام
                    های کلی همانند tehrandoctor و یا pezeshk 1 استفاده کرد. </small></li>
              </ul>
              <form action="{{ route('dr-uuid-update') }}" method="POST" id="uuid-form">
                @csrf
                <div class="row">
                  <div class="col-12 position-relative">
                    <h4 class="text-left"><span class="color-999">benobe.ir/ </span><span class="color-nobat">
                        {{ Auth::guard('doctor')->user()->uuid ?? '1997' }}
                      </span></h4>
                    <div class="mt-3 w-100">
                      <label class="label-top-input-special-takhasos"> آی دی خود را وارد نمایید : </label>
                      <input class="form-control mt-2 h-50" type="text"
                        value="{{ Auth::guard('doctor')->user()->uuid }}" name="uuid">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="w-100">
                    <button type="submit" id="uuid-btn"
                      class="w-100 btn btn-primary h-50 col-lg-12 col-xs-12 col-md-12 col-sm-12 d-flex justify-content-center align-items-center">
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
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8 " id="messengers-section">
      <div class="d-flex justify-content-between align-items-center">
        <div>

          <img src="{{ asset('dr-assets/icons/message.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1"> پیام رسان ها</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">
        </div>
      </div>
      <div class="drop-toggle-styles messangers-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="alert alert-warning mt-2 text-center">
            <span class="text-sm font-weight-bold d-block font-size-15">لطفا شماره و نام کاربری پیام رسان ایتا یا شماره
              واتساپ خود را وارد.</span>
            <span class="font-size-15 mt-1">شماره موبایل این پیام رسان ها در دسترس بیمار قرار میگیرد.</span>
          </div>
          <form id="messengersForm">
            @csrf
            @method('PUT')
            <div>
              <h6 class="text-left font-weight-bold d-block font-size-13">پیام رسان های داخلی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20">
              <div
                class="d-flex justify-content-start gap-1 align-items-center  border border-solid py-2 px-4 rounded-lg">
                <img
                  src="data:image/png;base64,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"
                  alt=""><span class="text-sm mx-1">ایتا</span>
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
              <h6 class="text-left font-weight-bold d-block font-size-13">پیام رسان های خارجی</h6>
            </div>
            <div class="d-flex align-items-center justify-content-start gap-20 mt-2">
              <div
                class="d-flex justify-content-center gap-1 align-items-center border border-solid py-2 px-3 rounded-lg">
                <img
                  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAUkSURBVHgBhVVrTFxFGD1zdxe2gLAt0AalsG1aU62m0KSx8VXalKa2KJDGik1UamPjDyvVxMSYJgWtJiYmBRONCbXF2KLVKPCjUtNgaXwFURbsQ4EAi9DyKMWlu8tj994Zv5m7dxdasDe5O3e+mTnnzJlvv2H4n6fIc9CVHDddxhjyqOsWYNmgH/r2CYZ2cKPB0Bx1tfd/1L8QBpsvWOIpddsd8ScILM+aJScKQS+Tv5HFkozYKVBjhGcqvsyt8d6RYM/Fl8rARTktdMm+UOsRQRIQ8ywWcoBpPsZFRe266qoFCXZ3vHiYZpczK2zJZnN1iMgm2OytQSqRc1H+1brjFbcR7PI8X6bBVhlTKKLDibYE5C15BOlxaap/PTSG33xtuB6+YeLH0IQk14DXvs6tqYoSFHlK3IzHeUiRS26XMdPYRHsCXsnahw2u9RgjsL8C3US2CKmOVGQ7M9E8/hNOD9eL0ZkxZhFArqYkAAvnfLu+tt+utBqOwxw8hSkJcudcLItPZ0fufUupfaf3A1wJdMKyTlohd/NyZineXvUmO9T1HkZDNyC4qY1mpEBoNfSxmW3/tcRtt2l9KkEitiZqiaha+y4uB/7GxwPHY35b/s/ypfSeEmxIycWrVw6JSSMIlcbmcbCAY3KxxgQrMjgndk66BTgXKFi6TQF9OnASS2yL8caKA8iMvxthQ4fODei62Ya5jmMDpxDUJ/Fkej4zDM4MYRCUoJbDGXKWaQRbyKmjU24atEi+W1MfQ+3Vb+AL+7EzPR8PpazHJjpkCWiRqNYw2/rhRhQuewIGYUhwOc6VaJGnUSeH2CLgHGl0gNLf7mCfAuARL+SCsASkeSHDJApHCH4cb0GCtggrnFlMYnCJJ1SbrdFCl2mRYBIkPT5VIXb6exAmK74balIE/nCQCHXzlTYRuM7N74mQX81xMiekWC7tJiwSvUJTTBTQZYC+r02OMHNyvAKQRB/2HMMLWbtRnLFDKU5zLMGDyffNsUuemVBnyNUuJJZs7UJwL6dCZv1XhqZHlFr3oix4fJdU7It/6pUFB1buw7rktVidtBIZzqU4PdiAyu5q5KSsVfO6gj3qHGSNooShrGQTdlLupaoVqZJmKjaONqF0+TNoHW+HVdo+6fkcg1PDKMjIV+B+PajGpfrtS7fQmh9wMxQwi4VVvsDa7QbXm6lQbWIqxlW8K9BLizarQ5xdMuoHz6JuoBHJcUmK2B8OYM/yYuxYtgVP/76fEoXHipb8MniDFtKcVdwwZP6qA5KttKF1vANOLR4rE9yRjIml6Pj0BHwzEyjJLMTrq/ejqqcag8EhxpXvEsdMgpDG6hRj7rn889HaL5VuPIEke6I6tGRHEq5NjSg7Wv/tUONrklfhqYxt6vtI51FcGGsxRbNYYaWS8Vnb1u/3mrVIt+0VtrCHwi7yV1ydGmZtvj/xh++i6A70sE1pD+Px1I14LnsX7iLCTn8vqr2ncGb4HG5SQrDYxWC6LzChcaMiWk3l88DZvDLqHpXcUR2RmhIz1SpDArePzbqKmHbw0vYmVa5tVmz0pLcl7Vm39DFPqDtKXo4y41R2Q6gEEKpeqZxXl6eIXj5WnAYqLu84//7ss57zrD7zaJkmtHKa6Yokm8prccsCEY1HrzwfA6/oLPi5cjbevJf+mrqNbsNho+uTlUpVUdNgXfqR4m1ZxFizofHSvp2/9N+KNS+B9biJSNNYIaktIqk51KYoUAEv3YteZrALHHGV3uJm30IY/wHQyRqM6Hi8vgAAAABJRU5ErkJggg=="
                  alt="">
                <span class="text-sm mx-1 font-size-13">واتساپ</span>
              </div>
              <div class="w-100">
                <div class="w-100">
                  <input type="text" name="whatsapp_phone" class="form-control h-50 border-radius-4 col-12"
                    placeholder="شماره موبایل" maxlength="11"
                    value="{{ $messengers->where('messenger_type', 'whatsapp')->first()->phone_number ?? '' }}">
                </div>
              </div>
            </div>
            <div class="mt-2">
              <h6 class="text-left font-weight-bold d-block font-size-13"> تماس امن</h6>
            </div>
            <div
              class="d-flex gap-4 justify-content-between align-items-center p-3 border border-solid rounded-lg border-slate-200 mt-2">
              <div>
                <span class="text-responsive font-size-13 font-weight-bold">تماس امن به عنوان راه ارتباط جانبی در کنار
                  هر یک از
                  پیام‌رسان‌ها قرار می‌گیرد.</span>
                <img src="{{ asset('dr-assets/icons/help.svg') }}" alt="">

              </div>
              <div class="flex flex-col gap-2">
                <div class="flex items-center rounded-lg elative MuiBox-root muirtl-0">
                  <div class="password_toggle__AXK9v">
                    <input type="checkbox" id="secure_call" name="secure_call" value="1"
                      {{ ($messengers->where('messenger_type', 'ita')->first()->is_secure_call ?? false) ||
                      ($messengers->where('messenger_type', 'whatsapp')->first()->is_secure_call ?? false)
                          ? 'checked'
                          : '' }}>
                    <label for="secure_call">Toggle</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit"
                class="btn btn-primary w-100 h-50 border-radius-4 d-flex justify-content-center align-items-center">
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
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <img src="{{ asset('dr-assets/icons/lock.svg') }}" alt="">

          <span class="txt-card-span mx-1">رمز عبور ثابت</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="">

        </div>
      </div>
      <div class="drop-toggle-styles password-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="accordion_content__bS0xm">
            <form class="w-100" action="{{ route('dr-static-password-update') }}" method="POST"
              id="staticPasswordForm">
              @csrf
              <div class="d-flex align-items-center mt-2">
                <div class="password_toggle__AXK9v d-flex align-items-center">
                  <input type="checkbox" id="static_password_enabled" name="static_password_enabled" value="1"
                    {{ Auth::guard('doctor')->user()->static_password_enabled ? 'checked' : '' }}>
                  <label for="static_password_enabled">Toggle</label>
                  <span class="mx-1">رمزعبور ثابت فعال است</span>
                </div>
              </div>
              <div class="w-100 d-flex justify-content-between gap-4 flex-xs-wrap flex-xs-column">
                <div class="w-100">
                  <label for="password" class="label-top-input"> کلمه عبور</label>
                  <input type="password"
                    class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje"
                    id="password" name="password" placeholder="رمز عبور"
                    {{ Auth::guard('doctor')->user()->static_password_enabled ? '' : 'disabled' }}>

                  <img onclick="togglePassword('password')" class="show-pass"
                    src="{{ asset('dr-assets/icons/hide-pass.svg') }}" alt="">

                </div>
                <div class="w-100">
                  <label for="password_confirmation" class="label-top-input">تکرار کلمه عبور</label>
                  <input type="password" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative"
                    id="password_confirmation" name="password_confirmation" placeholder="تکرار رمز عبور"
                    {{ Auth::guard('doctor')->user()->static_password_enabled ? '' : 'disabled' }}>
                  <img onclick="togglePassword('password_confirmation')" class="hide-pass"
                    src="{{ asset('dr-assets/icons/show-pass.svg') }}" alt="">
                </div>
              </div>
              <div class="w-100 mt-3">
                <button type="submit"
                  class="btn btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                  id="btn-save-pass" {{ Auth::guard('doctor')->user()->static_password_enabled ? '' : 'disabled' }}>
                  <span class="button_text">ذخیره تغیرات</span>
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
          <img src="{{ asset('dr-assets/icons/lock.svg') }}" alt="" srcset="">

          <span class="txt-card-span mx-1">فعال‌سازی گذرواژه دو مرحله‌ای</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
      <div class="drop-toggle-styles two-factor-data-drop-toggle">
        <div class="loading-spinner d-none"></div>
        <div>
          <div class="accordion_content__bS0xm">
            <div class="d-flex align-items-center mt-3">
              <div class="password_toggle__AXK9v d-flex align-items-center">
                <input type="checkbox" id="two_factor_enabled" name="two_factor_enabled" value="1"
                  {{ Auth::guard('doctor')->user()->two_factor_secret_enabled ? 'checked' : '' }}>
                <label for="two_factor_enabled">Toggle</label>
              </div>
              <span class="mx-1"> گذرواژه دو مرحله ای فعال است</span>
            </div>
            <form class="w-100" action="{{ route('dr-two-factor-update') }}" method="POST" id="twoFactorForm">
              @csrf
              <div class="w-100 d-flex justify-content-between gap-4 flex-xs-wrap flex-xs-column">
                <div class="w-100">
                  <label for="two_factor_secret" class="label-top-input"> کلید مخفی</label>
                  <input type="text"
                    class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje"
                    id="two_factor_secret" name="two_factor_secret" placeholder="کلید مخفی"
                    {{ Auth::guard('doctor')->user()->two_factor_secret_enabled ? '' : 'disabled' }}>
                </div>
              </div>
              <div class="w-100 mt-3">
                <button type="submit"
                  class="btn btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
                  id="btn-save-two-factor"
                  {{ Auth::guard('doctor')->user()->two_factor_secret_enabled ? '' : 'disabled' }}>
                  <span class="button_text">ذخیره تغیرات</span>
                  <div class="loader"></div>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12  col-md-12 col-lg-8">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <img src="{{ asset('dr-assets/icons/cogs.svg') }}" alt="" srcset="">
          <span class="txt-card-span mx-1"> تنظیمات نسخه نویسی</span>
        </div>
        <div>
          <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="" srcset="">

        </div>
      </div>
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
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/profile/edit-profile.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
// تابع کمکی برای مقداردهی اولیه Tom Select
function initializeTomSelect(elementId, options = {}) {
    const selectElement = document.getElementById(elementId);
    if (selectElement && !selectElement.tomSelect) {
        return new TomSelect(`#${elementId}`, {
            direction: 'rtl',
            placeholder: options.placeholder || 'انتخاب کنید',
            allowEmptyOption: true,
            maxOptions: null,
            ...options
        });
    }
    return selectElement ? selectElement.tomSelect : null;
}

document.addEventListener('DOMContentLoaded', function() {
    // مقداردهی اولیه Tom Select برای استان و شهر و ذخیره نمونه‌ها
    const provinceTomSelect = initializeTomSelect('province_id', { placeholder: 'انتخاب استان' });
    const cityTomSelect = initializeTomSelect('city_id', { placeholder: 'انتخاب شهر' });

    // بررسی وجود نمونه‌ها
    if (!provinceTomSelect || !cityTomSelect) {
        console.error('خطا در مقداردهی Tom Select برای استان یا شهر');
        return;
    }

    const provinceSelect = document.getElementById('province_id');

    // مدیریت تغییر در انتخاب استان
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;

        if (provinceId) {
            fetch(`{{ route('dr-get-cities') }}?province_id=${provinceId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cityTomSelect.clear();
                    cityTomSelect.clearOptions();
                    cityTomSelect.addOption({ value: '', text: 'انتخاب شهر' });
                    data.cities.forEach(city => {
                        cityTomSelect.addOption({ value: city.id, text: city.name });
                    });
                    cityTomSelect.enable();
                    cityTomSelect.refreshOptions();
                } else {
                    toastr.error(data.message || 'خطا در بارگذاری شهرها');
                    cityTomSelect.clear();
                    cityTomSelect.clearOptions();
                    cityTomSelect.addOption({ value: '', text: 'خطا در بارگذاری' });
                    cityTomSelect.disable();
                }
            })
            .catch(error => {
                toastr.error('خطا در ارتباط با سرور');
                cityTomSelect.clear();
                cityTomSelect.clearOptions();
                cityTomSelect.addOption({ value: '', text: 'خطا در بارگذاری' });
                cityTomSelect.disable();
            });
        } else {
            cityTomSelect.clear();
            cityTomSelect.clearOptions();
            cityTomSelect.addOption({ value: '', text: 'ابتدا یک استان انتخاب کنید' });
            cityTomSelect.disable();
        }
    });

    // لود اولیه شهرها اگر استان از قبل انتخاب شده باشد
    if (provinceSelect.value) {
        provinceSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@include('dr.panel.profile.option.profile-option')

@endsection
