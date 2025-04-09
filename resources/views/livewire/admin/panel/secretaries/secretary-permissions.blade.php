<div class="container-fluid py-2" dir="rtl">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت دسترسی‌های منشی‌ها</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3 px-4"
        wire:model.live="search" placeholder="جستجو در منشی‌ها یا کلینیک‌ها...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        @forelse ($doctors as $doctor)
          <div class="doctor-section border-bottom">
            <h4 class="p-3 bg-light fw-bold">{{ $doctor->full_name }}</h4>
            @forelse ($doctor->secretaries as $secretary)
              <div class="secretary-toggle border-bottom">
                <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                  wire:click="toggleSecretary({{ $secretary->id }})">
                  <div class="d-flex align-items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="fw-bold">{{ $secretary->first_name . ' ' . $secretary->last_name }}
                      ({{ $secretary->mobile }})
                    </span>
                    @if ($secretary->clinic)
                      <span class="badge bg-label-primary">{{ $secretary->clinic->name }}</span>
                    @else
                      <span class="badge bg-label-info">ویزیت آنلاین</span>
                    @endif
                  </div>
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2"
                    class="transition-transform {{ in_array($secretary->id, $expandedSecretaries) ? 'rotate-180' : '' }}">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>

                @if (in_array($secretary->id, $expandedSecretaries))
                  <div class="p-3 bg-light">
                    <div class="permissions-grid">
                      @php
                        $permission = $secretary->permissions->firstWhere('clinic_id', $secretary->clinic_id);
                        $savedPermissions = $permission ? json_decode($permission->permissions ?? '[]', true) : [];
                        $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
                        $clinicId = $secretary->clinic_id;
                      @endphp
                      @foreach ($permissionsConfig as $permissionKey => $permissionData)
                        <div class="permission-item p-2 rounded shadow-sm">
                          <div class="d-flex align-items-center mb-1">
                            <label class="custom-checkbox">
                              <input type="checkbox" class="custom-checkbox-input"
                                id="parent-{{ $secretary->id }}-{{ $permissionKey }}"
                                wire:change="updatePermissions({{ $secretary->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$permissionKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $permissionKey)) }}, {{ $clinicId ?? 'null' }})"
                                {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                              <span class="custom-checkbox-checkmark"></span>
                              <span class="custom-checkbox-label text-gray-700"
                                style="font-weight: 700; font-size: 14px;">
                                {{ $permissionData['title'] }}
                              </span>
                            </label>
                          </div>
                          @if (!empty($permissionData['routes']))
                            <div class="permission-sub-items ps-4 pe-2">
                              @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                                <div class="d-flex align-items-center mb-1">
                                  <label class="custom-checkbox">
                                    <input type="checkbox" class="custom-checkbox-input"
                                      id="child-{{ $secretary->id }}-{{ $routeKey }}"
                                      wire:change="updatePermissions({{ $secretary->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$routeKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $routeKey)) }}, {{ $clinicId ?? 'null' }})"
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
                @endif
              </div>
            @empty
              <div class="p-3 text-muted">منشی‌ای برای این دکتر ثبت نشده است.</div>
            @endforelse
          </div>
        @empty
          <div class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="text-muted mb-3">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <p class="text-muted fw-medium m-0">دکتری با منشی مطابق جستجو یافت نشد.</p>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <style>
    .glass-header {
      background: linear-gradient(90deg, rgba(107, 114, 128, 0.9), rgba(55, 65, 81, 0.9));
      backdrop-filter: blur(10px);
    }

    .card {
      background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    }

    .secretary-toggle {
      transition: all 0.25s ease;
    }

    .secretary-toggle:hover {
      background: #f9fafb;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    .transition-transform {
      transition: transform 0.25s ease;
    }

    .rotate-180 {
      transform: rotate(180deg);
    }

    .permissions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 10px;
    }

    .permission-item {
      border: 1px solid #d1d5db;
      border-radius: 6px;
      transition: all 0.25s ease;
    }

    .permission-item:hover {
      border-color: #9ca3af;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .text-gray-700 {
      color: #374151;
    }

    .text-gray-600 {
      color: #4b5563;
    }

    .bg-label-primary {
      background: #e5e7eb;
      color: #374151;
    }

    .bg-label-info {
      background: #dbeafe;
      color: #1e40af;
    }

    .gap-3 {
      gap: 0.75rem;
    }

    @media (max-width: 768px) {
      .permissions-grid {
        grid-template-columns: 1fr;
      }

      .glass-header {
        flex-direction: column;
        gap: 10px;
      }

      .input-group {
        width: 100%;
        max-width: none;
      }
    }

    .custom-checkbox {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .custom-checkbox-input {
      display: none;
    }

    .custom-checkbox-checkmark {
      width: 18px;
      height: 18px;
      background-color: #fff;
      border: 2px solid #6b7280;
      border-radius: 4px;
      position: relative;
      transition: all 0.2s ease;
    }

    .custom-checkbox-input:checked+.custom-checkbox-checkmark {
      background-color: #3B99FC;
      border-color: #3B99FC;
    }

    .custom-checkbox-input:checked+.custom-checkbox-checkmark:after {
      content: "";
      position: absolute;
      left: 5px;
      top: 2px;
      width: 5px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }

    .custom-checkbox-label {
      margin-right: 1rem;
    }

    .custom-checkbox:hover .custom-checkbox-checkmark {
      border-color: #9ca3af;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>
