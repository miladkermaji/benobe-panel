<!-- Desktop Table View -->
<div class="table-responsive d-none d-md-block">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>تاریخ</th>
        <th>ساعت ورود</th>
        <th>ساعت خروج</th>
        <th>وضعیت</th>
        <th>آی‌پی</th>
        <th>دستگاه</th>
        <th>حذف</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($secretaryLogs as $log)
        <tr>
          <td>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($log->login_at))->format('Y/m/d') }}</td>
          <td>{{ \Carbon\Carbon::parse($log->login_at)->format('H:i') }}</td>
          <td>{{ $log->logout_at ? \Carbon\Carbon::parse($log->logout_at)->format('H:i') : 'هنوز خارج نشده' }}</td>
          <td class="{{ $log->logout_at ? 'text-danger' : 'text-success' }}">
            {{ $log->logout_at ? 'آفلاین' : 'آنلاین' }}
          </td>
          <td>{{ $log->ip_address }}</td>
          <td>{{ $log->device }}</td>
          <td>
            <button class="btn btn-light btn-sm delete-log" data-id="{{ $log->id }}">
              <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted">هیچ لاگی یافت نشد.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Mobile Card View -->
<div class="notes-cards d-md-none">
  @forelse ($secretaryLogs as $log)
    <div class="note-card mb-2" data-id="{{ $log->id }}">
      <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2">
        <div class="d-flex align-items-center gap-2">
          <span
            class="fw-bold">{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($log->login_at))->format('Y/m/d') }}</span>
          <span class="badge {{ $log->logout_at ? 'bg-danger' : 'bg-success' }}">
            {{ $log->logout_at ? 'آفلاین' : 'آنلاین' }}
          </span>
        </div>
        <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-log" data-id="{{ $log->id }}"
          title="حذف">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
          </svg>
        </button>
      </div>
      <div class="note-card-body px-2 py-2">
        <div class="note-card-item d-flex justify-content-between align-items-center py-1">
          <span class="note-card-label">ساعت ورود:</span>
          <span class="note-card-value">{{ \Carbon\Carbon::parse($log->login_at)->format('H:i') }}</span>
        </div>
        <div class="note-card-item d-flex justify-content-between align-items-center py-1">
          <span class="note-card-label">ساعت خروج:</span>
          <span
            class="note-card-value">{{ $log->logout_at ? \Carbon\Carbon::parse($log->logout_at)->format('H:i') : 'هنوز خارج نشده' }}</span>
        </div>
        <div class="note-card-item d-flex justify-content-between align-items-center py-1">
          <span class="note-card-label">آی‌پی:</span>
          <span class="note-card-value">{{ $log->ip_address }}</span>
        </div>
        <div class="note-card-item d-flex justify-content-between align-items-center py-1">
          <span class="note-card-label">دستگاه:</span>
          <span class="note-card-value">{{ $log->device }}</span>
        </div>
      </div>
    </div>
  @empty
    <div class="text-center py-4">
      <div class="d-flex justify-content-center align-items-center flex-column">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="text-muted mb-2">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <p class="text-muted fw-medium">هیچ لاگی یافت نشد.</p>
      </div>
    </div>
  @endforelse
</div>

<div class="pagination-links w-100 d-flex justify-content-center">
  {{ $secretaryLogs->links('pagination::bootstrap-4') }}
</div>
