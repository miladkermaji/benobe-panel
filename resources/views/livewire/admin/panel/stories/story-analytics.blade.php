<div class="container-fluid py-4" dir="rtl" wire:init="loadAnalytics">
  <!-- Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <svg class="text-primary" width="32" height="32" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
              </path>
            </svg>
          </div>
          <div>
            <h2 class="mb-0 fw-bold">آمار و تحلیل استوری‌ها</h2>
            <p class="text-muted mb-0">مدیریت و تحلیل عملکرد استوری‌ها</p>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row gap-2">
          <select wire:model.live="selectedPeriod" class="form-select" style="width: auto;">
            @foreach ($periods as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
          <select wire:model.live="selectedStory" class="form-select" style="width: auto;">
            <option value="">همه استوری‌ها</option>
            @foreach ($stories as $story)
              <option value="{{ $story->id }}">{{ $story->title }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  @if ($readyToLoad)
    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-primary text-white h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-1">کل استوری‌ها</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($totalStories) }}</h3>
              </div>
              <div class="opacity-75">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                  </path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-success text-white h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-1">کل بازدیدها</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($totalViews) }}</h3>
              </div>
              <div class="opacity-75">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                  </path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-warning text-white h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-1">کل لایک‌ها</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($totalLikes) }}</h3>
              </div>
              <div class="opacity-75">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                  </path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-info text-white h-100 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-1">نرخ تعامل</h6>
                <h3 class="mb-0 fw-bold">{{ $engagementRate }}%</h3>
              </div>
              <div class="opacity-75">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Average Statistics -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h6 class="card-title text-muted mb-2">میانگین بازدید هر استوری</h6>
            <h4 class="text-primary fw-bold mb-0">{{ number_format($averageViewsPerStory) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h6 class="card-title text-muted mb-2">میانگین لایک هر استوری</h6>
            <h4 class="text-warning fw-bold mb-0">{{ number_format($averageLikesPerStory) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">روند بازدیدها</h6>
          </div>
          <div class="card-body">
            <div class="position-relative" style="height: 300px;">
              <canvas id="viewsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">روند لایک‌ها</h6>
          </div>
          <div class="card-body">
            <div class="position-relative" style="height: 300px;">
              <canvas id="likesChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Stories and Performance -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">برترین استوری‌ها</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="border-0">عنوان</th>
                    <th class="border-0">بازدید</th>
                    <th class="border-0">لایک</th>
                    <th class="border-0">تعامل</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($topStoriesData as $story)
                    <tr>
                      <td>
                        <a href="{{ route('admin.panel.stories.edit', $story['id']) }}" class="text-decoration-none">
                          {{ Str::limit($story['title'], 30) }}
                        </a>
                      </td>
                      <td>{{ number_format($story['views']) }}</td>
                      <td>{{ number_format($story['likes']) }}</td>
                      <td>
                        <span
                          class="badge {{ $story['engagement_rate'] > 5 ? 'bg-success' : ($story['engagement_rate'] > 2 ? 'bg-warning' : 'bg-danger') }}">
                          {{ $story['engagement_rate'] }}%
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted">داده‌ای موجود نیست</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">بازدید بر اساس نوع کاربر</h6>
          </div>
          <div class="card-body">
            <div class="position-relative" style="height: 300px;">
              <canvas id="viewsByTypeChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8 mb-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="جستجو در استوری‌ها..."
              class="form-control">
          </div>
          <div class="col-md-4 mb-2">
            <select wire:model.live="perPage" class="form-select">
              <option value="10">10 آیتم</option>
              <option value="25">25 آیتم</option>
              <option value="50">50 آیتم</option>
              <option value="100">100 آیتم</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Performance Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0">
        <h6 class="mb-0 fw-bold">جدول عملکرد استوری‌ها</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="border-0">عنوان</th>
                <th class="border-0">نوع</th>
                <th class="border-0">وضعیت</th>
                <th class="border-0">تاریخ ایجاد</th>
                <th class="border-0">بازدید</th>
                <th class="border-0">لایک</th>
                <th class="border-0">نرخ تعامل</th>
                <th class="border-0">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @forelse($this->paginatedStories as $story)
                <tr>
                  <td>
                    <div class="fw-medium">{{ Str::limit($story->title, 40) }}</div>
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ $story->type }}</span>
                  </td>
                  <td>
                    <span class="badge {{ $story->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                      {{ $story->status === 'active' ? 'فعال' : 'غیرفعال' }}
                    </span>
                  </td>
                  <td>{{ $story->created_at->format('Y-m-d') }}</td>
                  <td>{{ number_format($story->views_count) }}</td>
                  <td>{{ number_format($story->likes_count) }}</td>
                  <td>
                    @php
                      $engagementRate =
                          $story->views_count > 0 ? round(($story->likes_count / $story->views_count) * 100, 2) : 0;
                    @endphp
                    <span
                      class="badge {{ $engagementRate > 5 ? 'bg-success' : ($engagementRate > 2 ? 'bg-warning' : 'bg-danger') }}">
                      {{ $engagementRate }}%
                    </span>
                  </td>
                  <td>
                    <a href="{{ route('admin.panel.stories.edit', $story->id) }}" class="btn btn-sm btn-primary">
                      <svg class="me-1" width="16" height="16" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                      </svg>
                      ویرایش
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">داده‌ای موجود نیست</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        @if ($this->paginatedStories->hasPages())
          <div class="card-footer bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="text-muted">
                نمایش {{ $this->paginatedStories->firstItem() ?? 0 }} تا
                {{ $this->paginatedStories->lastItem() ?? 0 }} از {{ $this->paginatedStories->total() }} نتیجه
              </div>
              <div>
                {{ $this->paginatedStories->links() }}
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  @else
    <!-- Loading State -->
    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
              <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
            <p class="mb-0 fw-medium">در حال بارگذاری آمار...</p>
            <p class="text-muted">لطفاً صبر کنید</p>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('livewire:init', () => {
    let viewsChart, likesChart, viewsByTypeChart;

    Livewire.on('analytics-updated', () => {
      updateCharts();
    });

    function updateCharts() {
      // Views Chart
      if (viewsChart) {
        viewsChart.destroy();
      }

      const viewsCtx = document.getElementById('viewsChart');
      if (viewsCtx) {
        const viewsData = @json($viewsChartData);
        viewsChart = new Chart(viewsCtx, {
          type: 'line',
          data: {
            labels: viewsData.map(item => item.date),
            datasets: [{
              label: 'بازدیدها',
              data: viewsData.map(item => item.count),
              borderColor: 'rgb(59, 130, 246)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.4,
              fill: true,
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                  font: {
                    size: 12
                  }
                }
              },
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  font: {
                    size: 12
                  }
                }
              }
            },
            elements: {
              point: {
                radius: 4,
                hoverRadius: 6
              }
            }
          }
        });
      }

      // Likes Chart
      if (likesChart) {
        likesChart.destroy();
      }

      const likesCtx = document.getElementById('likesChart');
      if (likesCtx) {
        const likesData = @json($likesChartData);
        likesChart = new Chart(likesCtx, {
          type: 'line',
          data: {
            labels: likesData.map(item => item.date),
            datasets: [{
              label: 'لایک‌ها',
              data: likesData.map(item => item.count),
              borderColor: 'rgb(239, 68, 68)',
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              tension: 0.4,
              fill: true,
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                  font: {
                    size: 12
                  }
                }
              },
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  font: {
                    size: 12
                  }
                }
              }
            },
            elements: {
              point: {
                radius: 4,
                hoverRadius: 6
              }
            }
          }
        });
      }

      // Views By Type Chart
      if (viewsByTypeChart) {
        viewsByTypeChart.destroy();
      }

      const viewsByTypeCtx = document.getElementById('viewsByTypeChart');
      if (viewsByTypeCtx) {
        const viewsByTypeData = @json($viewsByTypeData);
        viewsByTypeChart = new Chart(viewsByTypeCtx, {
          type: 'doughnut',
          data: {
            labels: viewsByTypeData.map(item => item.type),
            datasets: [{
              data: viewsByTypeData.map(item => item.count),
              backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(139, 92, 246, 0.8)'
              ],
              borderWidth: 2,
              borderColor: '#ffffff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  padding: 20,
                  usePointStyle: true,
                  font: {
                    size: 12
                  }
                }
              }
            },
            cutout: '60%'
          }
        });
      }
    }

    // Initial chart update
    if (@json($readyToLoad)) {
      updateCharts();
    }

    // Handle window resize
    window.addEventListener('resize', () => {
      if (viewsChart) viewsChart.resize();
      if (likesChart) likesChart.resize();
      if (viewsByTypeChart) viewsByTypeChart.resize();
    });
  });
</script>
