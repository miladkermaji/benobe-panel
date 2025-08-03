@extends('mc.panel.layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/panel.css') }}">

  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/secretary_options/secretary_option.css') }}">

@endsection

@section('site-header', 'به نوبه | پنل دکتر')
@section('content')
@section('bread-crumb-title', 'مدیریت دسترسی‌ها')

<div class="container-fluid" dir="rtl">
  <div class="glass-header text-white mb-4 shadow d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1">مدیریت دسترسی‌های منشی‌ها</h1>
  </div>

  <div class="card shadow-sm rounded-3">
    <div class="card-body p-0">
      @forelse ($secretaries as $secretary)
        @php
          $permission = $secretary->permissions->firstWhere('medical_center_id', $secretary->medical_center_id);
          $savedPermissions = $permission ? json_decode($permission->permissions ?? '[]', true) : [];
          $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
        @endphp
        <div class="secretary-toggle border-bottom">
          <div class="d-flex justify-content-between align-items-center cursor-pointer toggle-header"
            data-secretary-id="{{ $secretary->id }}">
            <div class="d-flex align-items-center gap-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
                stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <span class="fw-bold" style="color: var(--text-primary);">{{ $secretary->first_name }}
                {{ $secretary->last_name }}</span>
              <span class="badge bg-label-primary">{{ $secretary->medicalCenter->name ?? 'ویزیت آنلاین' }}</span>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
              stroke-width="2" class="transition-transform toggle-icon">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </div>

          <div class="permissions-content bg-light d-none">
            <div class="permissions-grid">
              @foreach ($permissions as $permissionKey => $permissionData)
                <div class="permission-item rounded">
                  <div class="form-check">
                    <input class="form-check-input parent-permission update-permissions" type="checkbox"
                      id="perm-{{ $secretary->id }}-{{ $permissionKey }}" data-secretary-id="{{ $secretary->id }}"
                      value="{{ $permissionKey }}" {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="perm-{{ $secretary->id }}-{{ $permissionKey }}"
                      style="color: var(--text-primary);">
                      {{ $permissionData['title'] }}
                    </label>
                  </div>
                  @if (!empty($permissionData['routes']))
                    <div class="permission-sub-items">
                      @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                        <div class="form-check">
                          <input class="form-check-input child-permission update-permissions" type="checkbox"
                            id="perm-{{ $secretary->id }}-{{ $routeKey }}"
                            data-secretary-id="{{ $secretary->id }}" data-parent="{{ $permissionKey }}"
                            value="{{ $routeKey }}" {{ in_array($routeKey, $savedPermissions) ? 'checked' : '' }}>
                          <label class="form-check-label" for="perm-{{ $secretary->id }}-{{ $routeKey }}"
                            style="color: var(--text-secondary);">
                            {{ $routeTitle }}
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
        <div class="text-center py-4">
          <div class="d-flex flex-column align-items-center justify-content-center">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
              stroke-width="2" class="mb-2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
            <p class="text-muted fw-medium m-0" style="color: var(--text-secondary);">منشی‌ای ثبت نشده است.</p>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script>
  $(document).ready(function() {
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
      @if (Auth::guard('medical_center')->check())
        let selectedClinicId = '{{ Auth::guard('medical_center')->id() }}';
      @else
        let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
      @endif

      $('input[data-secretary-id="' + secretaryId + '"]:checked').each(function() {
        permissions.push($(this).val());
      });

      $.ajax({
        url: "{{ route('mc-secretary-permissions-update', ':id') }}".replace(':id', secretaryId),
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
