@extends('dr.panel.layouts.master')
@section('styles')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/panel.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/secretary_options/secretary_option.css') }}">
@endsection
@section('site-header', 'به نوبه | پنل دکتر')
@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی‌ها')
<div class="container">
  <div class="">
    <div class="">
      <form id="permissions-form">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-dark">
              <tr>
                <th>نام منشی</th>
                <th>دسترسی‌ها</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($secretaries as $secretary)
                @php
                  $savedPermissions = json_decode($secretary->permissions->permissions ?? '[]', true);
                  $savedPermissions = is_array($savedPermissions) ? $savedPermissions : []; // اطمینان از آرایه بودن
                @endphp
                <tr>
                  <td>{{ $secretary->first_name }} {{ $secretary->last_name }}</td>
                  <td>
                    <div class="form-check w-100 my-check-wrapper" style="text-align: right">
                      @foreach ($permissions as $permissionKey => $permissionData)
                        <div class="mb-2 d-flex align-items-center">
                          <input type="checkbox"
                            class="form-check-input parent-permission update-permissions substituted"
                            data-secretary-id="{{ $secretary->id }}" value="{{ $permissionKey }}"
                            {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                          <label class="form-check-label font-weight-bold mx-1">{{ $permissionData['title'] }}</label>
                        </div>
                        @if (!empty($permissionData['routes']))
                          <div class="ml-3">
                            @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                              <div class="d-flex align-items-center">
                                <input type="checkbox"
                                  class="form-check-input child-permission update-permissions substituted"
                                  data-secretary-id="{{ $secretary->id }}" data-parent="{{ $permissionKey }}"
                                  value="{{ $routeKey }}"
                                  {{ in_array($routeKey, $savedPermissions) ? 'checked' : '' }}>
                                <label class="form-check-label mx-1">{{ $routeTitle }}</label>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      @endforeach
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script>
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    $('.option-card').on('click', function() {
      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);

      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;

      // ریلود صفحه با پارامتر جدید
      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
  $(document).ready(function() {
    let updateTimer; // متغیر برای مدیریت debounce
    // تابع برای ارسال درخواست AJAX
    function updatePermissions(secretaryId) {
      let permissions = [];
      let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';

      $('input[data-secretary-id="' + secretaryId + '"]:checked').each(function() {
        permissions.push($(this).val());
      });

      $.ajax({
        url: "{{ route('dr-secretary-permissions-update', ':id') }}".replace(':id', secretaryId),
        method: "POST",
        data: {
          permissions: permissions,
          selectedClinicId: selectedClinicId,
          _token: "{{ csrf_token() }}"
        },
        success: function(response) {
          if (response.success) {
            toastr.success(response.message);
          }
        },
        error: function() {
          toastr.error('مشکلی در ذخیره اطلاعات پیش آمد.');
        }
      });
    }

    // مدیریت ارتباط بین والد و فرزند
    $('.parent-permission').change(function() {
      let isChecked = $(this).is(':checked');
      let parentKey = $(this).val();
      let secretaryId = $(this).data('secretary-id');
      $(this).closest('td').find(`.child-permission[data-parent="${parentKey}"]`).prop('checked', isChecked);
      clearTimeout(updateTimer);
      updateTimer = setTimeout(() => updatePermissions(secretaryId), 500);
    });
    // بروزرسانی سطوح دسترسی با debounce
    $('.update-permissions').change(function() {
      let secretaryId = $(this).data('secretary-id');
      clearTimeout(updateTimer);
      updateTimer = setTimeout(() => updatePermissions(secretaryId), 500);
    });
  });
</script>
@endsection
