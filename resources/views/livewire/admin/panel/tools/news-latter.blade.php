<div class="newsletter-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadNewsletterMembers">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M4 4h16v12H4z" />
              <path d="M4 8l8 4 8-4" />
            </svg>
            مدیریت خبرنامه
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
              <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو در اعضا...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <button onclick="openXModal('addMemberModal')"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن عضو</span>
              </button>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $members->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو در اعضا...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <button onclick="openXModal('addMemberModal')"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن عضو</span>
          </button>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $members->total() : 0 }}
          </span>
        </div>
      </div>
    </header>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedMembers.length > 0">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_active">فعال کردن</option>
                <option value="status_inactive">غیرفعال کردن</option>
              </select>
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
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">ایمیل</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($members as $index => $member)
                    <tr class="align-middle">
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                          <input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}"
                            class="form-check-input m-0 align-middle">
                        </div>
                      </td>
                      <td class="text-center">{{ $members->firstItem() + $index }}</td>
                      <td>
                        @if ($editId === $member->id)
                          <input type="email"
                            class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start h-50"
                            wire:model.live="editEmail"
                            style="padding-right: 20px; text-align: right; direction: rtl;">
                        @else
                          <div class="text-truncate" style="max-width: 300px;" title="{{ $member->email }}">
                            {{ $member->email }}
                          </div>
                        @endif
                      </td>
                      <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center">
                          <input class="form-check-input" type="checkbox" role="switch"
                            wire:click="toggleStatus({{ $member->id }})" @checked($member->is_active)
                            style="width: 3em; height: 1.5em; margin-top: 0;">
                        </div>
                      </td>
                      <td class="text-center">
                        @if ($editId === $member->id)
                          <div class="d-flex justify-content-center gap-1">
                            <button wire:click="updateMember" class="btn btn-sm btn-gradient-success px-2 py-1">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5" />
                              </svg>
                            </button>
                            <button wire:click="cancelEdit" class="btn btn-sm btn-gradient-danger px-2 py-1">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12" />
                              </svg>
                            </button>
                          </div>
                        @else
                          <div class="d-flex justify-content-center gap-1">
                            <button wire:click="startEdit({{ $member->id }})"
                              class="btn btn-sm btn-gradient-success px-2 py-1">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                              </svg>
                            </button>
                            <button wire:click="confirmDelete({{ $member->id }})"
                              class="btn btn-sm btn-gradient-danger px-2 py-1">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                              </svg>
                            </button>
                          </div>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path d="M4 4h16v12H4z" />
                            <path d="M4 8l8 4 8-4" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ عضوی یافت نشد.</p>
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
              @forelse ($members as $index => $member)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">{{ $member->email }} <span
                          class="text-muted">({{ $member->status ? 'فعال' : 'غیرفعال' }})</span></span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">ایمیل:</span>
                      @if ($editId === $member->id)
                        <input type="email"
                          class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start h-50"
                          wire:model.live="editEmail" style="padding-right: 20px; text-align: right; direction: rtl;">
                      @else
                        <span class="note-card-value">{{ $member->email }}</span>
                      @endif
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" role="switch"
                          wire:click="toggleStatus({{ $member->id }})" @checked($member->is_active)
                          style="width: 3em; height: 1.5em; margin-top: 0;">
                      </div>
                    </div>
                    <div class="note-card-actions d-flex gap-1 mt-2 pt-2 border-top">
                      @if ($editId === $member->id)
                        <button wire:click="updateMember" class="btn btn-sm btn-gradient-success px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5" />
                          </svg>
                        </button>
                        <button wire:click="cancelEdit" class="btn btn-sm btn-gradient-danger px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12" />
                          </svg>
                        </button>
                      @else
                        <button wire:click="startEdit({{ $member->id }})"
                          class="btn btn-sm btn-gradient-success px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $member->id }})"
                          class="btn btn-sm btn-gradient-danger px-2 py-1">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M4 4h16v12H4z" />
                      <path d="M4 8l8 4 8-4" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ عضوی یافت نشد.</p>
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
            @if ($readyToLoad)
              <div class="text-muted">
                نمایش {{ $members->firstItem() }} تا {{ $members->lastItem() }}
                از {{ $members->total() }} ردیف
              </div>
              @if ($members->hasPages())
                {{ $members->links('livewire::bootstrap') }}
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Add Member Modal -->
    <x-custom-modal id="addMemberModal" title="افزودن عضو جدید" size="md">
      <div class="space-y-4" wire:ignore>
        <div>
          <label for="newEmail" class="block text-sm font-medium text-gray-700 mb-2">ایمیل</label>
          <input type="email" id="newEmail" wire:model="newEmail"
            class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="example@email.com" style="direction: rtl; text-align: right;">
          @error('newEmail')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button type="button" onclick="closeXModal('addMemberModal')"
            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            لغو
          </button>
          <button type="button" onclick="validateAndAdd() ? $wire.addMember() : null"
            class="btn btn-primary px-4 py-2 rounded-lg transition-colors">
            افزودن
          </button>
        </div>
      </div>
    </x-custom-modal>

    <script>
      function validateAndAdd() {
        const emailInput = document.getElementById('newEmail');
        const email = emailInput.value.trim();

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
          // Show error message
          const errorSpan = emailInput.parentNode.querySelector('.text-red-500');
          if (errorSpan) {
            errorSpan.textContent = 'لطفاً ایمیل را وارد کنید.';
          } else {
            const newErrorSpan = document.createElement('span');
            newErrorSpan.className = 'text-red-500 text-sm mt-1';
            newErrorSpan.textContent = 'لطفاً ایمیل را وارد کنید.';
            emailInput.parentNode.appendChild(newErrorSpan);
          }
          return false;
        }

        if (!emailRegex.test(email)) {
          // Show error message
          const errorSpan = emailInput.parentNode.querySelector('.text-red-500');
          if (errorSpan) {
            errorSpan.textContent = 'ایمیل واردشده معتبر نیست.';
          } else {
            const newErrorSpan = document.createElement('span');
            newErrorSpan.className = 'text-red-500 text-sm mt-1';
            newErrorSpan.textContent = 'ایمیل واردشده معتبر نیست.';
            emailInput.parentNode.appendChild(newErrorSpan);
          }
          return false;
        }

        // Clear any existing error messages
        const errorSpan = emailInput.parentNode.querySelector('.text-red-500');
        if (errorSpan) {
          errorSpan.remove();
        }

        // If validation passes, proceed with Livewire action
        return true;
      }

      document.addEventListener('livewire:init', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });
        Livewire.on('confirm-delete', (event) => {
          Swal.fire({
            title: 'حذف عضو',
            text: 'آیا مطمئن هستید که می‌خواهید این عضو را حذف کنید؟',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteMemberConfirmed', {
                id: event.id
              });
            }
          });
        });
        Livewire.on('close-modal', (event) => {
          closeXModal(event.modalId);
        });
      });
    </script>
  </div>
</div>
