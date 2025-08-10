<div>
  <div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">لیست استوری‌ها</h4>
        <p class="text-muted mb-0">مدیریت و نظارت بر استوری‌های سیستم</p>
      </div>
      <div>
        <a href="{{ route('admin.panel.stories.create') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i> ایجاد استوری جدید
        </a>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input wire:model.live="search" type="text" class="form-control"
              placeholder="جستجو در عنوان و توضیحات...">
          </div>
          <div class="col-md-2">
            <select wire:model.live="statusFilter" class="form-select">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
              <option value="pending">در انتظار تأیید</option>
            </select>
          </div>
          <div class="col-md-2">
            <select wire:model.live="typeFilter" class="form-select">
              <option value="">همه انواع</option>
              <option value="image">تصویر</option>
              <option value="video">ویدیو</option>
            </select>
          </div>
          <div class="col-md-2">
            <select wire:model.live="ownerTypeFilter" class="form-select">
              <option value="">همه مالکان</option>
              <option value="user">کاربر</option>
              <option value="doctor">پزشک</option>
              <option value="medical_center">مرکز درمانی</option>
              <option value="manager">مدیر</option>
            </select>
          </div>
          <div class="col-md-3">
            <button wire:click="loadStories" class="btn btn-outline-primary w-100">
              <i class="fas fa-search"></i> جستجو
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Group Actions -->
    @if ($readyToLoad && $totalFilteredCount > 0)
      <div class="card mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-3">
              <div class="form-check">
                <input wire:model.live="selectAll" type="checkbox" class="form-check-input" id="selectAll">
                <label class="form-check-label" for="selectAll">
                  انتخاب همه ({{ $totalFilteredCount }} مورد)
                </label>
              </div>
            </div>
            <div class="col-md-3">
              <select wire:model="groupAction" class="form-select">
                <option value="">عملیات گروهی</option>
                <option value="activate">فعال کردن</option>
                <option value="deactivate">غیرفعال کردن</option>
                <option value="approve">تأیید</option>
                <option value="reject">رد کردن</option>
                <option value="delete">حذف</option>
              </select>
            </div>
            <div class="col-md-3">
              <div class="form-check">
                <input wire:model="applyToAllFiltered" type="checkbox" class="form-check-input" id="applyToAll">
                <label class="form-check-label" for="applyToAll">
                  اعمال به همه فیلتر شده
                </label>
              </div>
            </div>
            <div class="col-md-3">
              <button wire:click="executeGroupAction" class="btn btn-warning w-100"
                @if (empty($groupAction)) disabled @endif>
                <i class="fas fa-cogs"></i> اجرا
              </button>
            </div>
          </div>
        </div>
      </div>
    @endif

    <!-- Stories List -->
    @if ($readyToLoad)
      @if ($stories->count() > 0)
        <div class="row">
          @foreach ($stories as $story)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
              <div class="card h-100">
                <!-- Media Preview -->
                <div class="position-relative">
                  @if ($story->media_path)
                    @if ($story->type === 'image')
                      <img src="{{ Storage::url($story->media_path) }}" class="card-img-top" alt="{{ $story->title }}"
                        style="height: 200px; object-fit: cover;">
                    @else
                      <video class="card-img-top" style="height: 200px; object-fit: cover;" controls>
                        <source src="{{ Storage::url($story->media_path) }}" type="video/mp4">
                        مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
                      </video>
                    @endif
                  @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                      style="height: 200px;">
                      <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                  @endif

                  <!-- Status Badge -->
                  <div class="position-absolute top-0 start-0 m-2">
                    @if ($story->status === 'active')
                      <span class="badge bg-success">فعال</span>
                    @elseif($story->status === 'inactive')
                      <span class="badge bg-secondary">غیرفعال</span>
                    @else
                      <span class="badge bg-warning">در انتظار</span>
                    @endif

                    @if ($story->is_live)
                      <span class="badge bg-danger ms-1">لایو</span>
                    @endif
                  </div>

                  <!-- Type Badge -->
                  <div class="position-absolute top-0 end-0 m-2">
                    @if ($story->type === 'image')
                      <span class="badge bg-info">تصویر</span>
                    @else
                      <span class="badge bg-primary">ویدیو</span>
                    @endif
                  </div>
                </div>

                <div class="card-body">
                  <h6 class="card-title">{{ Str::limit($story->title, 50) }}</h6>

                  @if ($story->description)
                    <p class="card-text text-muted small">{{ Str::limit($story->description, 100) }}</p>
                  @endif

                  <!-- Owner Info -->
                  <div class="mb-2">
                    <small class="text-muted">
                      <i class="fas fa-user"></i>
                      @if ($story->user)
                        کاربر: {{ $story->user->first_name }} {{ $story->user->last_name }}
                      @elseif($story->doctor)
                        پزشک: {{ $story->doctor->first_name }} {{ $story->doctor->last_name }}
                      @elseif($story->medicalCenter)
                        مرکز: {{ $story->medicalCenter->name }}
                      @elseif($story->manager)
                        مدیر: {{ $story->manager->first_name }} {{ $story->manager->last_name }}
                      @endif
                    </small>
                  </div>

                  <!-- Stats -->
                  <div class="row text-center mb-3">
                    <div class="col-4">
                      <small class="text-muted d-block">بازدید</small>
                      <span class="fw-bold">{{ number_format($story->views_count) }}</span>
                    </div>
                    <div class="col-4">
                      <small class="text-muted d-block">لایک</small>
                      <span class="fw-bold">{{ number_format($story->likes_count) }}</span>
                    </div>
                    <div class="col-4">
                      <small class="text-muted d-block">ترتیب</small>
                      <span class="fw-bold">{{ $story->order }}</span>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="d-flex gap-2">
                    <a href="{{ route('admin.panel.stories.edit', $story->id) }}"
                      class="btn btn-sm btn-outline-primary flex-fill">
                      <i class="fas fa-edit"></i> ویرایش
                    </a>

                    @if ($story->status === 'pending')
                      <button wire:click="confirmApprove({{ $story->id }})" class="btn btn-sm btn-success">
                        <i class="fas fa-check"></i>
                      </button>
                      <button wire:click="confirmReject({{ $story->id }})" class="btn btn-sm btn-warning">
                        <i class="fas fa-times"></i>
                      </button>
                    @else
                      <button wire:click="confirmToggleStatus({{ $story->id }})"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-toggle-{{ $story->status === 'active' ? 'on' : 'off' }}"></i>
                      </button>
                    @endif

                    <button wire:click="confirmDelete({{ $story->id }})" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>

                  <!-- Checkbox for selection -->
                  <div class="mt-2">
                    <div class="form-check">
                      <input wire:model.live="selectedStories" type="checkbox" class="form-check-input"
                        value="{{ $story->id }}" id="story_{{ $story->id }}">
                      <label class="form-check-label small text-muted" for="story_{{ $story->id }}">
                        انتخاب
                      </label>
                    </div>
                  </div>
                </div>

                <div class="card-footer text-muted small">
                  <div class="d-flex justify-content-between">
                    <span>{{ $story->created_at->format('Y/m/d H:i') }}</span>
                    @if ($story->is_live && $story->live_start_time)
                      <span>لایو: {{ $story->live_start_time->format('H:i') }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $stories->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">هیچ استوری‌ای یافت نشد!</h5>
          <p class="text-muted">با تغییر فیلترها یا ایجاد استوری جدید شروع کنید.</p>
        </div>
      @endif
    @else
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
        <p class="mt-3 text-muted">در حال بارگذاری استوری‌ها...</p>
      </div>
    @endif
  </div>

  <!-- Confirmation Modals -->
  <script>
    // Delete confirmation
    window.addEventListener('confirm-delete', event => {
      if (confirm(`آیا از حذف استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('deleteStory', event.detail.id);
      }
    });

    // Toggle status confirmation
    window.addEventListener('confirm-toggle-status', event => {
      if (confirm(`آیا از ${event.detail.action} استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('toggleStatusConfirmed', event.detail.id);
      }
    });

    // Approve confirmation
    window.addEventListener('confirm-approve', event => {
      if (confirm(`آیا از تأیید استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('approveStory', event.detail.id);
      }
    });

    // Reject confirmation
    window.addEventListener('confirm-reject', event => {
      if (confirm(`آیا از رد کردن استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('rejectStory', event.detail.id);
      }
    });

    // Delete selected confirmation
    window.addEventListener('confirm-delete-selected', event => {
      const message = event.detail.allFiltered === 'allFiltered' ?
        'آیا از حذف تمام استوری‌های فیلتر شده اطمینان دارید؟' :
        'آیا از حذف استوری‌های انتخاب شده اطمینان دارید؟';

      if (confirm(message)) {
        @this.call('deleteSelected', event.detail.allFiltered);
      }
    });
  </script>
</div>
