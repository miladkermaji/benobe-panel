<div class="card">

 <div class="card-body">
  <div class="d-flex justify-content-between mb-3 flex-wrap gap-20">
   <div>
    <input type="search" class="form-control  w-100 me-2" placeholder="جستجو شهر" wire:model="search"
     wire:keyup="searchUpdated">
   </div>
    <a href="{{ route('admin.Dashboard.menu.create') }}" class="btn btn-primary">
      <i class="ti ti-plus"></i> افزودن منو
    </a>
   <div>
    <button class="btn btn-danger" id="deleteButton" @disabled(empty($selectedRows)) onclick="confirmDelete()">
     <i class="ti ti-trash"></i> حذف انتخاب‌شده‌ها
    </button>
   </div>
  </div>
  @if (session()->has('success'))
   <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  <div class="table-responsive">
   <table class="table table-striped">
    <thead>
     <tr>
      <th>
       <input type="checkbox" class="form-check-input" wire:model="selectAll"
        x-on:change="$wire.dispatch('updateDeleteButton')">
      </th>
      <th>ردیف</th>
      <th>نام</th>
      <th>لینک</th>
      <th>آیکون</th>
      <th>جایگاه</th>
      <th>زیرمجموعه</th>
      <th>ترتیب</th>
      <th>وضعیت</th>
      <th>عملیات</th>
     </tr>
    </thead>
    <tbody>
     @foreach ($menus as $index => $menu)
      <tr>
       <td><input type="checkbox" class="form-check-input" wire:model="selectedRows" value="{{ $menu->id }}"
       x-on:change="$wire.dispatch('updateDeleteButton')"></td>
       <td>{{ $index + 1 }}</td>
       <td>{{ $menu->name }}</td>
       <td>{{ $menu->url }}</td>
      <td>
      <img src="{{ $menu->icon ? asset('storage/' . $menu->icon) : '' }}" alt="آیکون منو"
        class="img-thumbnail" style="width: 40px; height: 40px; border-radius: 8px;">
      </td>

       <td>{{ $menu->position }}</td>
       <td>{{ $menu->parent ? $menu->parent->name : 'دسته اصلی' }}</td>
       <td>{{ $menu->order }}</td>
       <td>
      <span wire:click="toggleStatus({{ $menu->id }})"
       class="badge bg-label-{{ $menu->status == 1 ? 'success' : 'danger' }} cursor-pointer">
       {{ $menu->status == 1 ? 'فعال' : 'غیر فعال' }}
      </span>
       </td>
       <td>
      <div class="dropdown">
       <button class="btn p-0" data-bs-toggle="dropdown">
        <i class="ti ti-dots-vertical"></i>
       </button>
       <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.Dashboard.menu.edit', ['id' => $menu->id]) }}">
         <i class="ti ti-pencil"></i> ویرایش
        </a>
       </div>
      </div>
       </td>
      </tr>
   @endforeach
    </tbody>
  </table>
  </div>
  <div class="d-flex justify-content-between mt-3">
   <span>نمایش {{ $menus->count() }} از {{ $menus->total() }} منو</span>
   {{ $menus->links() }}
  </div>
 </div>
</div>
<script>
  function confirmDelete() {
    Swal.fire({
      title: "آیا مطمئن هستید؟",
      text: "این عملیات غیرقابل بازگشت است!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "بله، حذف شود!",
      cancelButtonText: "لغو",
    }).then((result) => {
      if (result.isConfirmed) {
        window.dispatchEvent(new CustomEvent('deleteSelectedMenus'));
      }
    });
  }
</script>