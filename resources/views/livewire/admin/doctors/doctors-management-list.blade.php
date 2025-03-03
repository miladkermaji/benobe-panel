<div>
 <div class="wrapper-md">
  <div class="panel panel-default shadow-sm mb-4">
   <div class="panel-heading py-2">جستجو</div>
   <div class="panel-body p-3">
    <div class="row g-3">
     <div class="col-md-3">
      <label class="fw-bold mb-1">موبایل:</label>
      <input type="text" class="form-control input-shiny" wire:model.live="searchMobile" placeholder="شماره موبایل">
     </div>
     <div class="col-md-3">
      <label class="fw-bold mb-1">نام و نام خانوادگی:</label>
      <input type="text" class="form-control input-shiny" wire:model.live="searchName"
       placeholder="نام و نام خانوادگی">
     </div>
     <div class="col-md-3">
      <label class="fw-bold mb-1">وضعیت:</label>
      <select class="form-control input-shiny" wire:model.live="status">
       <option value="0">همه</option>
       <option value="level_0">مرحله اول ثبت‌نام</option>
       <option value="level_special">مرحله انتخاب تخصص</option>
       <option value="level_nobatdehi">مرحله تنظیم برنامه نوبت‌دهی</option>
       <option value="level_moshavere">مرحله تنظیم برنامه مشاوره</option>
       <option value="level_finish">نهایی شده</option>
      </select>
     </div>
     <div class="col-md-3 d-flex align-items-end">
      <button wire:click="resetFilters" class="btn btn-gradient-primary w-100">از نو</button>
     </div>
    </div>
   </div>
  </div>

  <div class="card shadow-sm">
   <div class="card-body p-3">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered table-striped table-sm">
      <thead>
       <tr>
        <th>#</th>
        <th>آواتار</th>
        <th>نام و نام خانوادگی</th>
        <th>شماره تماس</th>
        <th>تاریخ ثبت‌نام</th>
        <th>تعرفه نوبت</th>
        <th>تعرفه ویزیت سایت</th>
        <th>شهر</th>
        <th>وضعیت</th>
        <th>Security</th>
        <th>ورود</th>
        <th>عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($doctors as $index => $doctor)
        <tr>
         <td>{{ $doctors->firstItem() + $index }}</td>
         <td>
          <a href="{{ asset('storage/'.$doctor->profile_photo_path) }}" target="_blank">
           <img src="{{ asset('storage/'.$doctor->profile_photo_path) }}" class="img-responsive img-circle"
            style="width:32px; height:32px;">
          </a>
         </td>
         <td>{{ $doctor->full_name ?? '-' }}</td>
         <td>{{ $doctor->mobile ?? '-' }}</td>
         <td>{{ \App\Helpers\JalaliHelper::toJalaliDateTime($doctor->created_at) }}</td>
         <td>
          {{ $doctor->tariff && $doctor->tariff->visit_fee > 0 ? number_format($doctor->tariff->visit_fee) . ' تومان' : 'رایگان' }}
         </td>
         <td>
          {{ $doctor->tariff && $doctor->tariff->site_fee > 0 ? number_format($doctor->tariff->site_fee) . ' تومان' : 'رایگان' }}
         </td>
         <td>{{ $doctor->province ? $doctor->province->name . ' / ' . ($doctor->city->name ?? '-') : '-' }}</td>
         <td>
          @switch($doctor->status)
           @case(0)
            مرحله اول ثبت‌نام
           @break

           @case(1)
            مرحله انتخاب تخصص
           @break

           @case(2)
            مرحله تنظیم برنامه نوبت‌دهی
           @break

           @case(3)
            مرحله تنظیم برنامه مشاوره
           @break

           @case(4)
            نهایی شده
           @break

           @default
            ناشناخته
           @break
          @endswitch
         </td>
         <td class="text-center">
          <i class="fa fa-minus-circle" style="color: red; font-size: 20px;"></i>
         </td>
         <td>
          <a href="{{ route('doctor.login', $doctor->id) }}" target="_blank" class="btn btn-sm btn-default">ورود</a>
         </td>
         <td class="text-center">
          <div class="dropdown">
           <button class="btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown">
            انجام عملیات <span class="caret"></span>
           </button>
           <ul class="dropdown-menu">
            <li><li><a href="{{ route('admin.doctors.doctors-management.edit', $doctor) }}" class="dropdown-item"><i class="fa fa-edit"></i> ویرایش</a></li></li>
            <li><a href="{{ route('admin.doctors.doctors-management.bime.index', $doctor->id) }}"
              class="dropdown-item"><i class="fa fa-edit"></i> بیمه‌ها</a></li>
            <li><a
              href="{{ route('dr-workhours', $doctor->id) }}"
              class="dropdown-item"><i class="fa fa-calendar-times-o"></i> برنامه نوبت‌دهی</a></li>
            <li><a
              href="{{ route('dr-moshavere_setting', $doctor->id) }}"
              class="dropdown-item"><i class="fa fa-calendar-times-o"></i> برنامه مشاوره</a></li>
            <li><a
              href="{{ route('admin.content.doctors.doctors-management.gallery.index', ['doctor_id' => $doctor->id]) }}"
              class="dropdown-item"><i class="fa fa-picture-o"></i> گالری عکس</a></li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('doctor.login', $doctor->id) }}" target="_blank" class="dropdown-item"><i
               class="fa fa-sign-in"></i> ورود</a></li>
           </ul>
          </div>
         </td>
        </tr>
        @empty
         <tr>
          <td colspan="12" class="text-center py-5">
           <i class="fas fa-user-md fs-1 text-muted mb-3"></i>
           <p class="text-muted fw-medium">پزشکی یافت نشد.</p>
          </td>
         </tr>
        @endforelse
       </tbody>
      </table>
     </div>
     <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted fs-6">نمایش {{ $doctors->firstItem() }} تا {{ $doctors->lastItem() }} از
       {{ $doctors->total() }} ردیف</div>
      {{ $doctors->links('vendor.pagination.bootstrap-4') }}
     </div>
    </div>
   </div>
  </div>

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

   .panel-default {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
   }

   .panel-heading {
    background: linear-gradient(135deg, #f9fafb, #e5e7eb);
    padding: 10px;
    font-weight: bold;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 8px 8px 0 0;
    color: #4b5563;
   }

   .input-shiny {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
   }

   .input-shiny:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
   }

   .btn-gradient-primary {
    background: linear-gradient(90deg, #4f46e5, #7c3aed);
    border: none;
    color: white;
   }

   .btn-gradient-primary:hover {
    background: linear-gradient(90deg, #4338ca, #6d28d9);
    transform: translateY(-1px);
   }

   .table-sm td,
   .table-sm th {
    padding: 0.5rem;
    font-size: 0.875rem;
   }
  </style>
 </div>
