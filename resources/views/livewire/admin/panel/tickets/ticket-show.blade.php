<div class="container py-4" dir="rtl">
  <div class="card shadow-sm rounded-2">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">مشاهده تیکت</h4>
    </div>
    <div class="card-body">
      @if ($ticket)
        <div class="mb-3">
          <strong>عنوان:</strong> {{ $ticket->title }}
        </div>
        <div class="mb-3">
          <strong>کاربر:</strong>
          @if ($ticket->user)
            {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
          @else
            -
          @endif
        </div>
        <div class="mb-3">
          <strong>پزشک:</strong>
          @if ($ticket->doctor)
            {{ $ticket->doctor->first_name }} {{ $ticket->doctor->last_name }}
          @else
            -
          @endif
        </div>
        <div class="mb-3">
          <strong>وضعیت:</strong>
          @php
            $statusFa =
                [
                    'open' => 'باز',
                    'answered' => 'پاسخ داده شده',
                    'pending' => 'در حال بررسی',
                    'closed' => 'بسته',
                ][$ticket->status] ?? $ticket->status;
          @endphp
          <span class="badge bg-info">{{ $statusFa }}</span>
        </div>
        <div class="mb-3">
          <strong>توضیحات:</strong>
          <div class="border rounded p-2">{{ $ticket->description }}</div>
        </div>
        <div class="mb-3">
          <strong>تاریخ ثبت:</strong> {{ jdate($ticket->created_at)->format('Y/m/d H:i') }}
        </div>
      @else
        <div class="alert alert-danger">تیکت یافت نشد.</div>
      @endif
    </div>
  </div>
</div>
