<div>
 <h4 class="py-3 mb-4">
  <span class="text-muted fw-light">تخصص‌ها /</span> اضافه کردن تخصص جدید
 </h4>

 @if ($successMessage)
  <div class="alert alert-success">
   {{ $successMessage }}
  </div>
  <script>
   setTimeout(function () {
    window.location.href = "{{ route('admin.Dashboard.specialty.index') }}";
   }, 5000);
  </script>
 @endif

 <div class="card">
  <div class="card-header">
   <h5 class="card-title">اضافه کردن تخصص</h5>
  </div>
  <div class="card-body">
   <form wire:submit.prevent="store">
    <div class="row">
     <div class="col-md-12">
      <label class="form-label">نام تخصص <span class="text-danger">*</span></label>
      <input type="text" class="form-control" wire:model="name" placeholder="مثلا: متخصص قلب">
      @error('name') <span class="text-danger">{{ $message }}</span> @enderror
     </div>

     <input type="hidden" wire:model.lazy="level" value="1">

     <div class="col-12 mt-4">
      <button type="submit" class="btn btn-success">اضافه کردن</button>
      <a href="{{ route('admin.Dashboard.specialty.index') }}" class="btn btn-secondary">بازگشت</a>
     </div>
    </div>
   </form>
  </div>
 </div>
</div>