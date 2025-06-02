<div class="container-fluid py-4" dir="rtl" wire:init="loadSecretaries">
  <div
    class="glass-header p-4 rounded-xl mb-6 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-4">
    <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px; color: var(--text-primary);">مدیریت منشی‌ها</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
      <input type="text"
        class="form-control border-0 shadow-none bg-background-card text-text-primary ps-5 rounded-full h-12"
        wire:model.live="search" placeholder="جستجو در منشی‌ها یا پزشکان...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-4 text-text-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-3 flex-shrink-0 flex-wrap justify-content-center mt-md-0 buttons-container">
      <a href="{{ route('admin.panel.secretaries.create') }}"
        class="btn btn-gradient-success rounded-full px-4 py-1.5 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all duration-300">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="font-medium text-sm">افزودن منشی</span>
      </a>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-xl rounded-2xl overflow-hidden bg-background-card">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $doctor)
            <div class="doctor-toggle border-bottom transition-all duration-300 hover:bg-background-light">
              <div class="d-flex justify-content-between align-items-center p-4 cursor-pointer"
                wire:click="toggleDoctor({{ $doctor->id }})">
                <div class="d-flex align-items-center gap-3">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  <span class="fw-bold text-text-primary text-lg">{{ $doctor->first_name . ' ' . $doctor->last_name }}
                    ({{ $doctor->mobile }})
                  </span>
                  <span
                    class="badge-comment bg-gradient-primary text-white font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">{{ $doctor->secretaries->count() }}
                    منشی</span>
                </div>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
                  stroke-width="2"
                  class="transition-transform {{ in_array($doctor->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($doctor->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-4 bg-background-light">
                  <table class="table table-modern w-100 m-0 desktop-table">
                    <thead class="table-header">
                      <tr>
                        <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                        <th class="align-middle">نام منشی</th>
                        <th class="align-middle">موبایل</th>
                        <th class="align-middle">کلینیک</th>
                        <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                        <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($doctor->secretaries as $index => $secretary)
                        <tr class="hover:bg-background-light transition-colors duration-200">
                          <td class="text-center align-middle font-medium text-text-secondary">{{ $index + 1 }}</td>
                          <td class="align-middle text-text-primary">
                            {{ $secretary->first_name . ' ' . $secretary->last_name }}</td>
                          <td class="align-middle text-text-secondary">{{ $secretary->mobile }}</td>
                          <td class="align-middle text-text-primary">
                            {{ $secretary->clinic ? $secretary->clinic->name : '-' }}</td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleStatus({{ $secretary->id }})"
                              class="badge {{ $secretary->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">
                              {{ $secretary->is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.secretaries.edit', $secretary->id) }}"
                                class="btn btn-gradient-success rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $secretary->id }})"
                                class="btn btn-gradient-danger rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                          </td>

                        </tr>
                      @empty
                        <tr>
                          <td colspan="6" class="text-center py-6">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                                stroke="var(--text-secondary)" stroke-width="2" class="mb-3 custom-animate-bounce">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-text-secondary font-medium m-0">هیچ منشی‌ای یافت نشد.</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  <!-- کارت‌ها برای موبایل و تبلت -->
                  <div class="mobile-cards d-block d-lg-none">
                    @forelse ($doctor->secretaries as $index => $secretary)
                      <div class="card mb-3 rounded-xl shadow-md bg-background-card">
                        <div class="card-body p-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-text-secondary font-medium text-sm">#{{ $index + 1 }}</span>
                            <button wire:click="toggleStatus({{ $secretary->id }})"
                              class="badge {{ $secretary->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">
                              {{ $secretary->is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </div>
                          <div class="mb-2">
                            <p class="text-text-primary mb-1 text-sm"><strong>نام منشی:</strong>
                              {{ $secretary->first_name . ' ' . $secretary->last_name }}</p>
                            <p class="text-text-secondary mb-1 text-sm"><strong>موبایل:</strong>
                              {{ $secretary->mobile }}</p>
                            <p class="text-text-primary mb-1 text-sm"><strong>کلینیک:</strong>
                              {{ $secretary->clinic ? $secretary->clinic->name : '-' }}</p>
                          </div>
                          <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.panel.secretaries.edit', $secretary->id) }}"
                              class="btn btn-gradient-success rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                              </svg>
                            </a>
                            <button wire:click="confirmDelete({{ $secretary->id }})"
                              class="btn btn-gradient-danger rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                              </svg>
                            </button>

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
                          <p class="text-text-secondary font-medium m-0">هیچ منشی‌ای یافت نشد.</p>
                        </div>
                      </div>
                    @endforelse
                  </div>
                </div>
              @endif
            </div>
          @empty
            <div class="text-center py-6">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                  stroke="var(--text-secondary)" stroke-width="2" class="mb-3 custom-animate-bounce">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-text-secondary font-medium m-0">هیچ پزشکی یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-6 text-text-secondary font-medium animate-pulse">در حال بارگذاری منشی‌ها...</div>
        @endif
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف منشی',
          text: 'آیا مطمئن هستید که می‌خواهید این منشی را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: 'var(--primary)',
          cancelButtonColor: 'var(--text-secondary)',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر',
          customClass: {
            popup: 'rounded-2xl shadow-xl',
            confirmButton: 'rounded-full px-6 py-2',
            cancelButton: 'rounded-full px-6 py-2'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteSecretary', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>
