<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorComments">
  <div
    class="glass-header text-white p-4 rounded-xl mb-6 shadow-2xl d-flex justify-content-between align-items-center flex-wrap gap-4">
    <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px;">مدیریت نظرات پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
      <input type="text" class="form-control border-0 shadow-none bg-white/90 text-dark ps-5 rounded-full h-12"
        wire:model.live="search" placeholder="جستجو در نام، نظر یا پزشک...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-4 text-gray-500">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-3 flex-shrink-0 flex-wrap justify-content-center mt-md-0 buttons-container">
      <a href="{{ route('admin.panel.doctor-comments.create') }}"
        class="btn btn-gradient-success rounded-full px-5 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all duration-300">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="font-medium">افزودن نظر</span>
      </a>
      <div class="dropdown">
        <button
          class="btn btn-gradient-warning rounded-full px-5 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all duration-300"
          data-bs-toggle="dropdown" @if (empty($selectedDoctorComments)) disabled @endif>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10 12h4" />
            <path d="M12 10v4" />
          </svg>
          <span class="font-medium">اقدامات گروهی</span>
        </button>
        <ul class="dropdown-menu rounded-xl shadow-lg bg-white border-0 mt-2">
          <li><a class="dropdown-item px-4 py-2 hover:bg-gray-100" wire:click="toggleSelectedStatus">تغییر وضعیت</a>
          </li>
          <li><a class="dropdown-item px-4 py-2 hover:bg-gray-100" wire:click="deleteSelected">حذف انتخاب‌شده‌ها</a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-xl rounded-2xl overflow-hidden bg-white/95">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $doctor)
            <div class="doctor-toggle border-bottom transition-all duration-300 hover:bg-gray-50">
              <div class="d-flex justify-content-between align-items-center p-4 cursor-pointer"
                wire:click="toggleDoctor({{ $doctor->id }})">
                <div class="d-flex align-items-center gap-3">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4b5563"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  <span
                    class="fw-bold text-gray-800 text-lg">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                  <span
                    class="badge bg-gray-100 text-gray-700 font-medium px-3 py-1 rounded-full">{{ $doctor->comments->total() }}
                    نظر</span>
                </div>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="2"
                  class="transition-transform {{ in_array($doctor->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($doctor->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-4 bg-gray-50">
                  <table class="table table-modern w-100 m-0">
                    <thead class="glass-header text-white">
                      <tr>
                        <th class="text-center align-middle" style="width: 50px;">
                          <input type="checkbox" wire:model.live="selectAll.{{ $doctor->id }}"
                            class="form-check-input m-0 shadow-sm">
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
                        <tr class="hover:bg-gray-100 transition-colors duration-200">
                          <td class="text-center align-middle">
                            <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                              class="form-check-input m-0 shadow-sm">
                          </td>
                          <td class="text-center align-middle font-medium text-gray-600">
                            {{ $doctor->comments->firstItem() + $index }}</td>
                          <td class="align-middle text-gray-700">{{ $comment->user_name }}</td>
                          <td class="align-middle text-gray-600">{{ $comment->user_phone ?? 'ثبت نشده' }}</td>
                          <td class="align-middle">
                            <span class="text-gray-800">{{ Str::limit($comment->comment, 50) }}</span>
                            @if ($comment->reply)
                              <div class="mt-2 p-3 bg-emerald-50 rounded-xl text-emerald-900 shadow-sm">
                                <strong class="font-semibold text-emerald-700">پاسخ:</strong>
                                <span class="block mt-1">{{ Str::limit($comment->reply, 50) }}</span>
                              </div>
                            @endif
                          </td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleStatus({{ $comment->id }})"
                              class="toggle-btn {{ $comment->status ? 'toggle-active' : 'toggle-inactive' }} px-4 py-1 rounded-full font-medium shadow-sm hover:scale-105 transition-all duration-300">
                              {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                                class="btn btn-gradient-success rounded-full px-4 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="toggleReply({{ $comment->id }})"
                                class="btn btn-gradient-info rounded-full px-4 py-1 shadow-sm hover:shadow-md transition-all duration-300"
                                title="پاسخ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M21 11.5a8.38 8.38 0 01-11.9 7.6L3 21l1.9-5.7a8.38 8.38 0 017.6-11.9A8.38 8.38 0 0121 11.5z" />
                                </svg>
                              </button>
                              <button wire:click="confirmDelete({{ $comment->id }})"
                                class="btn btn-gradient-danger rounded-full px-4 py-1 shadow-sm hover:shadow-md transition-all duration-300">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                            @if ($replyingTo === $comment->id)
                              <div class="mt-3 p-3 bg-gray-50 rounded-xl shadow-inner">
                                <textarea wire:model.live="replyText.{{ $comment->id }}"
                                  class="form-control mb-2 rounded-lg border-gray-200 focus:border-indigo-400 focus:ring-indigo-200 w-full"
                                  rows="3" placeholder="پاسخ خود را با دقت بنویسید..."></textarea>
                                <button wire:click="saveReply({{ $comment->id }})"
                                  class="btn btn-gradient-success rounded-full px-6 py-2 shadow-sm hover:shadow-md transition-all duration-300">ارسال
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
                                stroke="currentColor" stroke-width="2" class="text-gray-400 mb-3 animate-bounce">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-gray-500 font-medium m-0">هیچ نظری یافت نشد</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  @if ($doctor->comments->hasPages())
                    <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-4 bg-gray-50">
                      <span class="text-gray-600 font-medium">نمایش {{ $doctor->comments->firstItem() }} تا
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
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-gray-400 mb-3 animate-bounce">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-gray-500 font-medium m-0">هیچ پزشک یا نظری یافت نشد</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-6 text-gray-600 font-medium animate-pulse">در حال بارگذاری نظرات...</div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .glass-header {
      background: linear-gradient(135deg, rgba(55, 65, 81, 0.95), rgba(17, 24, 39, 0.9));
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-gradient-success {
      background: linear-gradient(135deg, #34d399, #10b981);
      color: white;
    }

    .btn-gradient-warning {
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: white;
    }

    .btn-gradient-danger {
      background: linear-gradient(135deg, #f87171, #ef4444);
      color: white;
    }

    .btn-gradient-info {
      background: linear-gradient(135deg, #60a5fa, #3b82f6);
      color: white;
    }

    .card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      background: linear-gradient(145deg, #ffffff, #f8fafc);
    }

    .table-modern {
      border: none;
      border-radius: 12px;
      overflow: hidden;
    }

    .table-modern th,
    .table-modern td {
      border: none;
      padding: 16px;
      vertical-align: middle;
    }

    .table-modern th {
      background: linear-gradient(135deg, rgba(55, 65, 81, 0.95), rgba(17, 24, 39, 0.9));
      color: white;
      font-weight: 600;
    }

    .table-modern tbody tr {
      border-bottom: 1px solid #f1f5f9;
    }

    .form-control {
      transition: all 0.3s ease;
    }

    .form-control:focus {
      box-shadow: 0 0 8px rgba(99, 102, 241, 0.2);
    }

    .rounded-full {
      border-radius: 9999px;
    }

    .rounded-xl {
      border-radius: 1rem;
    }

    .rounded-2xl {
      border-radius: 1.5rem;
    }

    .doctor-toggle {
      transition: all 0.3s ease;
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

    /* Toggle Button Styling */
    .toggle-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 90px;
      padding: 6px 16px;
      font-size: 0.9rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: none;
      cursor: pointer;
    }

    .toggle-active {
      background: linear-gradient(135deg, #34d399, #10b981);
      color: white;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
    }

    .toggle-inactive {
      background: linear-gradient(135deg, #f87171, #ef4444);
      color: white;
      box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3);
    }

    .toggle-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    /* Reply Section Styling */
    .bg-emerald-50 {
      background: linear-gradient(135deg, #ecfdf5, #d1fae5);
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .form-control.reply-textarea {
      width: 100%;
      min-height: 90px;
      padding: 12px 16px;
      font-size: 1rem;
      border-radius: 12px;
      background: #ffffff;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .form-control.reply-textarea:focus {
      border-color: #10b981;
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
    }

    /* Dropdown Styling */
    .dropdown-menu {
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      border: none;
      overflow: hidden;
    }

    .dropdown-item {
      padding: 10px 20px;
      font-size: 0.95rem;
      color: #374151;
      transition: all 0.2s ease;
    }

    .dropdown-item:hover {
      background: #f1f5f9;
      color: #1f2937;
    }
  </style>

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
          cancelButtonColor: '#6b7280',
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
