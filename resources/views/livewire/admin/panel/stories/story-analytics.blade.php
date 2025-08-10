<div class="container py-2 mt-3" dir="rtl" wire:init="loadAnalytics">
  <!-- Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0">
          <i class="fas fa-chart-line text-primary me-2"></i>
          آمار و تحلیل استوری‌ها
        </h2>
        <div class="d-flex gap-2">
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
        <div class="card bg-primary text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h6 class="card-title">کل استوری‌ها</h6>
                <h3 class="mb-0">{{ number_format($totalStories) }}</h3>
              </div>
              <div class="align-self-center">
                <i class="fas fa-file-video fa-2x opacity-75"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-success text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h6 class="card-title">کل بازدیدها</h6>
                <h3 class="mb-0">{{ number_format($totalViews) }}</h3>
              </div>
              <div class="align-self-center">
                <i class="fas fa-eye fa-2x opacity-75"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-warning text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h6 class="card-title">کل لایک‌ها</h6>
                <h3 class="mb-0">{{ number_format($totalLikes) }}</h3>
              </div>
              <div class="align-self-center">
                <i class="fas fa-heart fa-2x opacity-75"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-info text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h6 class="card-title">نرخ تعامل</h6>
                <h3 class="mb-0">{{ $engagementRate }}%</h3>
              </div>
              <div class="align-self-center">
                <i class="fas fa-percentage fa-2x opacity-75"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Average Statistics -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">میانگین بازدید هر استوری</h6>
            <h4 class="text-primary">{{ number_format($averageViewsPerStory) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">میانگین لایک هر استوری</h6>
            <h4 class="text-warning">{{ number_format($averageLikesPerStory) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">روند بازدیدها</h6>
          </div>
          <div class="card-body">
            <canvas id="viewsChart" width="400" height="200"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">روند لایک‌ها</h6>
          </div>
          <div class="card-body">
            <canvas id="likesChart" width="400" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Stories and Performance -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">برترین استوری‌ها</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>عنوان</th>
                    <th>بازدید</th>
                    <th>لایک</th>
                    <th>تعامل</th>
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
                          class="badge bg-{{ $story['engagement_rate'] > 5 ? 'success' : ($story['engagement_rate'] > 2 ? 'warning' : 'danger') }}">
                          {{ $story['engagement_rate'] }}%
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center">داده‌ای موجود نیست</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">بازدید بر اساس نوع کاربر</h6>
          </div>
          <div class="card-body">
            <canvas id="viewsByTypeChart" width="400" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Performance Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">جدول عملکرد استوری‌ها</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>عنوان</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>تاریخ ایجاد</th>
                    <th>بازدید</th>
                    <th>لایک</th>
                    <th>نرخ تعامل</th>
                    <th>عملیات</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($storyPerformanceData as $story)
                    <tr>
                      <td>{{ Str::limit($story['title'], 40) }}</td>
                      <td>
                        <span class="badge bg-secondary">{{ $story['type'] }}</span>
                      </td>
                      <td>
                        <span class="badge bg-{{ $story['status'] === 'active' ? 'success' : 'danger' }}">
                          {{ $story['status'] === 'active' ? 'فعال' : 'غیرفعال' }}
                        </span>
                      </td>
                      <td>{{ $story['created_at'] }}</td>
                      <td>{{ number_format($story['views']) }}</td>
                      <td>{{ number_format($story['likes']) }}</td>
                      <td>
                        <span
                          class="badge bg-{{ $story['engagement_rate'] > 5 ? 'success' : ($story['engagement_rate'] > 2 ? 'warning' : 'danger') }}">
                          {{ $story['engagement_rate'] }}%
                        </span>
                      </td>
                      <td>
                        <a href="{{ route('admin.panel.stories.edit', $story['id']) }}"
                          class="btn btn-sm btn-primary">
                          <i class="fas fa-edit"></i>
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center">داده‌ای موجود نیست</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @else
    <!-- Loading State -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
            <p class="mt-2">در حال بارگذاری آمار...</p>
          </div>
        </div>
      </div>
    </div>
    ر
  @endif
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
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true
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
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true
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
                  'rgba(255, 99, 132, 0.8)',
                  'rgba(54, 162, 235, 0.8)',
                  'rgba(255, 205, 86, 0.8)',
                  'rgba(75, 192, 192, 0.8)',
                  'rgba(153, 102, 255, 0.8)'
                ]
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  position: 'bottom'
                }
              }
            }
          });
        }
      }

      // Initial chart update
      if (@json($readyToLoad)) {
        updateCharts();
      }
    });
  </script>


</div>
