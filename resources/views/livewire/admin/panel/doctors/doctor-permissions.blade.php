<div class="container-fluid py-4" dir="rtl">
  <div class="glass-header p-4  mb-6 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-4">
    <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px; color: var(--text-primary);">دسترسی‌های پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
      <input type="text"
        class="form-control border-0 shadow-none bg-background-card text-text-primary ps-5 rounded-full h-12"
        wire:model.live="search" placeholder="جستجو در پزشکان...">
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
        <div class="table-responsive text-nowrap d-none d-md-block">
          <table class="table w-100 m-0">
            <tbody>
              @forelse ($doctors as $doctor)
            <tbody x-data="{ open: false }">
              <tr style="background: #f5f7fa;  cursor:pointer;" @click="open = !open">
                <td class="py-2 px-3 fw-bold text-primary" style="font-size: 1.05rem;">
                  <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                    style="vertical-align: middle; margin-left: 6px;">
                    <circle cx="9" cy="9" r="8" />
                    <path d="M9 5v4l3 2" />
                  </svg>
                  {{ $doctor->full_name }} @if ($doctor->national_code)
                    ({{ $doctor->national_code }})
                  @endif
                </td>
                <td class="text-center align-middle" style="width: 40px; padding: 0;">
                  <span class="d-flex justify-content-center align-items-center w-100 h-100 p-0 m-0">
                    <svg width="20" height="20" fill="none" stroke="#0d6efd" stroke-width="2"
                      :style="open ? 'display: block; transition: transform 0.2s; transform: rotate(180deg);' :
                          'display: block; transition: transform 0.2s;'">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </span>
                </td>
              </tr>
              <tr x-show="open" x-transition>
                <td class="align-middle" colspan="2">
                  <div class="permissions-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                      $permission = $doctor->permissions;
                      $savedPermissions = $permission ? $permission->permissions ?? [] : [];
                      $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
                    @endphp
                    @foreach ($permissionsConfig as $permissionKey => $permissionData)
                      <div class="permission-item p-3 shadow-md bg-background-card transition-all duration-300 mb-2">
                        <div class="d-flex align-items-center mb-2">
                          <label class="custom-checkbox flex items-center gap-2">
                            <input type="checkbox" class="custom-checkbox-input parent-checkbox form-check-input"
                              data-doctor-id="{{ $doctor->id }}" data-permission-key="{{ $permissionKey }}"
                              id="parent-{{ $doctor->id }}-{{ $permissionKey }}"
                              wire:change="updatePermissions({{ $doctor->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$permissionKey], array_keys($permissionData['routes'] ?? []))) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $permissionKey && !in_array($p, array_keys($permissionData['routes'] ?? [])))) }})"
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
                                  <input type="checkbox" class="custom-checkbox-input child-checkbox form-check-input"
                                    data-parent-id="parent-{{ $doctor->id }}-{{ $permissionKey }}"
                                    id="child-{{ $doctor->id }}-{{ $routeKey }}"
                                    wire:change="updatePermissions({{ $doctor->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$routeKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $routeKey)) }})"
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
                </td>
              </tr>
            </tbody>
          @empty
            <tr>
              <td colspan="2" class="text-center py-4">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
                    stroke-width="2" class="mb-3 custom-animate-bounce">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-text-secondary font-medium m-0">پزشکی مطابق جستجو یافت نشد.</p>
                </div>
              </td>
            </tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <!-- Mobile Card View (original UI) -->
        <div class="d-md-none">
          @forelse ($doctors as $doctor)
            <div class="mb-3 p-2 rounded-3 shadow-sm" x-data="{ open: false }"
              style="border: 2px solid #b3c2d1; background: #f5f7fa;">
              <div class="fw-bold text-primary mb-2 d-flex align-items-center justify-content-between"
                style="font-size: 1.08rem; cursor:pointer;" @click="open = !open">
                <span>
                  <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                    style="vertical-align: middle; margin-left: 6px;">
                    <circle cx="9" cy="9" r="8" />
                    <path d="M9 5v4l3 2" />
                  </svg>
                  {{ $doctor->full_name }} @if ($doctor->national_code)
                    ({{ $doctor->national_code }})
                  @endif
                </span>
                <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24" fill="none"
                  stroke="#0d6efd" stroke-width="2" style="transition: transform 0.2s;">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>
              <div x-show="open" x-transition>
                <div class="permissions-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  @php
                    $permission = $doctor->permissions;
                    $savedPermissions = $permission ? $permission->permissions ?? [] : [];
                    $savedPermissions = is_array($savedPermissions) ? $savedPermissions : [];
                  @endphp
                  @foreach ($permissionsConfig as $permissionKey => $permissionData)
                    <div class="permission-item p-3 shadow-md bg-background-card transition-all duration-300 mb-2">
                      <div class="d-flex align-items-center mb-2">
                        <label class="custom-checkbox flex items-center gap-2">
                          <input type="checkbox" class="custom-checkbox-input parent-checkbox form-check-input"
                            data-doctor-id="{{ $doctor->id }}" data-permission-key="{{ $permissionKey }}"
                            id="parent-{{ $doctor->id }}-{{ $permissionKey }}"
                            wire:change="updatePermissions({{ $doctor->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$permissionKey], array_keys($permissionData['routes'] ?? []))) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $permissionKey && !in_array($p, array_keys($permissionData['routes'] ?? [])))) }})"
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
                                <input type="checkbox" class="custom-checkbox-input child-checkbox form-check-input"
                                  data-parent-id="parent-{{ $doctor->id }}-{{ $permissionKey }}"
                                  id="child-{{ $doctor->id }}-{{ $routeKey }}"
                                  wire:change="updatePermissions({{ $doctor->id }}, $event.target.checked ? {{ json_encode(array_merge($savedPermissions, [$routeKey])) }} : {{ json_encode(array_filter($savedPermissions, fn($p) => $p !== $routeKey)) }})"
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
            </div>
          @empty
            <div class="text-center py-6">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                  stroke="var(--text-secondary)" stroke-width="2" class="mb-3 custom-animate-bounce">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-text-secondary font-medium m-0">پزشکی مطابق جستجو یافت نشد.</p>
              </div>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // اضافه کردن event listener برای تیک‌های پدر
      document.addEventListener('change', function(e) {
        if (e.target.classList.contains('parent-checkbox')) {
          const parentId = e.target.id;
          const childCheckboxes = document.querySelectorAll(`.child-checkbox[data-parent-id="${parentId}"]`);

          // Update UI
          childCheckboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
          });
        }
      });
    });
  </script>
</div>
