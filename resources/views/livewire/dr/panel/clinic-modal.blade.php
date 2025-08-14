<div>
  @if ($showModal)
    <x-custom-modal id="clinicModal" title="اطلاعات ساعت کاری" size="lg" :show="true">
      <div class="clinic-modal-content">
        <!-- نمایش ساعات کاری -->
        <section class="work-schedule-preview">
          <h6 class="section-title">
            <i class="fas fa-clock me-2"></i>
            ساعت کاری شما به شرح زیر است:
          </h6>
          <div class="work-schedule-grid">
            @foreach ($workSchedules as $schedule)
              @php
                $hours = collect($schedule['work_hours'] ?? []);
              @endphp
              @if ($hours->isNotEmpty())
                <article class="schedule-day-card">
                  <header class="day-header">
                    <h6 class="day-title">{{ $schedule['day'] }}</h6>
                  </header>
                  <div class="time-slots">
                    @foreach ($hours as $timeSlot)
                      <div class="time-slot">
                        <span class="badge badge-primary">
                          <i class="fas fa-clock me-1"></i>
                          {{ is_array($timeSlot) ? ($timeSlot['start'] ?? '') . ' - ' . ($timeSlot['end'] ?? '') : '' }}
                        </span>
                        @if (is_array($timeSlot) && isset($timeSlot['max_appointments']))
                          <span class="badge badge-info">
                            {{ $timeSlot['max_appointments'] }} نوبت
                          </span>
                        @endif
                      </div>
                    @endforeach
                  </div>
                </article>
              @endif
            @endforeach
          </div>
        </section>

        <!-- پیام هشدار -->
        <section class="alert-section">
          <div class="alert alert-warning">
            <div class="alert-content">
              <i class="fas fa-exclamation-triangle text-warning me-2"></i>
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
                    برای استفاده از این ساعات کاری، لطفاً مطب خود را تعریف کنید. سپس می‌توانید ساعات کاری را به آن تخصیص دهید.
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
        </section>

        <!-- دکمه‌های عملیات -->
        <section class="modal-actions">
          @if ($hasClinic && $needsWorkHoursAssignment)
            <button type="button" class="btn btn-success btn-action" wire:click="assignToMyClinic">
              <i class="fas fa-link me-2"></i>
              تخصیص ساعات کاری به مطب {{ optional($policlinicCenter)->name ?? '' }}
            </button>
            <a href="{{ route('dr-clinic-management') }}" class="btn btn-outline-primary btn-action">
              مدیریت مطب‌ها
            </a>
          @elseif(!$hasClinic && $needsClinicCreation)
            <button type="button" class="btn btn-primary btn-action" wire:click="goToCreateClinic">
              <i class="fas fa-plus me-2"></i>
              افزودن مطب
            </button>
          @else
            <a href="{{ route('dr-clinic-management') }}" class="btn btn-outline-primary btn-action">
              مدیریت مطب‌ها
            </a>
          @endif
        </section>
      </div>
    </x-custom-modal>
  @endif

  <style>
    :root {
      --primary-color: #007bff;
      --info-color: #17a2b8;
      --warning-color: #ffc107;
      --light-bg: #f8f9fa;
      --border-color: #e9ecef;
      --shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      --spacing-sm: 8px;
      --spacing-md: 15px;
      --spacing-lg: 20px;
      --border-radius-sm: 8px;
      --border-radius-md: 12px;
      --font-size-sm: 0.875rem;
      --font-size-md: 1rem;
    }

    .clinic-modal-content {
      max-width: 100%;
      padding: var(--spacing-lg);
    }

    .work-schedule-preview {
      background: var(--light-bg);
      border-radius: var(--border-radius-md);
      padding: var(--spacing-lg);
      margin-bottom: var(--spacing-lg);
    }

    .section-title {
      color: var(--primary-color);
      margin-bottom: var(--spacing-md);
      font-weight: 600;
    }

    .work-schedule-grid {
      display: grid;
      gap: var(--spacing-md);
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .schedule-day-card {
      background: white;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius-sm);
      overflow: hidden;
      box-shadow: var(--shadow);
    }

    .day-header {
      background: var(--light-bg);
      padding: var(--spacing-sm);
      border-bottom: 1px solid var(--border-color);
    }

    .day-title {
      margin: 0;
      color: #343a40;
      font-weight: 600;
    }

    .time-slots {
      padding: var(--spacing-md);
    }

    .time-slot {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      padding: var(--spacing-sm) 0;
    }

    .badge {
      padding: 6px 12px;
      font-size: var(--font-size-sm);
      border-radius: 4px;
    }

    .badge-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .badge-info {
      background-color: var(--info-color);
      color: white;
    }

    .alert-section {
      margin-bottom: var(--spacing-lg);
    }

    .alert {
      border-radius: var(--border-radius-sm);
      padding: var(--spacing-md);
    }

    .alert-content {
      display: flex;
      align-items: flex-start;
      gap: var(--spacing-sm);
    }

    .alert-content i {
      font-size: 1.5rem;
    }

    .modal-actions {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-md);
      justify-content: center;
      padding-top: var(--spacing-lg);
      border-top: 1px solid var(--border-color);
    }

    .btn-action {
      flex: 1;
      min-width: 160px;
      padding: var(--spacing-sm) var(--spacing-md);
      font-weight: 500;
      font-size: var(--font-size-md);
      border-radius: var(--border-radius-sm);
    }

    @media (max-width: 576px) {
      .work-schedule-grid {
        grid-template-columns: 1fr;
      }

      .btn-action {
        min-width: 100%;
      }
    }
  </style>
</div>