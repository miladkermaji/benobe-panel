@extends('dr.panel.layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/panel.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/scheduleSetting.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/secretary_options/secretary_option.css') }}">
  <style>
    /* استایل کلی صفحه */
    .permissions-container {
      max-width: 100%;
      margin: 0 auto;
      padding: 20px 30px;
      min-height: calc(100vh - 100px);
      display: flex;
      flex-direction: column;
    }

    /* کارت اصلی */
    .permissions-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      overflow: hidden;
      border: 1px solid #e9ecef;
      flex: 1;
    }

    /* هدر کارت */
    .permissions-card-header {
      background: linear-gradient(135deg, #343a40, #495057);
      color: #fff;
      padding: 15px 20px;
      font-size: 18px;
      font-weight: 600;
      border-bottom: 1px solid #e9ecef;
    }

    /* هدر جدول */
    .table-dark {
      background: #495057;
      color: #fff;
      font-weight: 600;
      text-align: center;
    }

    /* جدول */
    .table-responsive {
      border-radius: 8px;
      overflow-x: auto;
    }

    .table {
      margin-bottom: 0;
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }

    .table th,
    .table td {
      vertical-align: middle;
      padding: 15px;
      border-color: #e9ecef;
    }

    .table th {
      font-size: 16px;
    }

    .table td {
      font-size: 14px;
    }

    .table tbody tr {
      transition: background 0.3s ease;
    }

    .table tbody tr:hover {
      background: #f8f9fa;
    }

    .table td:first-child {
      font-weight: 500;
      color: #212529;
      min-width: 150px;
    }

    /* تنظیمات دسترسی‌ها */
    .permissions-list {
      padding: 15px;
    }

    /* چیدمان چندستونه */
    .permissions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      direction: rtl;
    }

    .permission-item {
      padding: 10px;
      border-bottom: 1px solid #f1f3f5;
      transition: background 0.3s ease;
    }

    .permission-item:hover {
      background: #f8f9fa;
    }

    .permission-item:last-child {
      border-bottom: none;
    }

    /* فاصله‌گذاری برای دسترسی‌های زیرمجموعه */
    .permission-sub-items {
      margin-right: 20px;
      margin-top: 5px;
    }

    /* ریسپانسیو کردن */
    @media (max-width: 992px) {
      .permissions-container {
        padding: 15px;
      }

      .table th,
      .table td {
        padding: 12px;
        font-size: 14px;
      }

      .permissions-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
      }

      .permissions-card-header {
        font-size: 16px;
        padding: 12px 15px;
      }
    }

    @media (max-width: 768px) {

      .table th,
      .table td {
        padding: 10px;
        font-size: 13px;
      }

      .permissions-grid {
        grid-template-columns: 1fr;
        /* توی موبایل تک‌ستونه می‌شه */
      }

      .table td:first-child {
        min-width: 120px;
      }
    }

    @media (max-width: 576px) {
      .permissions-container {
        padding: 10px;
      }

      .table th,
      .table td {
        padding: 8px;
        font-size: 12px;
      }

      .table td:first-child {
        min-width: 100px;
      }

      .permission-item {
        padding: 8px;
      }

      .permission-sub-items {
        margin-right: 15px;
      }

      .permissions-card-header {
        font-size: 14px;
        padding: 10px 12px;
      }
    }
  </style>
@endsection

@section('site-header', 'به نوبه | پنل دکتر')
@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی‌ها')

<div class="permissions-container">
  <div class="permissions-card">
    <div class="permissions-card-header">
      مدیریت دسترسی‌های منشی‌ها
    </div>
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
            @forelse ($secretaries as $secretary)
           @php
                $permission = $secretary->permissions->firstWhere('clinic_id', $secretary->clinic_id);
                $savedPermissions = $permission ? json_decode($permission->permissions ?? '[]', true) : [];
                $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
              @endphp
              <tr>
                <td>{{ $secretary->first_name }} {{ $secretary->last_name }}</td>
                <td>
                  <div class="permissions-list">
                    <div class="permissions-grid">
                      @foreach ($permissions as $permissionKey => $permissionData)
                        <div class="permission-item">
                          <div class="d-flex align-items-center">
                            <input type="checkbox"
                              class="form-check-input parent-permission update-permissions substituted"
                              data-secretary-id="{{ $secretary->id }}" value="{{ $permissionKey }}"
                              {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold mx-1">{{ $permissionData['title'] }}</label>
                          </div>
                          @if (!empty($permissionData['routes']))
                            <div class="permission-sub-items">
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
                        </div>
                      @endforeach
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="2" class="text-center">منشی‌ای ثبت نشده است</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>
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

      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });

  $(document).ready(function() {
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
      $(this).closest('td').find(`.child-permission[data-parent="${parentKey}"]`).prop('checked', isChecked);
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
