<div class="container-fluid py-2 mt-3" dir="rtl">
  <div
    class="header-custom p-2 rounded-lg mb-3 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center gap-2"
    style="background: var(--background-light); border: 1px solid var(--border-neutral);">
    <h1 class="m-0 h5 font-medium flex-grow-1 text-center text-md-start" style="color: var(--text-primary);">مدیریت نظرات
    </h1>
    <div class="input-group flex-grow-1" style="max-width: 100%; width: 100%; max-width: 350px;">
      <input type="text" class="form-control border-0 rounded-lg h-40" wire:model.live="search" placeholder="جستجو..."
        style="background: var(--background-card); color: var(--text-primary); font-size: 0.85rem;">
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
      <button wire:click="toggleSelectedStatus"
        class="btn btn-secondary rounded-lg px-3 py-1 d-flex align-items-center gap-1"
        @if (empty($selectedDoctorComments)) disabled @endif title="تغییر وضعیت">
        <span class="d-none d-md-inline font-medium">تغییر وضعیت</span>
        <svg class="d-md-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2">
          <path d="M12 20v-6m0 0l-4 4m4-4l4 4M12 4v6m0 0l-4-4m4 4l4-4" />
        </svg>
      </button>
      <button wire:click="deleteSelected" class="btn btn-danger rounded-lg px-3 py-1 d-flex align-items-center gap-1"
        @if (empty($selectedDoctorComments)) disabled @endif title="حذف">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span class="d-none d-md-inline font-medium">حذف</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm rounded-lg" style="background: var(--background-card);">
      <div class="card-body p-0">
        @if ($readyToLoad)
          <div class="table-responsive p-2 d-none d-md-block">
            <table class="table table-modern w-100 m-0">
              <thead style="background: var(--primary); color: #ffffff;">
                <tr>
                  <th class="text-center" style="width: 40px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center" style="width: 60px;">#</th>
                  <th>پزشک</th>
                  <th>نام کاربر</th>
                  <th>نظر</th>
                  <th class="text-center" style="width: 100px;">وضعیت</th>
                  <th class="text-center" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($comments as $index => $comment)
                  <tr>
                    <td class="text-center">
                      <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                        class="form-check-input m-0 align-middle">
                    </td>
                    <td class="text-center" style="color: var(--text-secondary);">{{ $comments->firstItem() + $index }}
                    </td>
                    <td style="color: var(--text-primary);">{{ $comment->doctor->full_name }}</td>
                    <td style="color: var(--text-secondary);">{{ $comment->user_name }}</td>
                    <td style="color: var(--text-primary);">{{ Str::limit($comment->comment, 30) }}
                      @if ($comment->reply)
                        <div class="mt-1 p-1 rounded" style="background: var(--background-light);">
                          <strong style="color: var(--secondary-hover);">پاسخ:</strong>
                          <span>{{ Str::limit($comment->reply, 30) }}</span>
                        </div>
                      @endif
                    </td>
                    <td class="text-center">
                      <button wire:click="toggleStatus({{ $comment->id }})"
                        class="toggle-btn {{ $comment->status ? 'toggle-active' : 'toggle-inactive' }} px-2 py-1 rounded font-medium">
                        {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <button wire:click="toggleReply({{ $comment->id }})" class="btn btn-primary rounded px-2 py-1"
                          title="پاسخ">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                          </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $comment->id }})"
                          class="btn btn-danger rounded px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @if ($replyingTo === $comment->id)
                    <tr>
                      <td colspan="7" class="p-2">
                        <div class="rounded-lg shadow-inner" style="background: var(--background-light);">
                          <textarea wire:model.live="replyText.{{ $comment->id }}" class="form-control mb-2 rounded-lg w-100" rows="2"
                            placeholder="پاسخ..." style="border-color: var(--border-neutral); font-size: 0.85rem;"></textarea>
                          <button wire:click="saveReply({{ $comment->id }})"
                            class="btn btn-secondary rounded-lg px-3 py-1">ارسال</button>
                        </div>
                      </td>
                    </tr>
                  @endif
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-3">
                      <p style="color: var(--text-secondary); margin: 0;">هیچ نظری یافت نشد</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <!-- نمایش کارت‌مانند در موبایل -->
          <div class="d-md-none p-2">
            @forelse ($comments as $index => $comment)
              <div class="card mb-2 p-2 shadow-sm rounded-lg">
                <div class="d-flex align-items-center gap-2">
                  <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                    class="form-check-input">
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                      <span class="font-medium"
                        style="color: var(--text-primary);">{{ $comment->doctor->full_name }}</span>
                      <span style="color: var(--text-secondary);">#{{ $comments->firstItem() + $index }}</span>
                    </div>
                    <p style="color: var(--text-secondary); margin: 0;">{{ $comment->user_name }}</p>
                    <p style="color: var(--text-primary); margin: 0;">{{ Str::limit($comment->comment, 50) }}</p>
                    @if ($comment->reply)
                      <div class="mt-1 p-1 rounded" style="background: var(--background-light);">
                        <strong style="color: var(--secondary-hover);">پاسخ:</strong>
                        <span>{{ Str::limit($comment->reply, 50) }}</span>
                      </div>
                    @endif
                    <div class="d-flex justify-content-between mt-2">
                      <button wire:click="toggleStatus({{ $comment->id }})"
                        class="toggle-btn {{ $comment->status ? 'toggle-active' : 'toggle-inactive' }} px-2 py-1 rounded font-medium">
                        {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                      <div class="d-flex gap-2">
                        <button wire:click="toggleReply({{ $comment->id }})"
                          class="btn btn-primary rounded px-2 py-1" title="پاسخ">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                          </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $comment->id }})"
                          class="btn btn-danger rounded px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    @if ($replyingTo === $comment->id)
                      <div class="mt-2 p-2 rounded-lg shadow-inner" style="background: var(--background-light);">
                        <textarea wire:model.live="replyText.{{ $comment->id }}" class="form-control mb-2 rounded-lg w-100" rows="2"
                          placeholder="پاسخ..." style="border-color: var(--border-neutral); font-size: 0.85rem;"></textarea>
                        <button wire:click="saveReply({{ $comment->id }})"
                          class="btn btn-secondary rounded-lg px-3 py-1">ارسال</button>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-3">
                <p style="color: var(--text-secondary); margin: 0;">هیچ نظری یافت نشد</p>
              </div>
            @endforelse
          </div>
          @if ($comments->hasPages())
            <div class="p-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <span style="color: var(--text-secondary); font-size: 0.85rem;">نمایش {{ $comments->firstItem() }} تا
                {{ $comments->lastItem() }} از {{ $comments->total() }}</span>
              {{ $comments->links('livewire::bootstrap') }}
            </div>
          @endif
        @else
          <div class="text-center py-3" style="color: var(--text-secondary); font-size: 0.85rem;">در حال بارگذاری...
          </div>
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

          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#707070',
          confirmButtonText: 'بله',
          cancelButtonText: 'خیر',
          customClass: {
            popup: 'rounded-lg shadow-md',
            confirmButton: 'rounded-lg px-3 py-1',
            cancelButton: 'rounded-lg px-3 py-1'
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
