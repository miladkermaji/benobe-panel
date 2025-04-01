<div class="container-fluid py-2" dir="rtl" wire:init="loadDocuments">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت مدارک پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در پزشکان یا مدارک..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3"
        style="z-index: 5; top: 11px; right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.doctor-documents.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن مدرک</span>
      </a>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedDocuments)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $doctor)
            <div class="doctor-toggle border-bottom">
              <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                wire:click="toggleDoctor({{ $doctor->id }})">
                <div class="d-flex align-items-center gap-3">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  <span class="fw-bold">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                  <span class="badge bg-label-primary">{{ $doctor->documents->count() }} مدرک</span>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                  class="transition-transform {{ in_array($doctor->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($doctor->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-3 bg-light">
                  <table class="table table-bordered table-hover w-100 m-0">
                    <thead class="glass-header text-white">
                      <tr>
                        <th class="text-center align-middle" style="width: 50px;">
                          <input type="checkbox" wire:model.live="selectAll.{{ $doctor->id }}"
                            class="form-check-input m-0">
                        </th>
                        <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                        <th class="align-middle">عنوان مدرک</th>
                        <th class="align-middle">نوع فایل</th>
                        <th class="text-center align-middle" style="width: 100px;">وضعیت تأیید</th>
                        <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($doctor->documents as $index => $document)
                        <tr>
                          <td class="text-center align-middle">
                            <input type="checkbox" wire:model.live="selectedDocuments" value="{{ $document->id }}"
                              class="form-check-input m-0">
                          </td>
                          <td class="text-center align-middle">{{ $index + 1 }}</td>
                          <td class="align-middle">{{ $document->title ?? 'بدون عنوان' }}</td>
                          <td class="align-middle">{{ $document->file_type }}</td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleVerified({{ $document->id }})"
                              class="badge {{ $document->is_verified ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                              {{ $document->is_verified ? 'تأیید شده' : 'تأیید نشده' }}
                            </button>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <button
                                wire:click="$dispatch('showPreview', { path: '{{ route('preview.document', basename($document->file_path)) }}', type: '{{ $document->file_type }}' })"
                                class="btn btn-gradient-info rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                  <circle cx="12" cy="12" r="3" />
                                </svg>
                              </button>
                              <a href="{{ route('admin.panel.doctor-documents.edit', $document->id) }}"
                                class="btn btn-gradient-success rounded-pill px-3">
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
                        </tr>
                      @empty
                        <tr>
                          <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-muted fw-medium m-0">هیچ مدرکی یافت نشد.</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          @empty
            <div class="text-center py-5">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-muted mb-3">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-muted fw-medium m-0">هیچ پزشک یا مدرکی یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-5">در حال بارگذاری پزشکان و مدارک...</div>
        @endif
      </div>
    </div>
  </div>

  <!-- مودال پیش‌نمایش -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-3 shadow-lg border-0" style="background: #ffffff;">
        <div class="modal-header bg-gradient-primary text-white p-3 rounded-top-3">
          <h5 class="modal-title fw-bold text-shadow" id="previewModalLabel">پیش‌نمایش مدرک</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 text-center" id="previewContent">
          <!-- محتوای پیش‌نمایش اینجا به صورت دینامیک پر می‌شه -->
        </div>
        <div class="modal-footer p-3">
          <a href="#" id="downloadLink" class="btn btn-gradient-success rounded-pill px-4" download>دانلود</a>
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">بستن</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .glass-header {
      background: linear-gradient(90deg, rgba(107, 114, 128, 0.9), rgba(55, 65, 81, 0.9));
      backdrop-filter: blur(10px);
    }

    .btn-gradient-success {
      background: linear-gradient(90deg, #10b981, #059669);
      color: white;
    }

    .btn-gradient-danger {
      background: linear-gradient(90deg, #ef4444, #dc2626);
      color: white;
    }

    .btn-gradient-info {
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      color: white;
    }

    .btn-gradient-info:hover {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
    }

    .doctor-toggle {
      transition: all 0.3s ease;
    }

    .doctor-toggle:hover {
      background: #f9fafb;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    .transition-transform {
      transition: transform 0.3s ease;
    }

    .rotate-180 {
      transform: rotate(180deg);
    }

    .bg-label-primary {
      background: #e5e7eb;
      color: #374151;
    }

    .bg-label-success {
      background: #d1fae5;
      color: #059669;
    }

    .bg-label-danger {
      background: #fee2e2;
      color: #dc2626;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
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

      Livewire.on('showPreview', (event) => {
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
          // استفاده از Google Docs Viewer برای داکیومنت‌ها
          const viewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(event.path)}&embedded=true`;
          content.innerHTML =
            `<iframe src="${viewerUrl}" class="w-100 rounded-3" style="height: 500px;"></iframe>`;
        } else {
          content.innerHTML = `<p class="text-muted">نوع فایل ناشناخته است: ${event.type}.</p>`;
        }

        downloadLink.href = event.path;
        downloadLink.download = `document.${fileType}`;

        modal.show();
      });
    });
  </script>
</div>
