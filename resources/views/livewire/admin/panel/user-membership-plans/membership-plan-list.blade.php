<div class="container-fluid py-4" dir="rtl" wire:init="loadPlans">
  <!-- Header Section -->
  <div class="glass-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">

        <h4 class="mb-0 fw-bold text-white">طرح‌های عضویت</h4>
      </div>
      <div class="d-flex gap-3">

        <a href="{{ route('admin.panel.user-membership-plans.create') }}"
          class="btn btn-success btn-sm rounded-pill px-4 py-2 d-flex align-items-center text-white gap-2 shadow-sm hover-shadow-lg transition-all">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14" />
          </svg>
          افزودن  
        </a>
      </div>
    </div>
  </div>

  @if ($readyToLoad)
    <div class="row g-4">
      @forelse($plans as $plan)
        @php
          $planColors = [
              'gold' => [
                  'bg' => 'bg-opacity-5',
                  'text' => 'text-warning',
                  'border' => 'border-warning',
                  'badge' => 'bg-warning',
                  'icon' => '⭐',
                  'color' => '#FFD700',
                  'gradient' => 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)',
              ],
              'silver' => [
                  'bg' => 'bg-opacity-5',
                  'text' => 'text-secondary',
                  'border' => 'border-secondary',
                  'badge' => 'bg-secondary',
                  'icon' => '🥈',
                  'color' => '#C0C0C0',
                  'gradient' => 'linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%)',
              ],
              'bronze' => [
                  'bg' => 'bg-opacity-5',
                  'text' => 'text-danger',
                  'border' => 'border-danger',
                  'badge' => 'bg-danger',
                  'icon' => '🥉',
                  'color' => '#CD7F32',
                  'gradient' => 'linear-gradient(135deg, #CD7F32 0%, #8B4513 100%)',
              ],
          ];
          $planType = $loop->index % 3;
          $colors = match ($planType) {
              0 => $planColors['gold'],
              1 => $planColors['silver'],
              default => $planColors['bronze'],
          };
        @endphp
        <div class="col-md-4">
          <div class="plan-card rounded-5 p-4 h-100 position-relative"
            style="background: white; border: 1px solid {{ $colors['color'] }}30;">
            <!-- Gradient Corner -->
            <div class="gradient-corner" style="background: {{ $colors['gradient'] }}; z-index: 0;"></div>

            <!-- Duration Type Badge -->
            <div class="duration-badge rounded-pill px-3 py-1 position-absolute top-0 start-0 m-3"
              style="background: {{ $colors['color'] }}50; backdrop-filter: blur(5px); z-index: 1;">
              <span class="fw-bold text-white">
                @switch($plan->duration_type)
                  @case('day')
                    روزانه
                  @break

                  @case('week')
                    هفتگی
                  @break

                  @case('month')
                    ماهانه
                  @break

                  @case('year')
                    سالانه
                  @break
                @endswitch
              </span>
            </div>

            <!-- Plan Type Badge -->
            <div class="plan-type-badge rounded-pill px-3 py-1 position-absolute"
              style="background: {{ $colors['color'] }}; backdrop-filter: blur(5px); box-shadow: 0 2px 8px {{ $colors['color'] }}50; z-index: 999; left: 50%; top: -15px; transform: translateX(-50%);">
              <span class="badge-text">{{ $colors['icon'] }} {{ $plan->name }}</span>
            </div>

            <!-- Price Section -->
            <div class="text-center my-4">
              <div class="price-tag {{ $colors['text'] }} mb-2">
                <span
                  class="display-4 fw-bold text-dark text-shadow">{{ number_format($plan->price * (1 - $plan->discount / 100)) }}</span>
                <span class="fs-5 fw-bold text-dark text-shadow">تومان</span>
              </div>
              @if ($plan->discount > 0)
                <div class="discount-badge {{ $colors['badge'] }} text-white rounded-pill px-3 py-1 d-inline-block">
                  {{ $plan->discount }}% تخفیف
                </div>
                <div class="original-price text-dark-50 text-decoration-line-through mt-2">
                  {{ number_format($plan->price) }} تومان
                </div>
              @endif
            </div>

            <!-- Features Section -->
            <div class="features-list">
              <div class="feature-item d-flex align-items-center gap-2 mb-3">
                <div class="feature-icon {{ $colors['text'] }}">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="feature-text">
                  <span class="text-dark-75 fw-medium">مدت زمان:</span>
                  <span class="fw-bold text-dark">{{ $plan->duration_days }} روز</span>
                </div>
              </div>

              <div class="feature-item d-flex align-items-center gap-2 mb-3">
                <div class="feature-icon {{ $colors['text'] }}">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="feature-text">
                  <span class="text-dark-75 fw-medium">تعداد نوبت:</span>
                  <span class="fw-bold text-dark">{{ $plan->appointment_count }} نوبت</span>
                </div>
              </div>

              @if ($plan->description)
                <div class="feature-item d-flex align-items-center gap-2">
                  <div class="feature-icon {{ $colors['text'] }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div class="feature-text">
                    <span class="text-dark-75 fw-medium">توضیحات:</span>
                    <p class="mb-0 small text-dark fw-medium">{{ Str::limit($plan->description, 100) }}</p>
                  </div>
                </div>
              @endif
            </div>

            <!-- Actions Section -->
            <div class="actions mt-4 pt-3 border-top border-dark-25">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                  <a href="{{ route('admin.panel.user-membership-plans.edit', $plan) }}"
                    class="btn btn-sm rounded-pill px-3" style="background: {{ $colors['color'] }}; color: white;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    ویرایش
                  </a>
                  <button wire:click="confirmDelete({{ $plan->id }})" class="btn btn-sm rounded-pill px-3"
                    style="background: #dc3545; color: white;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    </svg>
                    حذف
                  </button>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" wire:click="toggleStatus({{ $plan->id }})"
                    {{ $plan->status ? 'checked' : '' }}>
                  <label class="form-check-label small text-dark fw-bold">
                    {{ $plan->status ? 'فعال' : 'غیرفعال' }}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        @empty
          <div class="col-12">
            <div class="empty-state text-center py-5">
              <div class="empty-state-icon mb-3">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-muted">
                  <path
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h5 class="text-muted mb-0">هیچ طرح عضویتی یافت نشد!</h5>
            </div>
          </div>
        @endforelse
      </div>

      <div class="d-flex justify-content-center mt-4">
        {{ $plans->links() }}
      </div>
    @else
      <div class="loading-state text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
        <h5 class="text-muted mt-3 mb-0">در حال بارگذاری...</h5>
      </div>
    @endif

    @push('scripts')
      <script>
        document.addEventListener('livewire:init', function() {
          Livewire.on('show-alert', (event) => {
            toastr[event.type](event.message);
          });

          Livewire.on('confirm-delete', (event) => {
            Swal.fire({
              title: 'آیا مطمئن هستید؟',
              text: "این عملیات غیرقابل بازگشت است!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'بله، حذف شود!',
              cancelButtonText: 'انصراف'
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('deletePlanConfirmed', {
                  id: event.id
                });
              }
            });
          });
        });
      </script>
    @endpush
    <style>
      .glass-header {
        background: var(--gradient-primary);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 15px var(--shadow);
        border-radius: var(--radius-card);
        transition: all 0.3s ease;
        padding: 0.75rem;
      }

      .glass-input {
        background: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
      }

      .glass-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
      }

      .glass-input:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
        box-shadow: none;
      }

      .plan-card {
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border-radius: 1.5rem !important;
        background: white !important;
        overflow: visible;
        position: relative;
      }

      .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.3);
      }

      .gradient-corner {
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        opacity: 0.1;
        clip-path: polygon(100% 0, 0 0, 100% 100%);
        transition: all 0.3s ease;
      }

      .plan-card:hover .gradient-corner {
        opacity: 0.15;
        transform: scale(1.1);
      }

      .duration-badge {
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      }

      .plan-type-badge {
        font-size: 0.9rem;
        font-weight: 600;
        white-space: nowrap;
        color: white;
        padding: 0.5rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        background: var(--gradient-primary);
      }

      .plan-card:hover .plan-type-badge {
        transform: translateX(-50%) scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      }

      .badge-text {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        letter-spacing: 0.5px;
      }

      .feature-item {
        padding: 0.75rem;
        border-radius: 1rem;
        transition: all 0.2s ease;
        background: rgba(0, 0, 0, 0.03);
      }

      .feature-item:hover {
        background: rgba(0, 0, 0, 0.05);
      }

      .feature-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.05);
      }

      .text-dark-75 {
        color: rgba(0, 0, 0, 0.75) !important;
      }

      .text-dark-50 {
        color: rgba(0, 0, 0, 0.5) !important;
      }

      .border-dark-25 {
        border-color: rgba(0, 0, 0, 0.25) !important;
      }

      .price-tag {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
      }

      .text-shadow {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
      }

      .discount-badge {
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
      }

      .fw-medium {
        font-weight: 500 !important;
      }

      .btn {
        transition: all 0.2s ease;
      }

      .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        color: white;
      }
    </style>
  </div>
