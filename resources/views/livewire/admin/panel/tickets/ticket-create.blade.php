<div class="container py-4" dir="rtl">
  <div class="card shadow-sm rounded-2">
    <div class="card-header bg-success text-white">
      <h4 class="mb-0">افزودن تیکت جدید</h4>
    </div>
    <div class="card-body">
      <form wire:submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label">عنوان تیکت <span class="text-danger">*</span></label>
          <input type="text" class="form-control" wire:model.defer="title">
          @error('title')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label class="form-label">توضیحات <span class="text-danger">*</span></label>
          <textarea class="form-control" rows="4" wire:model.defer="description"></textarea>
          @error('description')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label class="form-label">کاربر</label>
          <select class="form-select" wire:model.defer="user_id">
            <option value="">انتخاب کاربر</option>
            @foreach ($users as $user)
              <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
            @endforeach
          </select>
          @error('user_id')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label class="form-label">پزشک</label>
          <select class="form-select" wire:model.defer="doctor_id">
            <option value="">انتخاب پزشک</option>
            @foreach ($doctors as $doctor)
              <option value="{{ $doctor->id }}">{{ $doctor->first_name }} {{ $doctor->last_name }}</option>
            @endforeach
          </select>
          @error('doctor_id')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label class="form-label">وضعیت</label>
          <select class="form-select" wire:model.defer="status">
            <option value="open">باز</option>
            <option value="answered">پاسخ داده شده</option>
            <option value="pending">در حال بررسی</option>
            <option value="closed">بسته</option>
          </select>
          @error('status')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <button type="submit" class="btn btn-success w-100">ثبت تیکت</button>
      </form>
    </div>
  </div>
</div>
