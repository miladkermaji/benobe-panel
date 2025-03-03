<div>
 <div class="container-fluid py-4">
  <!-- هدر اصلی -->
  <header class="glass-header p-3 rounded-3 mb-4 shadow-lg animate__fadeIn">
   <div class="d-flex align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
     <i class="fas fa-comments fs-4 text-white animate__bounce"></i>
     <h4 class="mb-0 fw-bold text-white">ویرایش نظر</h4>
    </div>
   <div>
    <a href="{{ route('admin.content.doctors.comment-doctor.index') }}" class="btn btn-light">بازگشت</a>
   </div>
   </div>
  </header>
  <!-- بدنه اصلی -->
  <div class="row g-4">
   <!-- کارت ویرایش نظر -->
   <div class="col-12 col-md-6">
    <div class="card h-100 shadow-lg border-0 animate__fadeInLeft">
     <div class="card-header bg-gradient-primary text-white">
      <h5 class="card-title mb-0">جزئیات نظر</h5>
     </div>
     <div class="card-body p-4">
      <form wire:submit.prevent="updateComment" class="needs-validation" novalidate>
       <input type="hidden" wire:model="commentId" value="{{ $comment->id }}">
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">صفحه نمایش دیدگاه:</label>
        <div>
         <a href="#" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 animate__pulse">
          <i class="fas fa-eye"></i> نمایش صفحه 
         </a>
        </div>
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">IP:</label>
        <p class="mb-0 text-secondary">{{ $comment->ip_address ?? 'ندارد' }}</p>
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">تاریخ ارسال:</label>
        <p class="mb-0 text-secondary">
         {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($comment->created_at))->format('Y/m/d H:i') }}
        </p>
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">نام کاربر:</label>
        <p class="mb-0 text-secondary">{{ $comment->user_name }} @if ($comment->user_phone)
          -
          {{ $comment->user_phone }}
         @endif
       </p>
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">دکتر:</label>
        <p class="mb-0 text-secondary">
         {{ $comment->doctor->first_name . ' ' . $comment->doctor->last_name }}
        </p>
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">نظر <span class="text-danger">*</span></label>
        <textarea wire:model="commentText" class="form-control form-control-lg" style="height:150px"
         placeholder="متن نظر را وارد کنید" required>{{ $commentText }}</textarea>
        @error('commentText')
         <span class="text-danger small">{{ $message }}</span>
        @enderror
       </div>
       <div class="mb-3">
        <label class="form-label fw-bold text-muted">وضعیت <span class="text-danger">*</span></label>
        <select wire:model="commentStatus" class="form-select form-select-lg" required>
         <option value="0" {{ $commentStatus == 0 ? 'selected' : '' }}>غیرفعال</option>
         <option value="1" {{ $commentStatus == 1 ? 'selected' : '' }}>فعال</option>
        </select>
        @error('commentStatus')
         <span class="text-danger small">{{ $message }}</span>
        @enderror
       </div>
       <button type="submit"
        class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2 animate__fadeIn">
        <i class="fas fa-save"></i> ثبت تغییرات
       </button>
      </form>
     </div>
    </div>
   </div>
   <!-- کارت پاسخ -->
<div class="col-12 col-md-6">
    <div class="card h-100 shadow-lg border-0 animate__fadeInRight">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0">پاسخ به نظر</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="addReply" class="needs-validation" novalidate>
                <input type="hidden" wire:model="commentId" value="{{ $comment->id }}">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">پاسخ <span class="text-danger">*</span></label>
                    <textarea wire:model="replyText" class="form-control form-control-lg" style="height: 150px"
                        placeholder="متن پاسخ را وارد کنید" required>{{ $replyText }}</textarea>
                    @error('replyText')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit"
                    class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2 animate__fadeIn">
                    <i class="fas fa-reply"></i> ثبت پاسخ
                </button>
            </form>
            @if ($comment->reply)
                <div class="mt-3 p-3 bg-light rounded">
                    <label class="form-label fw-bold text-muted">پاسخ ثبت‌شده:</label>
                    <p class="mb-0 text-secondary">{{ $comment->reply }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
  </div>
 </div>
 <!-- استایل‌ها -->
 <style>
  .glass-header {
   background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(124, 58, 237, 0.7));
   backdrop-filter: blur(12px);
   border: 1px solid rgba(255, 255, 255, 0.3);
   box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
   transition: all 0.3s ease;
  }

  .glass-header:hover {
   box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
  }

  .card {
   border-radius: 12px;
   overflow: hidden;
   transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
   transform: translateY(-5px);
   box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  .bg-gradient-primary {
   background: linear-gradient(45deg, #4f46e5, #7c3aed);
  }

  .bg-gradient-info {
   background: linear-gradient(45deg, #0d6efd, #0dcaf0);
  }

  .form-control-lg,
  .form-select-lg {
   border-radius: 8px;
   padding: 0.875rem 1rem;
   font-size: 1.1rem;
   box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .form-control-lg:focus,
  .form-select-lg:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
  }

  .btn-lg {
   padding: 1rem 1.5rem;
   font-size: 1.1rem;
   border-radius: 8px;
   transition: all 0.3s ease;
  }

  .btn-primary,
  .btn-success {
   transition: all 0.3s ease;
  }

  .btn-primary:hover,
  .btn-success:hover {
   transform: translateY(-2px);
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .text-muted {
   color: #6c757d !important;
  }

  .text-danger {
   color: #dc3545;
  }

  .animate__fadeIn,
  .animate__fadeInLeft,
  .animate__fadeInRight {
   animation-duration: 0.8s;
  }

  .animate__pulse {
   animation: pulse 1.5s infinite;
  }

  @keyframes pulse {
   0% {
    transform: scale(1);
   }

   50% {
    transform: scale(1.05);
   }

   100% {
    transform: scale(1);
   }
  }

  .needs-validation .form-control:invalid,
  .needs-validation .form-select:invalid {
   border-color: #dc3545;
   box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
  }

  .needs-validation .form-control:valid,
  .needs-validation .form-select:valid {
   border-color: #198754;
   box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
  }

  .needs-validation .form-control:invalid:focus,
  .needs-validation .form-select:invalid:focus {
   border-color: #dc3545;
   box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
  }

  .needs-validation .form-control:valid:focus,
  .needs-validation .form-select:valid:focus {
   border-color: #198754;
   box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
  }
 </style>
 <script>
  document.addEventListener('livewire:initialized', () => {
   // اعتبارسنجی کلاینت‌ساید برای فرم‌ها
   (function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
     form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
       event.preventDefault();
       event.stopPropagation();
      }
      form.classList.add('was-validated');
     }, false);
    });
   })();
  });
 </script>
</div>
