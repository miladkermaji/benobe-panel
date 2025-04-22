@extends('dr.panel.layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/panel.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/secretary_options/secretary_option.css') }}">

@endsection

@section('site-header', 'به نوبه | پنل دکتر')
@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی‌ها')

<div class="container-fluid py-2" dir="rtl">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت دسترسی‌های منشی‌ها</h1>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        @forelse ($secretaries as $secretary)
          @php
            $permission = $secretary->permissions->firstWhere('clinic_id', $secretary->clinic_id);
            $savedPermissions = $permission ? json_decode($permission->permissions ?? '[]', true) : [];
            $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
          @endphp
          <div class="secretary-toggle border-bottom">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer toggle-header"
              data-secretary-id="{{ $secretary->id }}">
              <div class="d-flex align-items-center gap-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                <span class="fw-bold">{{ $secretary->first_name }} {{ $secretary->last_name }}</span>
                <span class="badge bg-label-primary">{{ $secretary->clinic->name ?? 'ویزیت آنلاین' }}</span>
              </div>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                class="transition-transform toggle-icon">
                <path d="M6 9l6 6 6-6" />
              </svg>
            </div>

            <div class="permissions-content p-3 bg-light d-none">
              <div class="permissions-grid">
                @foreach ($permissions as $permissionKey => $permissionData)
                  <div class="permission-item p-2 rounded shadow-sm">
                    <div class="d-flex align-items-center mb-1">
                      <label class="custom-checkbox">
                        <input type="checkbox"
                          class="custom-checkbox-input parent-permission update-permissions substituted"
                          data-secretary-id="{{ $secretary->id }}" value="{{ $permissionKey }}"
                          {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                        <span class="custom-checkbox-checkmark"></span>
                        <span class="custom-checkbox-label text-gray-700" style="font-weight: 700; font-size: 14px;">
                          {{ $permissionData['title'] }}
                        </span>
                      </label>
                    </div>
                    @if (!empty($permissionData['routes']))
                      <div class="permission-sub-items ps-4 pe-2">
                        @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                          <div class="d-flex align-items-center mb-1">
                            <label class="custom-checkbox">
                              <input type="checkbox"
                                class="custom-checkbox-input child-permission update-permissions substituted"
                                data-secretary-id="{{ $secretary->id }}" data-parent="{{ $permissionKey }}"
                                value="{{ $routeKey }}"
                                {{ in_array($routeKey, $savedPermissions) ? 'checked' : '' }}>
                              <span class="custom-checkbox-checkmark"></span>
                              <span class="custom-checkbox-label text-gray-600" style="font-size: 13px;">
                                {{ $routeTitle }}
                              </span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="text-muted mb-3">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <p class="text-muted fw-medium m-0">منشی‌ای ثبت نشده است.</p>
            </div>
          </div>
        @endforelse
      </div>
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
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
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

      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });

    // مدیریت تاگل‌ها
    $('.toggle-header').on('click', function() {
      const secretaryId = $(this).data('secretary-id');
      const content = $(this).siblings('.permissions-content');
      const icon = $(this).find('.toggle-icon');

      content.toggleClass('d-none');
      icon.toggleClass('rotate-180');
    });

    let updateTimer;

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

    $('.parent-permission').change(function() {
      let isChecked = $(this).is(':checked');
      let parentKey = $(this).val();
      let secretaryId = $(this).data('secretary-id');
      $(this).closest('.permission-item').find(`.child-permission[data-parent="${parentKey}"]`).prop('checked',
        isChecked);
      clearTimeout(updateTimer);
      updateTimer = setTimeout(() => updatePermissions(secretaryId), 500);
    });

    $('.update-permissions').change(function() {
      let secretaryId = $(this).data('secretary-id');
      clearTimeout(updateTimer);
      updateTimer = setTimeout(() => updatePermissions(secretaryId), 500);
    });
  });
</script>
@endsection
