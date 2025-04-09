<div class="container-fluid py-2" dir="rtl" wire:init="loadDoctorServices">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت خدمات پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در خدمات پزشکان..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.doctor-services.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن خدمت پزشک</span>
      </a>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $data)
            <div class="doctor-toggle border-bottom">
              <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                wire:click="toggleDoctor({{ $data['doctor']->id }})">
                <div class="d-flex align-items-center gap-3">
                  <img src="{{ $data['doctor']->profile_photo_url }}" class="rounded-circle"
                    style="width: 40px; height: 40px; object-fit: cover;" alt="پروفایل پزشک">
                  <span class="fw-bold">{{ $data['doctor']->first_name . ' ' . $data['doctor']->last_name }}</span>
                  <span class="badge bg-label-primary">{{ $data['totalServices'] }} خدمت</span>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                  class="transition-transform {{ in_array($data['doctor']->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($data['doctor']->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-3 bg-light">
                  <table class="table table-bordered table-hover w-100 m-0">
                    <thead class="glass-header text-white">
                      <tr>
                        <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                        <th class="align-middle">نام خدمت</th>
                        <th class="align-middle">توضیحات</th>
                        <th class="align-middle">مدت زمان</th>
                        <th class="align-middle">قیمت</th>
                        <th class="align-middle">تخفیف</th>
                        <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                        <th class="align-middle">مادر</th>
                        <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($data['services'] as $index => $item)
                        <tr>
                          <td class="text-center align-middle">
                            {{ ($data['currentPage'] - 1) * $servicesPerPage + $index + 1 }}</td>
                          <td class="align-middle">{{ $item->name }}</td>
                          <td class="align-middle">{{ $item->description }}</td>
                          <td class="align-middle">{{ $item->duration ? $item->duration . ' دقیقه' : '-' }}</td>
                          <td class="align-middle">{{ $item->price ? number_format($item->price) : '-' }}</td>
                          <td class="align-middle">{{ $item->discount ? $item->discount . '%' : '-' }}</td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleStatus({{ $item->id }})"
                              class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                              {{ $item->status ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </td>
                          <td class="align-middle">{{ $item->parent->name ?? '-' }}</td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.doctor-services.edit', $item->id) }}"
                                class="btn btn-gradient-success rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $item->id }})"
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
                          <td colspan="9" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-muted fw-medium m-0">هیچ خدمتی یافت نشد.</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>

                  <!-- پیجینیشن محلی برای خدمات -->
                  @if ($data['totalServices'] > $servicesPerPage)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <div>
                        نمایش {{ ($data['currentPage'] - 1) * $servicesPerPage + 1 }} تا
                        {{ min($data['currentPage'] * $servicesPerPage, $data['totalServices']) }} از
                        {{ $data['totalServices'] }} خدمت
                      </div>
                      <nav>
                        <ul class="pagination mb-0">
                          <li class="page-item {{ $data['currentPage'] == 1 ? 'disabled' : '' }}">
                            <button class="page-link"
                              wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $data['currentPage'] - 1 }})">قبلی</button>
                          </li>
                          @for ($i = 1; $i <= $data['lastPage']; $i++)
                            <li class="page-item {{ $data['currentPage'] == $i ? 'active' : '' }}">
                              <button class="page-link"
                                wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $i }})">{{ $i }}</button>
                            </li>
                          @endfor
                          <li class="page-item {{ $data['currentPage'] == $data['lastPage'] ? 'disabled' : '' }}">
                            <button class="page-link"
                              wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $data['currentPage'] + 1 }})">بعدی</button>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  @endif
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
                <p class="text-muted fw-medium m-0">هیچ خدمتی یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-5">در حال بارگذاری خدمات پزشکان...</div>
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
          title: 'حذف خدمت پزشک',
          text: 'آیا مطمئن هستید که می‌خواهید این خدمت پزشک را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteDoctorServiceConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>
