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
        <hr>
        <h5 class="mt-4 mb-3">پاسخ‌ها</h5>
        <div class="response-list mb-4">
          <div class="response-card p-3 mb-3 bg-light border rounded">
            <strong>کاربر:</strong>
            {{ $ticket->user ? $ticket->user->first_name . ' ' . $ticket->user->last_name : '---' }}
            <p class="mb-1">{{ $ticket->description }}</p>
            <small class="text-muted">{{ jdate($ticket->created_at)->ago() }}</small>
          </div>
          @forelse ($responses as $response)
            <div
              class="response-card p-3 mb-3 border rounded {{ $response->manager_id ? 'bg-primary text-white' : 'bg-white' }}">
              <strong>
                @if ($response->manager_id)
                  مدیر:
                  {{ $response->manager ? $response->manager->first_name . ' ' . $response->manager->last_name : '---' }}
                @elseif ($response->doctor_id)
                  پزشک:
                  {{ $response->doctor ? $response->doctor->first_name . ' ' . $response->doctor->last_name : '---' }}
                @elseif ($response->secretary_id)
                  منشی:
                  {{ $response->secretary ? $response->secretary->first_name . ' ' . $response->secretary->last_name : '---' }}
                @else
                  ---
                @endif
              </strong>
              <p class="mb-1">{{ $response->message }}</p>
              <small class="text-muted">{{ jdate($response->created_at)->ago() }}</small>
            </div>
          @empty
            <div class="alert alert-info text-center">هیچ پاسخی ثبت نشده است.</div>
          @endforelse
        </div>
        @if ($ticket->status !== 'closed')
          <form wire:submit.prevent="storeResponse" class="mt-4">
            <div class="mb-3">
              <label class="form-label">ارسال پاسخ</label>
              <textarea class="form-control" rows="3" wire:model.defer="responseMessage" placeholder="پاسخ خود را بنویسید..."></textarea>
              @error('responseMessage')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">ارسال پاسخ</button>
          </form>
        @else
          <div class="alert alert-warning mt-3 text-center">این تیکت بسته شده است و امکان ارسال پاسخ وجود ندارد.</div>
        @endif
      @else
        <div class="alert alert-danger">تیکت یافت نشد.</div>
      @endif
    </div>
  </div>
</div>
