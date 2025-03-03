<div>
  <div class="card-header d-flex border-top rounded-0 flex-wrap py-2 justify-content-between w-100 gap-20">
    <div class="d-flex align-items-center">
      <input type="search" class="form-control  w-100 me-2" placeholder="جستجو استان" wire:model="search"
        wire:keyup="searchUpdated">
    </div>
     <a href="{{ route('admin.Dashboard.cities.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i> افزودن  استان
        </a>
    <div>
<button class="btn btn-danger"
    wire:click="confirmDelete"
    wire:loading.attr="disabled"
    wire:target="deleteSelected"
    id="deleteButton"
    :disabled="{{ count($selectedRows) == 0 ? 'true' : 'false' }}">
    <i class="ti ti-trash"></i> حذف انتخاب‌شده‌ها
</button>








    </div>
  </div>

  <div class="table-responsive text-nowrap">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>
            <input type="checkbox" class="form-check-input" wire:model="selectAll"
              x-on:change="$wire.dispatch('updateDeleteButton')">

          </th>
          <th>کد استان</th>
          <th>نام استان</th>
          <th>وضعیت</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($cities as $city)
        <tr>
        <td>
        <input type="checkbox" class="form-check-input" wire:model="selectedRows" value="{{ $city->id }}"
        x-on:change="$wire.dispatch('updateDeleteButton')">


        </td>
        <td>{{ $city->id }}</td>
        <td>{{ $city->name }}</td>
        <td>
        <span wire:click="toggleStatus({{ $city->id }})"
          class="badge bg-label-{{ $city->status == 1 ? 'success' : 'danger' }} cursor-pointer">
          {{ $city->status == 1 ? 'فعال' : 'غیر فعال' }}
        </span>
        </td>
        <td>
        <div class="dropdown">
          <button class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" type="button">
          <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu">
          <a class="dropdown-item" href="{{ url('admin/dashboard/cities/show/' . $city->id) }}">
          <i class="ti ti-eye me-1"></i> شهرستان
          </a>
          <a class="dropdown-item" href="{{ url('admin/dashboard/cities/edit/' . $city->id) }}">
          <i class="ti ti-pencil me-1"></i> ویرایش
          </a>
          </div>
        </div>
        </td>
        </tr>
    @endforeach
      </tbody>
    </table>
  </div>

  <div class="row mx-2 mt-4">
    <div class="col-sm-12 col-md-6">
      {{ $cities->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
