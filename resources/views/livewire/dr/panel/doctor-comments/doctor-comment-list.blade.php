<div class="container-fluid py-3" dir="rtl">
  <div class="header-custom p-3 rounded-lg mb-4 shadow-md d-flex justify-content-between align-items-center flex-wrap gap-3"
    style="background: var(--background-light); border: 1px solid var(--border-neutral);">
    <h1 class="m-0 h4 font-medium flex-grow-1" style="min-width: 180px; color: var(--text-primary);">مدیریت نظرات</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 ps-4 rounded-lg h-50" wire:model.live="search"
        placeholder="جستجو در نام، نظر یا بیمار..."
        style="background: var(--background-card); color: var(--text-primary);">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3"
        style="color: var(--text-secondary);">
     
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center">
      <button wire:click="toggleSelectedStatus"
        class="btn btn-secondary rounded-lg px-4 py-1 d-flex align-items-center gap-1 shadow-sm hover:shadow-md transition-all duration-200 h-50"
        @if (empty($selectedDoctorComments)) disabled @endif>
        <span class="font-medium">تغییر وضعیت</span>
      </button>
      <button wire:click="deleteSelected"
        class="btn btn-danger rounded-lg px-4 h-50 py-1 d-flex align-items-center gap-1 shadow-sm hover:shadow-md transition-all duration-200"
        @if (empty($selectedDoctorComments)) disabled @endif>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span class="font-medium">حذف انتخاب‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-md rounded-lg" style="background: var(--background-card);">
      <div class="card-body p-0">
        @if ($readyToLoad)
          <div class="table-responsive text-nowrap p-3">
            <table class="table table-modern w-100 m-0">
              <thead style="background: var(--primary); color: #ffffff;">
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 shadow-sm">
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">#</th>
                  <th class="align-middle">پزشک</th>
                  <th class="align-middle">نام کاربر</th>
                  <th class="align-middle">نظر</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 200px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($comments as $index => $comment)
                  <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                        class="form-check-input m-0 shadow-sm">
                    </td>
                    <td class="text-center align-middle font-medium" style="color: var(--text-secondary);">
                      {{ $comments->firstItem() + $index }}</td>
                    <td class="align-middle font-medium" style="color: var(--text-primary);">
                      {{ $comment->doctor->full_name }}</td>
                    <td class="align-middle" style="color: var(--text-secondary);">{{ $comment->user_name }}</td>
                    <td class="align-middle">
                      <span style="color: var(--text-primary);">{{ Str::limit($comment->comment, 40) }}</span>
                      @if ($comment->reply)
                        <div class="mt-1 p-2 rounded-lg shadow-sm"
                          style="background: var(--background-light); border: 1px solid var(--border-neutral);">
                          <strong style="color: var(--secondary-hover);">پاسخ:</strong>
                          <span class="block mt-1"
                            style="color: var(--text-primary);">{{ Str::limit($comment->reply, 40) }}</span>
                        </div>
                      @endif
                    </td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $comment->id }})"
                        class="toggle-btn {{ $comment->status ? 'toggle-active' : 'toggle-inactive' }} px-3 py-1 rounded-lg font-medium shadow-sm hover:scale-105 transition-all duration-200">
                        {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <button wire:click="toggleReply({{ $comment->id }})"
                          class="btn btn-primary rounded-lg px-3 py-1 shadow-sm hover:shadow-md transition-all duration-200"
                          title="پاسخ">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                          </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $comment->id }})"
                          class="btn btn-danger rounded-lg px-3 py-1 shadow-sm hover:shadow-md transition-all duration-200">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                      @if ($replyingTo === $comment->id)
                        <div class="mt-2 p-2 rounded-lg shadow-inner" style="background: var(--background-light);">
                          <textarea wire:model.live="replyText.{{ $comment->id }}" class="form-control mb-2 rounded-lg w-full" rows="2"
                            placeholder="پاسخ خود را با دقت بنویسید..." style="border-color: var(--border-neutral);"></textarea>
                          <button wire:click="saveReply({{ $comment->id }})"
                            class="btn btn-secondary rounded-lg px-4 py-1 shadow-sm hover:shadow-md transition-all duration-200">ارسال
                            پاسخ</button>
                        </div>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <div class="d-flex flex-column align-items-center justify-content-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" style="color: var(--text-secondary);">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p style="color: var(--text-secondary); font-weight: 500; margin: 0;">هیچ نظری یافت نشد</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if ($comments->hasPages())
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3"
              style="background: var(--background-light);">
              <span style="color: var(--text-secondary); font-weight: 500;">نمایش {{ $comments->firstItem() }} تا
                {{ $comments->lastItem() }} از {{ $comments->total() }} نظر</span>
              {{ $comments->links('livewire::bootstrap') }}
            </div>
          @endif
        @else
          <div class="text-center py-4" style="color: var(--text-secondary); font-weight: 500;">در حال بارگذاری
            نظرات...</div>
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
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#707070',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر',
          customClass: {
            popup: 'rounded-lg shadow-md',
            confirmButton: 'rounded-lg px-4 py-1',
            cancelButton: 'rounded-lg px-4 py-1'
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
