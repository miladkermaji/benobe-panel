<div class="container-fluid py-4" dir="rtl" x-data="{ mobileSearchOpen: false }">
  <!-- Header -->
  <header class="glass-header text-white p-3 rounded-3  shadow-lg">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
      <!-- Title Section -->
      <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
        <h2 class="mb-0 fw-bold fs-5">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="header-icon">
            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          دسترسی‌های پزشکان
        </h2>
        <!-- Mobile Toggle Button -->
        <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
          @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </button>
      </div>
      <!-- Mobile Collapsible Section -->
      <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-none w-100">
        <div class="d-flex flex-column gap-2">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو در پزشکان...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $doctors->total() }}
          </span>
        </div>
      </div>
      <!-- Desktop Search and Actions -->
      <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
        <div class="search-box position-relative">
          <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو در پزشکان...">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="search-icon">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
        </div>
        <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
          {{ $doctors->total() }}
        </span>
      </div>
    </div>
  </header>

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
                  <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                    stroke="var(--text-secondary)" stroke-width="2" class="mb-3">
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
                <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                  fill="none" stroke="#0d6efd" stroke-width="2" style="transition: transform 0.2s;">
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
                  stroke="var(--text-secondary)" stroke-width="2" class="mb-3">
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
