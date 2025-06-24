<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorComments">
  <div
    class="glass-header p-4 rounded-xl mb-6 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-4">
    <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px; color: var(--text-primary);">مدیریت نظرات پزشکان
    </h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
      <input type="text"
        class="form-control border-0 shadow-none bg-background-card text-text-primary ps-5 rounded-full h-12"
        wire:model.live="search" placeholder="جستجو در نام، نظر یا پزشک...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-4 text-text-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-3 flex-shrink-0 flex-wrap justify-content-center mt-md-0 buttons-container">
      <a href="{{ route('admin.panel.doctor-comments.create') }}"
        class="btn btn-gradient-success rounded-full px-4 py-1.5 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all duration-300">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="font-medium text-sm">افزودن نظر</span>
      </a>
      <div class="dropdown">
        <button
          class="btn btn-gradient-warning rounded-full px-4 py-1.5 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all duration-300"
          data-bs-toggle="dropdown" @if (empty($selectedDoctorComments)) disabled @endif>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10 12h4" />
            <path d="M12 10v4" />
          </svg>
          <span class="font-medium text-sm">اقدامات گروهی</span>
        </button>
        <ul class="dropdown-menu rounded-xl shadow-lg bg-background-card border-0 mt-2">
          <li><a class="dropdown-item px-4 py-2 hover:bg-background-light" wire:click="toggleSelectedStatus">تغییر
              وضعیت</a></li>
          <li><a class="dropdown-item px-4 py-2 hover:bg-background-light" wire:click="deleteSelected">حذف
              انتخاب‌شده‌ها</a></li>
        </ul>
      </div>
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
                  <span
                    class="fw-bold text-text-primary text-lg">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                  <span
                    class="badge-comment bg-gradient-primary text-white font-medium px-3 py-1 rounded-full shadow-sm hover:shadow-md transition-all duration-300">{{ $doctor->comments->total() }}
                    نظر</span>
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
                        <th class="text-center align-middle" style="width: 50px;">
                          <input type="checkbox" wire:model.live="selectAll.{{ $doctor->id }}"
                            class="form-check-input m-0 align-middle shadow-sm">
                        </th>
                        <th class="text-center align-middle" style="width: 70px;">#</th>
                        <th class="align-middle">نام کاربر</th>
                        <th class="align-middle">شماره تماس</th>
                        <th class="align-middle">نظر</th>
                        <th class="text-center align-middle" style="width: 120px;">وضعیت</th>
                        <th class="text-center align-middle" style="width: 240px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($doctor->comments as $index => $comment)
                        <tr class="hover:bg-background-light transition-colors duration-200">
                          <td class="text-center align-middle">
                            <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                              class="form-check-input m-0 align-middle shadow-sm">
                          </td>
                          <td class="text-center align-middle font-medium text-text-secondary">
                            {{ $doctor->comments->firstItem() + $index }}</td>
                          <td class="align-middle text-text-primary">{{ $comment->user_name }}</td>
                          <td class="align-middle text-text-secondary">{{ $comment->user_phone ?? 'ثبت نشده' }}</td>
                          <td class="align-middle">
                            <span class="text-text-primary">{{ Str::limit($comment->comment, 50) }}</span>
                            @if ($comment->reply)
                              <div class="mt-2 p-3 bg-secondary/20 rounded-xl text-text-primary shadow-sm">
                                <strong class="font-semibold text-secondary">پاسخ:</strong>
                                <span class="block mt-1">{{ Str::limit($comment->reply, 50) }}</span>
                              </div>
                            @endif
                          </td>
                          <td class="text-center align-middle">
                            <label class="toggle-switch">
                              <input type="checkbox" wire:model.live="commentStatus.{{ $comment->id }}"
                                wire:change="toggleStatus({{ $comment->id }})" class="toggle-input">
                              <span class="toggle-slider"></span>
                            </label>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                                class="btn btn-gradient-success rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 12 8 13 9 9l9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="toggleReply({{ $comment->id }})"
                                class="btn btn-gradient-info rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300"
                                title="پاسخ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                                </svg>
                              </button>
                              <button wire:click="confirmDelete({{ $comment->id }})"
                                class="btn btn-gradient-danger rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                            @if ($replyingTo === $comment->id)
                              <div class="mt-3 p-3 bg-background-light rounded-xl shadow-inner">
                                <textarea wire:model.live="replyText.{{ $comment->id }}"
                                  class="form-control reply-textarea mb-2 rounded-lg border-border-neutral focus:border-primary focus:ring-primary/25 w-full"
                                  rows="3" placeholder="پاسخ خود را با دقت بنویسید..."></textarea>
                                <button wire:click="saveReply({{ $comment->id }})"
                                  class="btn btn-gradient-success rounded-full px-4 py-1.5 shadow-sm hover:shadow-md transition-all duration-300">ارسال
                                  پاسخ</button>
                              </div>
                            @endif
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="7" class="text-center py-6">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                                stroke="var(--text-secondary)" stroke-width="2" class="mb-3 custom-animate-bounce">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-text-secondary font-medium m-0">هیچ نظری یافت نشد</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  <!-- کارت‌ها برای موبایل و تبلت -->
                  <div class="mobile-cards d-block d-lg-none">
                    @forelse ($doctor->comments as $index => $comment)
                      <div class="card mb-3 rounded-xl shadow-md bg-background-card">
                        <div class="card-body p-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                              <input type="checkbox" wire:model.live="selectedDoctorComments"
                                value="{{ $comment->id }}" class="form-check-input m-0 align-middle shadow-sm">
                              <span
                                class="text-text-secondary font-medium text-sm">#{{ $doctor->comments->firstItem() + $index }}</span>
                            </div>
                            <label class="toggle-switch">
                              <input type="checkbox" wire:model.live="commentStatus.{{ $comment->id }}"
                                wire:change="toggleStatus({{ $comment->id }})" class="toggle-input">
                              <span class="toggle-slider"></span>
                            </label>
                          </div>
                          <div class="mb-2">
                            <p class="text-text-primary mb-1 text-sm"><strong>نام کاربر:</strong>
                              {{ $comment->user_name }}</p>
                            <p class="text-text-secondary mb-1 text-sm"><strong>شماره تماس:</strong>
                              {{ $comment->user_phone ?? 'ثبت نشده' }}</p>
                            <p class="text-text-primary mb-1 text-sm"><strong>نظر:</strong>
                              {{ Str::limit($comment->comment, 50) }}</p>
                            @if ($comment->reply)
                              <div class="mt-2 p-2 bg-secondary/20 rounded-lg text-text-primary shadow-sm">
                                <strong class="font-semibold text-secondary text-sm">پاسخ:</strong>
                                <span class="block mt-1 text-sm">{{ Str::limit($comment->reply, 50) }}</span>
                              </div>
                            @endif
                          </div>
                          <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                              class="btn btn-gradient-success rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 12 8 13 9 9l9.5-9.5z" />
                              </svg>
                            </a>
                            <button wire:click="toggleReply({{ $comment->id }})"
                              class="btn btn-gradient-info rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300"
                              title="پاسخ">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                              </svg>
                            </button>
                            <button wire:click="confirmDelete({{ $comment->id }})"
                              class="btn btn-gradient-danger rounded-full px-3 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                              </svg>
                            </button>
                          </div>
                          @if ($replyingTo === $comment->id)
                            <div class="mt-2 p-2 bg-background-light rounded-lg shadow-inner">
                              <textarea wire:model.live="replyText.{{ $comment->id }}"
                                class="form-control reply-textarea mb-2 rounded-lg border-border-neutral focus:border-primary focus:ring-primary/25 w-full"
                                rows="3" placeholder="پاسخ خود را با دقت بنویسید..."></textarea>
                              <button wire:click="saveReply({{ $comment->id }})"
                                class="btn btn-gradient-success rounded-full px-4 py-1.5 shadow-sm hover:shadow-md transition-all duration-300">ارسال
                                پاسخ</button>
                            </div>
                          @endif
                        </div>
                      </div>
                    @empty
                      <div class="text-center py-6">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                          <svg width="56" height="56" viewBox="0 0 24 24" fill="none"
                            stroke="var(--text-secondary)" stroke-width="2" class="mb-3 custom-animate-bounce">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-text-secondary font-medium m-0">هیچ نظری یافت نشد</p>
                        </div>
                      </div>
                    @endforelse
                  </div>
                  @if ($doctor->comments->hasPages())
                    <div
                      class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-4 bg-background-light">
                      <span class="text-text-secondary font-medium">نمایش {{ $doctor->comments->firstItem() }} تا
                        {{ $doctor->comments->lastItem() }} از {{ $doctor->comments->total() }} نظر</span>
                      {{ $doctor->comments->links('livewire::bootstrap') }}
                    </div>
                  @endif
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
                <p class="text-text-secondary font-medium m-0">هیچ پزشک یا نظری یافت نشد</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-6 text-text-secondary font-medium animate-pulse">در حال بارگذاری نظرات...</div>
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
          title: 'حذف نظر',
          text: 'مطمئن هستید که می‌خواهید این نظر را حذف کنید؟',
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
            Livewire.dispatch('deleteDoctorCommentConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>
