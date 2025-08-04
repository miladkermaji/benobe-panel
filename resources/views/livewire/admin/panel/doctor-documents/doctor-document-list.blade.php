<div class="doctor-notes-container">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadDocuments">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مدیریت مدارک پزشکان</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center w-100 flex-column flex-md-row">
              <!-- جستجو -->
              <div class="search-container position-relative flex-grow-1 mb-2 mb-md-0 w-100">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start w-100"
                  wire:model.live="search" placeholder="جستجو بر اساس نام پزشک یا عنوان مدرک..."
                  style="padding-right: 20px; text-align: right; direction: rtl; width: 100%; max-width: 400px; min-width: 200px;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <!-- فیلتر وضعیت -->
              <select class="form-select form-select-sm w-100 mb-2 mb-md-0 h-50" style="min-width: 0;"
                wire:model.live="statusFilter">
                <option value="">همه وضعیت‌ها</option>
                <option value="verified">فقط تأیید شده</option>
                <option value="unverified">فقط تأیید نشده</option>
              </select>
              <!-- دکمه افزودن مدرک -->
              <a href="{{ route('admin.panel.doctor-documents.create') }}"
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 w-100 w-md-auto justify-content-center justify-content-md-start h-50">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن مدرک</span>
              </a>
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
            x-show="$wire.selectedDocuments.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="verify">تأیید کردن</option>
                <option value="unverify">لغو تأیید</option>
              </select>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered" wire:model="applyToAllFiltered">
                <label class="form-check-label" for="applyToAllFiltered">
                  اعمال روی همه نتایج فیلترشده ({{ $totalFilteredCount ?? 0 }})
                </label>
              </div>
              <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
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
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام پزشک</th>
                  <th class="align-middle">عنوان مدرک</th>
                  <th class="align-middle">نوع فایل</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت تأیید</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                  <th class="text-center align-middle" style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @php
                    $grouped = collect($documents->items())->groupBy(function ($item) {
                        if (
                            is_object($item) &&
                            isset($item->doctor) &&
                            is_object($item->doctor) &&
                            isset($item->doctor->id)
                        ) {
                            return $item->doctor->id;
                        }
                        return 'بدون پزشک';
                    });
                    $rowIndex = 0;
                  @endphp
                  @forelse ($grouped as $doctorId => $doctorDocuments)
              <tbody x-data="{ open: false }">
                <tr style="background: #f5f7fa; border-top: 2px solid #b3c2d1; cursor:pointer;" @click="open = !open">
                  <td colspan="7" class="py-2 px-3 fw-bold text-primary" style="font-size: 1.05rem;">
                    <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                      style="vertical-align: middle; margin-left: 6px;">
                      <circle cx="9" cy="9" r="8" />
                      <path d="M9 5v4l3 2" />
                    </svg>
                    @php
                      $firstDoc = collect($doctorDocuments)->first(function ($item) {
                          return isset($item->doctor) && is_object($item->doctor);
                      });
                    @endphp
                    @if ($firstDoc)
                      {{ $firstDoc->doctor->first_name . ' ' . $firstDoc->doctor->last_name }}
                    @else
                      بدون پزشک
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
                @foreach ($doctorDocuments as $document)
                  @if (isset($document->doctor) && is_object($document->doctor))
                    <tr x-show="open" x-transition>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedDocuments" value="{{ $document->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $documents->firstItem() + $rowIndex }}</td>
                      <td class="align-middle">
                        {{ $document->doctor->first_name . ' ' . $document->doctor->last_name }}</td>
                      <td class="align-middle">{{ $document->title ?? 'بدون عنوان' }}</td>
                      <td class="align-middle">{{ $document->file_type }}</td>
                      <td class="text-center align-middle">
                        <button wire:click="confirmToggleVerified({{ $document->id }})"
                          class="badge {{ $document->is_verified ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                          {{ $document->is_verified ? 'تأیید شده' : 'تأیید نشده' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <button
                            wire:click="$dispatch('showPreview', { path: '{{ route('preview.document', basename($document->file_path)) }}', type: '{{ $document->file_type }}' })"
                            class="btn btn-info text-white rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                              <circle cx="12" cy="12" r="3" />
                            </svg>
                          </button>
                          <a href="{{ route('admin.panel.doctor-documents.edit', $document->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $document->id }})"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                      <td class="text-center align-middle"></td>
                    </tr>
                    @php $rowIndex++; @endphp
                  @endif
                @endforeach
              </tbody>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ مدرکی یافت نشد.</p>
                  </div>
                </td>
              </tr>
              @endforelse
            @else
              <tr>
                <td colspan="7" class="text-center py-4">
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
              @php
                $grouped = collect($documents->items())->groupBy(function ($item) {
                    if (
                        is_object($item) &&
                        isset($item->doctor) &&
                        is_object($item->doctor) &&
                        isset($item->doctor->id)
                    ) {
                        return $item->doctor->id;
                    }
                    return 'بدون پزشک';
                });
              @endphp
              @forelse ($grouped as $doctorId => $doctorDocuments)
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
                      @php
                        $firstDoc = collect($doctorDocuments)->first(function ($item) {
                            return isset($item->doctor) && is_object($item->doctor);
                        });
                      @endphp
                      @if ($firstDoc)
                        {{ $firstDoc->doctor->first_name . ' ' . $firstDoc->doctor->last_name }}
                      @else
                        بدون پزشک
                      @endif
                    </span>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div x-show="open" x-transition>
                    @foreach ($doctorDocuments as $document)
                      @if (isset($document->doctor) && is_object($document->doctor))
                        <div class="note-card mb-2">
                          <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2">
                            <span class="fw-bold">
                              {{ $document->title ?? 'بدون عنوان' }}
                              <span
                                class="text-muted">({{ $document->doctor->first_name . ' ' . $document->doctor->last_name }})</span>
                            </span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                              <path d="M6 9l6 6 6-6" />
                            </svg>
                          </div>
                          <div class="note-card-body px-2 py-2">
                            <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="note-card-label">نام پزشک:</span>
                              <span
                                class="note-card-value">{{ $document->doctor->first_name . ' ' . $document->doctor->last_name }}</span>
                            </div>
                            <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="note-card-label">عنوان مدرک:</span>
                              <span class="note-card-value">{{ $document->title ?? 'بدون عنوان' }}</span>
                            </div>
                            <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="note-card-label">نوع فایل:</span>
                              <span class="note-card-value">{{ $document->file_type }}</span>
                            </div>
                            <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="note-card-label">وضعیت تأیید:</span>
                              <button wire:click="confirmToggleVerified({{ $document->id }})"
                                class="badge {{ $document->is_verified ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">{{ $document->is_verified ? 'تأیید شده' : 'تأیید نشده' }}</button>
                            </div>
                            <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="note-card-label">عملیات:</span>
                              <div class="d-flex gap-2">
                                <button
                                  wire:click="$dispatch('showPreview', { path: '{{ route('preview.document', basename($document->file_path)) }}', type: '{{ $document->file_type }}' })"
                                  class="btn btn-info text-white rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                  </svg>
                                </button>
                                <a href="{{ route('admin.panel.doctor-documents.edit', $document->id) }}"
                                  class="btn btn-gradient-primary rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $document->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ مدرکی یافت نشد.</p>
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
          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center px-4 flex-wrap gap-3">
            <div class="text-muted">نمایش {{ $documents ? $documents->firstItem() : 0 }} تا
              {{ $documents ? $documents->lastItem() : 0 }} از {{ $documents ? $documents->total() : 0 }} ردیف
            </div>
            @if ($documents && $documents->hasPages())
              <div class="pagination-container">
                {{ $documents->onEachSide(1)->links('livewire::bootstrap') }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg border-0" style="background: #ffffff;">
          <div class="modal-header bg-gradient-primary text-white p-3 rounded-top-3">
            <h5 class="modal-title fw-bold text-dark text-shadow text-dark" id="previewModalLabel">پیش‌نمایش مدرک</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 text-center text-dark" id="previewContent"></div>
          <div class="modal-footer p-3">
            <a href="#" id="downloadLink" class="btn btn-gradient-success rounded-pill px-4"
              download>دانلود</a>
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">بستن</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });

        Livewire.on('confirm-delete', (event) => {
          Swal.fire({
            title: 'حذف مدرک',
            text: 'آیا مطمئن هستید که می‌خواهید این مدرک را حذف کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteDoctorDocumentConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-toggle-verified', (event) => {
          Swal.fire({
            title: event.action + ' مدرک',
            text: 'آیا مطمئن هستید که می‌خواهید وضعیت تأیید مدرک ' + event.name + ' را ' + event.action +
              ' کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1deb3c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('toggleVerifiedConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-delete-selected', function(data) {
          let text = data.allFiltered ?
            'آیا از حذف همه مدارک فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
            'آیا از حذف مدارک انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
          Swal.fire({
            title: 'تایید حذف گروهی',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'لغو',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              if (data.allFiltered) {
                Livewire.dispatch('deleteSelectedConfirmed', 'allFiltered');
              } else {
                Livewire.dispatch('deleteSelectedConfirmed');
              }
            }
          });
        });

        Livewire.on('showPreview', (event) => {
          if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            const content = document.getElementById('previewContent');
            const downloadLink = document.getElementById('downloadLink');
            content.innerHTML = '';

            const fileType = event.type.toLowerCase().split('/').pop();
            const imageTypes = ['jpg', 'jpeg', 'png'];
            const documentTypes = ['doc', 'docx'];

            if (imageTypes.includes(fileType)) {
              content.innerHTML =
                `<img src="${event.path}" class="img-fluid rounded-3" style="max-height: 500px;" alt="پیش‌نمایش مدرک">`;
            } else if (fileType === 'pdf') {
              content.innerHTML =
                `<iframe src="${event.path}" class="w-100 rounded-3" style="height: 500px;"></iframe>`;
            } else if (documentTypes.includes(fileType)) {
              const viewerUrl =
                `https://docs.google.com/viewer?url=${encodeURIComponent(event.path)}&embedded=true`;
              content.innerHTML =
                `<iframe src="${viewerUrl}" class="w-100 rounded-3" style="height: 500px;"></iframe>`;
            } else {
              content.innerHTML = `<p class="text-muted">نوع فایل ناشناخته است: ${event.type}.</p>`;
            }

            downloadLink.href = event.path;
            downloadLink.download = `document.${fileType}`;

            modal.show();
          }
        });
      });
    </script>
  </div>
</div>
