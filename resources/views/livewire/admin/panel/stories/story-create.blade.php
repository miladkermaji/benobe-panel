<div>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-plus-circle text-primary"></i>
              ایجاد استوری جدید
            </h5>
          </div>
          <div class="card-body">
            <form wire:submit.prevent="save">
              <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                  <div class="mb-3">
                    <label for="title" class="form-label">عنوان استوری <span class="text-danger">*</span></label>
                    <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror"
                      id="title" placeholder="عنوان استوری را وارد کنید">
                    @error('title')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">توضیحات</label>
                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description"
                      rows="4" placeholder="توضیحات استوری را وارد کنید"></textarea>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="type" class="form-label">نوع استوری <span class="text-danger">*</span></label>
                        <select wire:model="type" class="form-select @error('type') is-invalid @enderror"
                          id="type">
                          <option value="image">تصویر</option>
                          <option value="video">ویدیو</option>
                        </select>
                        @error('type')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="status" class="form-label">وضعیت <span class="text-danger">*</span></label>
                        <select wire:model="status" class="form-select @error('status') is-invalid @enderror"
                          id="status">
                          <option value="active">فعال</option>
                          <option value="inactive">غیرفعال</option>
                          <option value="pending">در انتظار تأیید</option>
                        </select>
                        @error('status')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="order" class="form-label">ترتیب نمایش</label>
                        <input wire:model="order" type="number"
                          class="form-control @error('order') is-invalid @enderror" id="order" placeholder="0"
                          min="0">
                        @error('order')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="duration" class="form-label">مدت زمان (ثانیه)</label>
                        <input wire:model="duration" type="number"
                          class="form-control @error('duration') is-invalid @enderror" id="duration"
                          placeholder="مدت زمان" min="1">
                        @error('duration')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <!-- Live Settings -->
                  <div class="card mb-3">
                    <div class="card-header">
                      <div class="form-check">
                        <input wire:model="is_live" type="checkbox" class="form-check-input" id="is_live">
                        <label class="form-check-label" for="is_live">
                          <strong>تنظیمات لایو</strong>
                        </label>
                      </div>
                    </div>
                    <div class="card-body" @if (!$is_live) style="display: none;" @endif>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="live_start_time" class="form-label">زمان شروع لایو</label>
                            <input wire:model="live_start_time" type="datetime-local"
                              class="form-control @error('live_start_time') is-invalid @enderror" id="live_start_time">
                            @error('live_start_time')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="live_end_time" class="form-label">زمان پایان لایو</label>
                            <input wire:model="live_end_time" type="datetime-local"
                              class="form-control @error('live_end_time') is-invalid @enderror" id="live_end_time">
                            @error('live_end_time')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                  <!-- Owner Selection -->
                  <div class="card mb-3">
                    <div class="card-header">
                      <h6 class="mb-0">انتخاب مالک</h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="owner_type" class="form-label">نوع مالک <span
                            class="text-danger">*</span></label>
                        <select wire:model="owner_type" class="form-select @error('owner_type') is-invalid @enderror"
                          id="owner_type">
                          <option value="user">کاربر</option>
                          <option value="doctor">پزشک</option>
                          <option value="medical_center">مرکز درمانی</option>
                          <option value="manager">مدیر</option>
                        </select>
                        @error('owner_type')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      @if ($owner_type === 'user')
                        <div class="mb-3">
                          <label for="user_id" class="form-label">انتخاب کاربر <span
                              class="text-danger">*</span></label>
                          <select wire:model="user_id" class="form-select @error('user_id') is-invalid @enderror"
                            id="user_id">
                            <option value="">کاربر را انتخاب کنید</option>
                            @foreach ($users as $user)
                              <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}
                                ({{ $user->mobile }})</option>
                            @endforeach
                          </select>
                          @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @endif

                      @if ($owner_type === 'doctor')
                        <div class="mb-3">
                          <label for="doctor_id" class="form-label">انتخاب پزشک <span
                              class="text-danger">*</span></label>
                          <select wire:model="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror"
                            id="doctor_id">
                            <option value="">پزشک را انتخاب کنید</option>
                            @foreach ($doctors as $doctor)
                              <option value="{{ $doctor->id }}">{{ $doctor->first_name }} {{ $doctor->last_name }}
                                ({{ $doctor->mobile }})</option>
                            @endforeach
                          </select>
                          @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @endif

                      @if ($owner_type === 'medical_center')
                        <div class="mb-3">
                          <label for="medical_center_id" class="form-label">انتخاب مرکز درمانی <span
                              class="text-danger">*</span></label>
                          <select wire:model="medical_center_id"
                            class="form-select @error('medical_center_id') is-invalid @enderror"
                            id="medical_center_id">
                            <option value="">مرکز درمانی را انتخاب کنید</option>
                            @foreach ($medicalCenters as $center)
                              <option value="{{ $center->id }}">{{ $center->name }}</option>
                            @endforeach
                          </select>
                          @error('medical_center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @endif

                      @if ($owner_type === 'manager')
                        <div class="mb-3">
                          <label for="manager_id" class="form-label">انتخاب مدیر <span
                              class="text-danger">*</span></label>
                          <select wire:model="manager_id"
                            class="form-select @error('manager_id') is-invalid @enderror" id="manager_id">
                            <option value="">مدیر را انتخاب کنید</option>
                            @foreach ($managers as $manager)
                              <option value="{{ $manager->id }}">{{ $manager->first_name }}
                                {{ $manager->last_name }}</option>
                            @endforeach
                          </select>
                          @error('manager_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      @endif
                    </div>
                  </div>

                  <!-- File Uploads -->
                  <div class="card mb-3">
                    <div class="card-header">
                      <h6 class="mb-0">آپلود فایل‌ها</h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="media_file" class="form-label">
                          فایل {{ $type === 'image' ? 'تصویر' : 'ویدیو' }} <span class="text-danger">*</span>
                        </label>
                        <input wire:model="media_file" type="file"
                          class="form-control @error('media_file') is-invalid @enderror" id="media_file"
                          accept="{{ $type === 'image' ? 'image/*' : 'video/*' }}">
                        <div class="form-text">
                          @if ($type === 'image')
                            فرمت‌های مجاز: JPG, PNG, GIF - حداکثر 100MB
                          @else
                            فرمت‌های مجاز: MP4, AVI, MOV - حداکثر 100MB
                          @endif
                        </div>
                        @error('media_file')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="mb-3">
                        <label for="thumbnail_file" class="form-label">تصویر بندانگشتی</label>
                        <input wire:model="thumbnail_file" type="file"
                          class="form-control @error('thumbnail_file') is-invalid @enderror" id="thumbnail_file"
                          accept="image/*">
                        <div class="form-text">فرمت‌های مجاز: JPG, PNG - حداکثر 5MB</div>
                        @error('thumbnail_file')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="d-flex justify-content-between">
                <a href="{{ route('admin.panel.stories.index') }}" class="btn btn-secondary">
                  <i class="fas fa-arrow-right"></i> بازگشت
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> ذخیره استوری
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Show/hide live settings based on checkbox
    document.addEventListener('livewire:init', () => {
      Livewire.on('updatedIsLive', (value) => {
        const liveSettings = document.querySelector('.card-body[style*="display: none"]');
        if (liveSettings) {
          liveSettings.style.display = value ? 'block' : 'none';
        }
      });
    });
  </script>
</div>
