<div class="secretary-list-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadSecretaries">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">منشی‌های من</h1>
            <!-- Mobile Toggle Button -->
            <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
              @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
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
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-block">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
              <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
                <div class="search-container position-relative" style="max-width: 100%;">
                  <input type="text"
                    class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                    wire:model.live="search" placeholder="جستجو در منشی‌ها..."
                    style="padding-right: 20px; text-align: right; direction: rtl;">
                  <span class="search-icon position-absolute top-40 start-0 translate-middle-y ms-2"
                    style="z-index: 5; top: 50%; right: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                  </span>
                </div>
                <a href="{{ route('dr-secretary-create') }}"
                  class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center text-white">
                  <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  <span>افزودن</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedSecretaries.length > 0">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_active">فعال کردن</option>
                <option value="status_inactive">غیرفعال کردن</option>
              </select>
              <button class="btn btn-sm btn-primary" onclick="confirmGroupDelete(event)" wire:loading.attr="disabled">
                <span wire:loading.remove>اجرا</span>
                <span wire:loading>در حال اجرا...</span>
              </button>
            </div>
          </div>
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام و نام خانوادگی</th>
                  <th class="align-middle">شماره موبایل</th>
                  <th class="align-middle">کدملی</th>
                  <th class="align-middle">جنسیت</th>
                  <th class="align-middle">استان/شهر</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($secretaries as $index => $item)
                    <tr class="align-middle">
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                          <input type="checkbox" wire:model.live="selectedSecretaries" value="{{ $item->id }}"
                            class="form-check-input m-0 align-middle">
                        </div>
                      </td>
                      <td class="text-center">{{ $secretaries->firstItem() + $index }}</td>
                      <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                      <td>{{ $item->mobile }}</td>
                      <td>{{ $item->national_code }}</td>
                      <td>{{ $item->gender === 'male' ? 'مرد' : 'زن' }}</td>
                      <td>{{ optional($item->province)->name }}/{{ optional($item->city)->name }}</td>
                      <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center">
                          <input class="form-check-input" type="checkbox" role="switch"
                            wire:click="toggleStatus({{ $item->id }})" {{ $item->status ? 'checked' : '' }}
                            style="width: 3em; height: 1.5em; margin-top: 0;">
                        </div>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          <a href="{{ route('dr-secretary-edit', $item->id) }}"
                            class="btn btn-sm btn-gradient-success ">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <a href="{{ route('dr-secretary-permissions') }}" class="btn btn-sm btn-primary"
                            title="دسترسی‌ها">
                            <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="دسترسی‌ها">
                          </a>
                          <button wire:click="confirmDelete({{ $item->id }})"
                            class="btn btn-sm btn-gradient-danger ">
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
                      <td colspan="12" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ منشی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="12" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                      </div>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @if ($readyToLoad)
              @forelse ($secretaries as $index => $item)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedSecretaries" value="{{ $item->id }}"
                        class="form-check-input m-0 align-middle" @click.stop>
                      <span class="fw-bold">{{ $item->first_name }} {{ $item->last_name }}</span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">شماره موبایل:</span>
                      <span class="note-card-value">{{ $item->mobile }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">کدملی:</span>
                      <span class="note-card-value">{{ $item->national_code }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">جنسیت:</span>
                      <span class="note-card-value">{{ $item->gender === 'male' ? 'مرد' : 'زن' }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">استان/شهر:</span>
                      <span
                        class="note-card-value">{{ optional($item->province)->name }}/{{ optional($item->city)->name }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" role="switch"
                          wire:click="toggleStatus({{ $item->id }})" {{ $item->status ? 'checked' : '' }}
                          style="width: 3em; height: 1.5em; margin-top: 0;">
                      </div>
                    </div>
                    <div class="note-card-actions d-flex gap-1 mt-2 pt-2 border-top">
                      <a href="{{ route('dr-secretary-edit', $item->id) }}"
                        class="btn btn-sm btn-gradient-success px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <a href="{{ route('dr-secretary-permissions') }}" class="btn btn-sm btn-primary px-2 py-1"
                        title="دسترسی‌ها">
                        <img src="{{ asset('dr-assets/icons/emergency.svg') }}" alt="دسترسی‌ها"
                          style="width:16px;height:16px;">
                      </a>
                      <button wire:click="confirmDelete({{ $item->id }})"
                        class="btn btn-sm btn-gradient-danger px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ منشی یافت نشد.</p>
                  </div>
                </div>
              @endforelse
            @else
              <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
              </div>
            @endif
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3 px-3 flex-wrap gap-2">
            @if ($readyToLoad)
              <div class="text-muted">
                نمایش {{ $secretaries->firstItem() }} تا {{ $secretaries->lastItem() }}
                از {{ $secretaries->total() }} ردیف
              </div>
              @if ($secretaries->hasPages())
                {{ $secretaries->links('livewire::bootstrap') }}
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    window.confirmGroupDelete = function(e) {
      var groupAction = document.querySelector('[wire\\:model="groupAction"]').value;
      if (groupAction === 'delete') {
        e.preventDefault();
        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'آیا از حذف منشی‌های انتخاب شده اطمینان دارید؟',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'بله، حذف کن!',
          cancelButtonText: 'انصراف'
        }).then((result) => {
          if (result.isConfirmed) {
            window.Livewire.find(document.querySelector('[wire\\:model="groupAction"]').closest('[wire\\:id]')
              .getAttribute('wire:id')).executeGroupAction();
          }
        });
      } else {
        window.Livewire.find(document.querySelector('[wire\\:model="groupAction"]').closest('[wire\\:id]').getAttribute(
          'wire:id')).executeGroupAction();
      }
    }
  </script>
</div>
