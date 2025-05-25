<div class="container-fluid py-4" dir="rtl">
  <div class="glass-header p-4 rounded-xl mb-6 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-4">
      <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px; color: var(--text-primary);">مدیریت دسترسی‌های منشی‌ها</h1>
      <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
          <input type="text" class="form-control border-0 shadow-none bg-background-card text-text-primary ps-5 rounded-full h-12"
              wire:model.live="search" placeholder="جستجو در منشی‌ها یا کلینیک‌ها...">
          <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-4 text-text-secondary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
              </svg>
          </span>
      </div>
  </div>

  <div class="container-fluid px-0">
      <div class="card shadow-xl rounded-2xl overflow-hidden bg-background-card">
          <div class="card-body p-0">
              @forelse ($doctors as $doctor)
                  <div class="doctor-section border-bottom transition-all duration-300 hover:bg-background-light">
                      <h5 class="p-4 bg-background-light fw-bold text-text-primary text-lg fw-bold">{{ $doctor->full_name }}</h5>
                      @forelse ($doctor->secretaries as $secretary)
                          <div class="secretary-toggle border-bottom">
                              <div class="d-flex justify-content-between align-items-center p-4 cursor-pointer"
                                  wire:click="toggleSecretary({{ $secretary->id }})">
                                  <div class="d-flex align-items-center gap-3">
                                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2">
                                          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                          <circle cx="12" cy="7" r="4" />
                                      </svg>
                                      <span class="fw-bold text-text-primary text-lg">{{ $secretary->first_name . ' ' . $secretary->last_name }} ({{ $secretary->mobile }})</span>
                                      @if ($secretary->clinic)
                                          <span class="badge-comment bg-gradient-primary text-white font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">{{ $secretary->clinic->name }}</span>
                                      @else
                                          <span class="badge-comment bg-gradient-info text-white font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">ویزیت آنلاین</span>
                                      @endif
                                  </div>
                                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2"
                                      class="transition-transform {{ in_array($secretary->id, $expandedSecretaries) ? 'rotate-180' : '' }}">
                                      <path d="M6 9l6 6 6-6" />
                                  </svg>
                              </div>

                              @if (in_array($secretary->id, $expandedSecretaries))
                                  <div class="p-4 bg-background-light">
                                      <div class="permissions-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                          @php
                                              $permission = $secretary->permissions->firstWhere('clinic_id', $secretary->clinic_id);
                                              $savedPermissions = $permission ? json_decode($permission->permissions ?? '[]', true) : [];
                                              $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
                                              $clinicId = $secretary->clinic_id;
                                          @endphp
                                          @foreach ($permissionsConfig as $permissionKey => $permissionData)
                                              <div class="permission-item p-3 rounded-xl shadow-md bg-background-card hover:bg-background-light transition-all duration-300">
                                                  <div class="d-flex align-items-center mb-2">
                                                      <label class="custom-checkbox flex items-center gap-2">
                                                          <input type="checkbox" class="custom-checkbox-input"
                                                              id="parent-{{ $secretary->id }}-{{ $permissionKey }}"
                                                              wire:change="updatePermissions({{ $secretary->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$permissionKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $permissionKey)) }}, {{ $clinicId ?? 'null' }})"
                                                              {{ in_array($permissionKey, $savedPermissions) ? 'checked' : '' }}>
                                                          <span class="custom-checkbox-checkmark"></span>
                                                          <span class="custom-checkbox-label text-text-primary font-bold text-sm">
                                                              {{ $permissionData['title'] }}
                                                          </span>
                                                      </label>
                                                  </div>
                                                  @if (!empty($permissionData['routes']))
                                                      <div class="permission-sub-items ps-4 pe-2">
                                                          @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                                                              <div class="d-flex align-items-center mb-2">
                                                                  <label class="custom-checkbox flex items-center gap-2">
                                                                      <input type="checkbox" class="custom-checkbox-input"
                                                                          id="child-{{ $secretary->id }}-{{ $routeKey }}"
                                                                          wire:change="updatePermissions({{ $secretary->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$routeKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $routeKey)) }}, {{ $clinicId ?? 'null' }})"
                                                                          {{ in_array($routeKey, $savedPermissions) ? 'checked' : '' }}>
                                                                      <span class="custom-checkbox-checkmark"></span>
                                                                      <span class="custom-checkbox-label text-text-secondary text-sm">
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
                          <div class="p-4 text-text-secondary text-center font-medium">منشی‌ای برای این دکتر ثبت نشده است.</div>
                      @endforelse
                  </div>
              @empty
                  <div class="text-center py-6">
                      <div class="d-flex flex-column align-items-center justify-content-center">
                          <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" class="mb-3 animate-bounce">
                              <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-text-secondary font-medium m-0">دکتری با منشی مطابق جستجو یافت نشد.</p>
                      </div>
                  </div>
              @endforelse
          </div>
      </div>
  </div>

  <script>
      document.addEventListener('livewire:init', function() {
          Livewire.on('show-alert', (event) => {
              toastr[event.type](event.message);
          });
      });
  </script>
</div>