<div>
  @if ($showModal)
    <x-custom-modal id="clinicModal" title="اطلاعات ساعت کاری" size="lg" :show="true">
      <div class="clinic-modal-content">
        <!-- نمایش ساعات کاری -->
        <div class="work-schedule-preview mb-4">
          <h6 class="text-primary mb-3">
            <i class="fas fa-clock me-2"></i>
            ساعت کاری شما به شرح زیر است:
          </h6>

          <div class="work-schedule-grid">
            @foreach ($workSchedules as $schedule)
              @php
                $hours = collect($schedule['work_hours'] ?? []);
              @endphp
              @if ($hours->isNotEmpty())
                <div class="schedule-day-card mb-3">
                  <div class="day-header bg-light p-2 rounded">
                    <h6 class="mb-0 text-dark fw-bold">{{ $schedule['day'] }}</h6>
                  </div>
                  <div class="time-slots p-3">
                    @foreach ($hours as $timeSlot)
                      <div class="time-slot d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">
                          <i class="fas fa-clock me-1"></i>
                          {{ is_array($timeSlot) ? $timeSlot['start'] ?? '' : '' }} -
                          {{ is_array($timeSlot) ? $timeSlot['end'] ?? '' : '' }}
                        </span>
                        @if (is_array($timeSlot) && isset($timeSlot['max_appointments']))
                          <span class="badge bg-info">
                            {{ $timeSlot['max_appointments'] }} نوبت
                          </span>
                        @endif
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        </div>

        <!-- پیام هشدار -->
        <div class="alert alert-warning mb-4">
          <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle text-warning me-2 fs-4"></i>
            <div>
              @if ($hasClinic && $needsWorkHoursAssignment)
                <strong>نیاز به تخصیص ساعات کاری:</strong>
                <br>
                <small class="text-muted">
                  مطب شما
                  <strong>{{ optional($policlinicCenter)->name ?? 'بدون نام' }}</strong>
                  ثبت شده اما هنوز ساعات کاری به آن تخصیص داده نشده است. می‌خواهید همین الآن تخصیص دهید؟
                </small>
              @elseif(!$hasClinic && $needsClinicCreation)
                <strong>هشدار: شما هیچ مطبی ندارید!</strong>
                <br>
                <small class="text-muted">
                  برای استفاده از این ساعات کاری، لطفاً مطب خود را تعریف کنید. سپس می‌توانید ساعات کاری را به آن تخصیص
                  دهید.
                </small>
              @else
                <strong>توجه:</strong>
                <br>
                <small class="text-muted">
                  لطفاً وضعیت مطب و ساعات کاری خود را بررسی کنید.
                </small>
              @endif
            </div>
          </div>
        </div>

        <!-- دکمه‌های عملیات -->
        <div class="modal-actions d-flex gap-2 justify-content-center">
          @if ($hasClinic && $needsWorkHoursAssignment)
            <button type="button" class="btn btn-success btn-lg px-4 w-100" wire:click="assignToMyClinic">
              <i class="fas fa-link me-2"></i>
              تخصیص ساعات کاری به مطب {{ optional($policlinicCenter)->name ?? '' }}
            </button>
            <a href="{{ route('dr-clinic-management') }}" class="btn btn-outline-primary px-4 w-100">
              مدیریت مطب‌ها
            </a>
          @elseif(!$hasClinic && $needsClinicCreation)
            <button type="button" class="btn btn-primary btn-lg px-4 w-100" wire:click="goToCreateClinic">
              <i class="fas fa-plus me-2"></i>
              افزودن مطب
            </button>
          @else
            <a href="{{ route('dr-clinic-management') }}" class="btn btn-outline-primary px-4 w-100">
              مدیریت مطب‌ها
            </a>
          @endif
        </div>
      </div>
    </x-custom-modal>
  @endif

  <style>
    .clinic-modal-content {
      max-width: 100%;
    }

    .work-schedule-preview {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
    }

    .work-schedule-grid {
      display: grid;
      gap: 15px;
    }

    .schedule-day-card {
      background: white;
      border-radius: 8px;
      border: 1px solid #e9ecef;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .day-header {
      border-bottom: 1px solid #e9ecef;
    }

    .time-slots {
      background: white;
    }

    .time-slot {
      padding: 8px 0;
    }

    .time-slot .badge {
      font-size: 0.875rem;
      padding: 6px 12px;
    }

    .modal-actions {
      border-top: 1px solid #e9ecef;
      padding-top: 20px;
      margin-top: 20px;
    }

    .modal-actions .btn {
      min-width: 140px;
      font-weight: 500;
    }
  </style>
</div>
