<div class="container-fluid py-2" dir="rtl" wire:init="loadDoctorNotes">
  <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg d-flex align-items-center flex-wrap gap-2">
    <h1 class="m-0 h4 font-thin flex-grow-1" style="min-width: 150px;">مدیریت یادداشت‌های پزشک</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 300px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-4 rounded-2"
        wire:model.live="search" placeholder="جستجو در یادداشت‌ها..." style="padding-right: 20px;">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
        style="z-index: 5; top: 50%; right: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 justify-content-center flex-wrap buttons-container">
      <a href="{{ route('dr.panel.doctornotes.create') }}"
        class="btn btn-gradient-success rounded-pill px-3 py-1 d-flex align-items-center gap-1">
        <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن یادداشت</span>
      </a>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-3 py-1 d-flex align-items-center gap-1"
        @if (empty($selectedDoctorNotes)) disabled @endif>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm rounded-2">
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered table-hover w-100 m-0">
            <thead>
              <tr>
                <th class="text-center align-middle" style="width: 40px;">
                  <div class="d-flex justify-content-center align-items-center">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0">
                  </div>
                </th>
                <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                <th class="align-middle">نوع نوبت</th>
                <th class="align-middle">کلینیک</th>
                <th class="align-middle">یادداشت</th>
                <th class="text-center align-middle" style="width: 120px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($doctorNotes as $index => $item)
                  <tr>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" wire:model.live="selectedDoctorNotes" value="{{ $item->id }}"
                          class="form-check-input m-0">
                      </div>
                    </td>
                    <td class="text-center align-middle">{{ $doctorNotes->firstItem() + $index }}</td>
                    <td class="align-middle">
                      @switch($item->appointment_type)
                        @case('in_person')
                          حضوری
                        @break

                        @case('online_phone')
                          تلفنی
                        @break

                        @case('online_text')
                          متنی
                        @break

                        @case('online_video')
                          ویدیویی
                        @break
                      @endswitch
                    </td>
                    <td class="align-middle">{{ $item->clinic ? $item->clinic->name : 'ندارد' }}</td>
                    <td class="align-middle">{{ e($item->notes) ?? 'بدون یادداشت' }}</td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-1">
                        <a href="{{ route('dr.panel.doctornotes.edit', $item->id) }}"
                          class="btn btn-gradient-success rounded-pill px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $item->id }})"
                          class="btn btn-gradient-danger rounded-pill px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-muted mb-2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ یادداشتی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="6" class="text-center py-4">در حال بارگذاری یادداشت‌ها...</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards View -->
          <div class="notes-cards d-none">
            @if ($readyToLoad)
              @forelse ($doctorNotes as $index => $item)
                <div class="note-card">
                  <div class="note-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" wire:model.live="selectedDoctorNotes" value="{{ $item->id }}"
                          class="form-check-input m-0">
                        <span class="text-muted">#{{ $doctorNotes->firstItem() + $index }}</span>
                      </div>
                      <div class="d-flex gap-1">
                        <a href="{{ route('dr.panel.doctornotes.edit', $item->id) }}"
                          class="btn btn-gradient-success rounded-pill px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $item->id }})"
                          class="btn btn-gradient-danger rounded-pill px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="note-card-body">
                    <div class="note-card-item">
                      <span class="note-card-label">نوع نوبت:</span>
                      <span class="note-card-value">
                        @switch($item->appointment_type)
                          @case('in_person')
                            حضوری
                          @break

                          @case('online_phone')
                            تلفنی
                          @break

                          @case('online_text')
                            متنی
                          @break

                          @case('online_video')
                            ویدیویی
                          @break
                        @endswitch
                      </span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">کلینیک:</span>
                      <span class="note-card-value">{{ $item->clinic ? $item->clinic->name : 'ندارد' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">یادداشت:</span>
                      <span class="note-card-value">{{ e($item->notes) ?? 'بدون یادداشت' }}</span>
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
                      <p class="text-muted fw-medium">هیچ یادداشتی یافت نشد.</p>
                    </div>
                  </div>
                @endforelse
              @else
                <div class="text-center py-4">در حال بارگذاری یادداشت‌ها...</div>
              @endif
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 px-3 flex-wrap gap-2">
              @if ($readyToLoad)
                <div class="text-muted">
                  نمایش {{ $doctorNotes->firstItem() }} تا {{ $doctorNotes->lastItem() }}
                  از {{ $doctorNotes->total() }} ردیف
                </div>
                @if ($doctorNotes->hasPages())
                  {{ $doctorNotes->links('livewire::bootstrap') }}
                @endif
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
                title: 'حذف یادداشت',
                text: 'آیا مطمئن هستید که می‌خواهید این یادداشت را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'خیر'
              }).then((result) => {
                if (result.isConfirmed) {
                  Livewire.dispatch('deleteDoctorNoteConfirmed', {
                    id: event.id
                  });
                }
              });
            });
          });
        </script>
      </div>
    </div>
    </div>
