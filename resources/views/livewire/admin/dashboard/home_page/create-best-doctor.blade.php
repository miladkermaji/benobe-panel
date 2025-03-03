<div>
 <div class="card">
  <div class="card-header">
   <h5 class="card-title">افزودن پزشک برتر</h5>
  </div>
  <div class="card-body">
   <form wire:submit.prevent="save">
    <div class="form-group">
     <label>انتخاب پزشک:</label>
     <select id="doctor_select" wire:model="doctor_id" class="form-control">
      <option value="">انتخاب پزشک</option>
      @foreach ($doctors as $doctor)
       <option value="{{ $doctor->id }}">
        {{ $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->national_code . ')' }}</option>
      @endforeach
     </select>
     @error('doctor_id')
      <span class="text-danger">{{ $message }}</span>
     @enderror
    </div>

    <div class="form-group mt-3">
     <label>انتخاب بیمارستان:</label>
     <select id="hospital_select" wire:model="hospital_id" class="form-control">
      <option value="">انتخاب بیمارستان (اختیاری)</option>
      @foreach ($hospitals as $hospital)
       <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
      @endforeach
     </select>
    </div>

    <div class="form-check mt-3">
     <input type="checkbox" wire:model="best_doctor" class="form-check-input">
     <label class="form-check-label">پزشک برتر</label>
    </div>

    <div class="form-check mt-2">
     <input type="checkbox" wire:model="best_consultant" class="form-check-input">
     <label class="form-check-label">مشاور تلفنی برتر</label>
    </div>

    <div class="mt-4">
     <button type="submit" class="btn btn-success">افزودن</button>
     <a href="{{ route('admin.Dashboard.home_page.index') }}" class="btn btn-secondary">بازگشت</a>
    </div>
   </form>
  </div>
 </div>

 <script>
  document.addEventListener("DOMContentLoaded", function() {
   new TomSelect("#doctor_select", {
    create: false,
    sortField: "text"
   });
   new TomSelect("#hospital_select", {
    create: false,
    sortField: "text"
   });
  });

  document.addEventListener("livewire:load", function() {
   new TomSelect("#doctor_select", {
    create: false,
    sortField: "text"
   });
   new TomSelect("#hospital_select", {
    create: false,
    sortField: "text"
   });
  });
 </script>
</div>
