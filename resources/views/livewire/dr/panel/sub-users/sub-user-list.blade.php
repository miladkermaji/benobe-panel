<div class="doctor-clinics-container">
  <div class="container py-2" dir="rtl" wire:init="loadSubUsers">
    <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">کاربران زیرمجموعه من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  wire:model.live="search" placeholder="جستجو در کاربران..."
                  style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <button
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 add-subuser-btn"
                id="add-subuser-btn" type="button" data-bs-toggle="modal" data-bs-target="#addSubUserModal">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام و نام خانوادگی</th>
                  <th class="align-middle">شماره موبایل</th>
                  <th class="align-middle">کدملی</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($subUsers as $index => $subUser)
                    <tr class="align-middle">
                      <td class="text-center">{{ $subUsers->firstItem() + $index }}</td>
                      <td>{{ $subUser->subuserable->first_name ?? '' }} {{ $subUser->subuserable->last_name ?? '' }}
                      </td>
                      <td>{{ $subUser->subuserable->mobile ?? '' }}</td>
                      <td>{{ $subUser->subuserable->national_code ?? '' }}</td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          <button class="btn btn-sm btn-gradient-success px-2 py-1"
                            wire:click="editSubUser({{ $subUser->id }})" title="ویرایش" data-bs-toggle="modal"
                            data-bs-target="#editSubUserModal">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </button>
                          <button class="btn btn-sm btn-gradient-danger px-2 py-1"
                            wire:click="deleteSubUser({{ $subUser->id }})" title="حذف">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-muted mb-2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="5" class="text-center py-4">
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
              @forelse ($subUsers as $index => $subUser)
                <div class="note-card mb-3">
                  <div class="note-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary-subtle text-primary">
                        {{ $subUser->subuserable->national_code ?? '' }}
                      </span>
                    </div>
                    <div class="d-flex gap-1">
                      <button class="btn btn-sm btn-gradient-success px-2 py-1"
                        wire:click="editSubUser({{ $subUser->id }})" title="ویرایش" data-bs-toggle="modal"
                        data-bs-target="#editSubUserModal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </button>
                      <button class="btn btn-sm btn-gradient-danger px-2 py-1"
                        wire:click="deleteSubUser({{ $subUser->id }})" title="حذف">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div class="note-card-body">
                    <div class="note-card-item">
                      <span class="note-card-label">نام و نام خانوادگی:</span>
                      <span class="note-card-value">{{ $subUser->subuserable->first_name ?? '' }}
                        {{ $subUser->subuserable->last_name ?? '' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">شماره موبایل:</span>
                      <span class="note-card-value">{{ $subUser->subuserable->mobile ?? '' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">کدملی:</span>
                      <span class="note-card-value">{{ $subUser->subuserable->national_code ?? '' }}</span>
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
                    <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
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
            @if ($readyToLoad && $subUsers)
              <div class="text-muted">
                نمایش {{ $subUsers->firstItem() }} تا {{ $subUsers->lastItem() }}
                از {{ $subUsers->total() }} ردیف
              </div>
              @if ($subUsers->hasPages())
                {{ $subUsers->links('livewire::bootstrap') }}
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
