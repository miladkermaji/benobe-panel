<div class="doctor-comments-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadDoctorComments">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 mb-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            نظرات پزشکان
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
              <input type="text" wire:model.live="search" class="form-control ps-5"
                placeholder="جستجو بر اساس نام پزشک یا نظر...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فقط فعال</option>
              <option value="inactive">فقط غیرفعال</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.doctor-comments.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن نظر</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $comments->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام پزشک یا نظر...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فقط فعال</option>
            <option value="inactive">فقط غیرفعال</option>
          </select>
          <a href="{{ route('admin.panel.doctor-comments.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن نظر</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $comments->total() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedDoctorComments.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب‌شده‌ها</option>
                <option value="activate">فعال کردن</option>
                <option value="deactivate">غیرفعال کردن</option>
              </select>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered"
                  wire:model="applyToAllFiltered">
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
                  <th class="align-middle">نام کاربر</th>
                  <th class="align-middle">شماره تماس</th>
                  <th class="align-middle">نظر</th>
                  <th class="align-middle">پاسخ</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 200px;">عملیات</th>
                  <th class="text-center align-middle" style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @php
                    $grouped = collect($comments->items())->groupBy(function ($item) {
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
                  @forelse ($grouped as $doctorId => $doctorComments)
              <tbody x-data="{ open: false }">
                <tr style="background: #f5f7fa; border-top: 2px solid #b3c2d1; cursor:pointer;" @click="open = !open">
                  <td colspan="9" class="py-2 px-3 fw-bold text-primary" style="font-size: 1.05rem;">
                    <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                      style="vertical-align: middle; margin-left: 6px;">
                      <circle cx="9" cy="9" r="8" />
                      <path d="M9 5v4l3 2" />
                    </svg>
                    @php
                      $firstComment = collect($doctorComments)->first(function ($item) {
                          return isset($item->doctor) && is_object($item->doctor);
                      });
                    @endphp
                    @if ($firstComment)
                      {{ $firstComment->doctor->first_name . ' ' . $firstComment->doctor->last_name }}
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
                @foreach ($doctorComments as $comment)
                  @if (isset($comment->doctor) && is_object($comment->doctor))
                    <tr x-show="open" x-transition style="border-bottom: 1px solid #e3e6ea; background: #fff;">
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $comments->firstItem() + $rowIndex }}</td>
                      <td class="align-middle">
                        {{ $comment->doctor->first_name . ' ' . $comment->doctor->last_name }}
                      </td>
                      <td class="align-middle">
                        @if ($comment->userable)
                          @if (property_exists($comment->userable, 'first_name'))
                            {{ $comment->userable->first_name . ' ' . $comment->userable->last_name }}
                          @elseif(property_exists($comment->userable, 'name'))
                            {{ $comment->userable->name }}
                          @else
                            {{ $comment->userable->id }}
                          @endif
                        @else
                          ---
                        @endif
                      </td>
                      <td class="align-middle">
                        @if ($comment->userable && property_exists($comment->userable, 'mobile'))
                          {{ $comment->userable->mobile }}
                        @else
                          ---
                        @endif
                      </td>
                      <td class="align-middle">{{ \Illuminate\Support\Str::limit($comment->comment, 50) }}</td>
                      <td class="align-middle">
                        {{ $comment->reply ? \Illuminate\Support\Str::limit($comment->reply, 50) : 'بدون پاسخ' }}
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="confirmToggleStatus({{ $comment->id }})"
                          class="badge {{ $comment->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                          {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="toggleReply({{ $comment->id }})"
                            class="btn btn-gradient-info rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                            </svg>
                          </button>
                          <button wire:click="confirmDelete({{ $comment->id }})"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                        @if ($replyingTo === $comment->id)
                          <div class="mt-3 p-3 bg-light rounded-2">
                            <textarea wire:model.live="replyText.{{ $comment->id }}" class="form-control mb-2" rows="3"
                              placeholder="پاسخ خود را بنویسید..."></textarea>
                            <button wire:click="saveReply({{ $comment->id }})"
                              class="btn btn-gradient-success rounded-pill px-3">ارسال پاسخ</button>
                          </div>
                        @endif
                      </td>
                      <td class="text-center align-middle"></td>
                    </tr>
                    @php $rowIndex++; @endphp
                  @endif
                @endforeach
              @empty
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="text-muted mb-2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                      </svg>
                      <p class="text-muted fw-medium">هیچ نظری یافت نشد.</p>
                    </div>
                  </td>
                </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="9" class="text-center py-4">
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
          <div class="comments-cards d-md-none">
            @if ($readyToLoad)
              @php
                $grouped = collect($comments->items())->groupBy(function ($item) {
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
              @foreach ($grouped as $doctorId => $doctorComments)
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
                        $firstComment = collect($doctorComments)->first(function ($item) {
                            return isset($item->doctor) && is_object($item->doctor);
                        });
                      @endphp
                      @if ($firstComment)
                        {{ $firstComment->doctor->first_name . ' ' . $firstComment->doctor->last_name }}
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
                    @foreach ($doctorComments as $comment)
                      @if (isset($comment->doctor) && is_object($comment->doctor))
                        <div class="comment-card mb-2 p-2 rounded-2"
                          style="background: #fff; border: 1px solid #e3e6ea;">
                          <div class="comment-card-header d-flex justify-content-between align-items-center px-2 py-2"
                            style="cursor:pointer;">
                            <div class="d-flex align-items-center gap-2">
                              <input type="checkbox" wire:model.live="selectedDoctorComments"
                                value="{{ $comment->id }}" class="form-check-input m-0" @click.stop>
                              <span class="fw-bold">
                                {{ \Illuminate\Support\Str::limit($comment->comment, 30) }}
                                <span
                                  class="text-muted">({{ $comment->doctor->first_name . ' ' . $comment->doctor->last_name }})</span>
                              </span>
                            </div>
                          </div>
                          <div class="comment-card-body px-2 py-2">
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">نام پزشک:</span>
                              <span
                                class="comment-card-value">{{ $comment->doctor->first_name . ' ' . $comment->doctor->last_name }}</span>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">نام کاربر:</span>
                              <span class="comment-card-value">
                                @if ($comment->userable)
                                  @if (property_exists($comment->userable, 'first_name'))
                                    {{ $comment->userable->first_name . ' ' . $comment->userable->last_name }}
                                  @elseif(property_exists($comment->userable, 'name'))
                                    {{ $comment->userable->name }}
                                  @else
                                    {{ $comment->userable->id }}
                                  @endif
                                @else
                                  ---
                                @endif
                              </span>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">شماره تماس:</span>
                              <span class="comment-card-value">
                                @if ($comment->userable && property_exists($comment->userable, 'mobile'))
                                  {{ $comment->userable->mobile }}
                                @else
                                  ---
                                @endif
                              </span>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">نظر:</span>
                              <span
                                class="comment-card-value">{{ \Illuminate\Support\Str::limit($comment->comment, 50) }}</span>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">پاسخ:</span>
                              <span
                                class="comment-card-value">{{ $comment->reply ? \Illuminate\Support\Str::limit($comment->reply, 50) : 'بدون پاسخ' }}</span>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">وضعیت:</span>
                              <button wire:click="confirmToggleStatus({{ $comment->id }})"
                                class="badge {{ $comment->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                                {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                              </button>
                            </div>
                            <div class="comment-card-item d-flex justify-content-between align-items-center py-1">
                              <span class="comment-card-label">عملیات:</span>
                              <div class="d-flex gap-2">
                                <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                                  class="btn btn-gradient-primary rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="toggleReply({{ $comment->id }})"
                                  class="btn btn-gradient-info rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                                  </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $comment->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </div>
                            </div>
                            @if ($replyingTo === $comment->id)
                              <div class="mt-3 p-3 bg-light rounded-2">
                                <textarea wire:model.live="replyText.{{ $comment->id }}" class="form-control mb-2" rows="3"
                                  placeholder="پاسخ خود را بنویسید..."></textarea>
                                <button wire:click="saveReply({{ $comment->id }})"
                                  class="btn btn-gradient-success rounded-pill px-3">ارسال پاسخ</button>
                              </div>
                            @endif
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </div>
              @endforeach
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
            <div class="text-muted">نمایش {{ $comments ? $comments->firstItem() : 0 }} تا
              {{ $comments ? $comments->lastItem() : 0 }} از {{ $comments ? $comments->total() : 0 }} ردیف
            </div>
            @if ($comments && $comments->hasPages())
              <div class="pagination-container">
                {{ $comments->onEachSide(1)->links('livewire::bootstrap') }}
              </div>
            @endif
          </div>
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
            title: 'حذف نظر',
            text: 'آیا مطمئن هستید که می‌خواهید این نظر را حذف کنید؟',

            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteDoctorCommentConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-toggle-status', (event) => {
          Swal.fire({
            title: event.action + ' نظر',
            text: 'آیا مطمئن هستید که می‌خواهید وضعیت نظر ' + event.name + ' را ' + event.action + ' کنید؟',

            showCancelButton: true,
            confirmButtonColor: '#1deb3c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('toggleStatusConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-delete-selected', function(data) {
          let text = data.allFiltered ?
            'آیا از حذف همه نظرات فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
            'آیا از حذف نظرات انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
          Swal.fire({
            title: 'تایید حذف گروهی',
            text: text,

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
      });
    </script>
  </div>
</div>
