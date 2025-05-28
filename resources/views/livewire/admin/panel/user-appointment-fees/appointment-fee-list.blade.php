<div>
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <input wire:model.live="search" type="text" class="form-control" placeholder="جستجو...">
        </div>
        <div>
          <a href="{{ route('admin.panel.user-appointment-fees.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i>
            افزودن حق نوبت جدید
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th wire:click="sortBy('name')" style="cursor: pointer">
                نام
                @if ($sortField === 'name')
                  @if ($sortDirection === 'asc')
                    <i class="fa fa-sort-up"></i>
                  @else
                    <i class="fa fa-sort-down"></i>
                  @endif
                @endif
              </th>
              <th wire:click="sortBy('price')" style="cursor: pointer">
                قیمت
                @if ($sortField === 'price')
                  @if ($sortDirection === 'asc')
                    <i class="fa fa-sort-up"></i>
                  @else
                    <i class="fa fa-sort-down"></i>
                  @endif
                @endif
              </th>
              <th wire:click="sortBy('discount')" style="cursor: pointer">
                تخفیف
                @if ($sortField === 'discount')
                  @if ($sortDirection === 'asc')
                    <i class="fa fa-sort-up"></i>
                  @else
                    <i class="fa fa-sort-down"></i>
                  @endif
                @endif
              </th>
              <th wire:click="sortBy('status')" style="cursor: pointer">
                وضعیت
                @if ($sortField === 'status')
                  @if ($sortDirection === 'asc')
                    <i class="fa fa-sort-up"></i>
                  @else
                    <i class="fa fa-sort-down"></i>
                  @endif
                @endif
              </th>
              <th>عملیات</th>
            </tr>
          </thead>
          <tbody>
            @forelse($fees as $fee)
              <tr>
                <td>{{ $fee->name }}</td>
                <td>{{ number_format($fee->price) }} تومان</td>
                <td>{{ $fee->discount }}%</td>
                <td>
                  @if ($fee->status)
                    <span class="badge badge-success">فعال</span>
                  @else
                    <span class="badge badge-danger">غیرفعال</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group">
                    <a href="{{ route('admin.user-appointment-fees.edit', $fee) }}" class="btn btn-sm btn-info">
                      <i class="fa fa-edit"></i>
                    </a>
                    <button wire:click="delete({{ $fee->id }})" class="btn btn-sm btn-danger"
                      onclick="confirm('آیا از حذف این حق نوبت اطمینان دارید؟') || event.stopImmediatePropagation()">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center">هیچ حق نوبتی یافت نشد.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $fees->links() }}
      </div>
    </div>
  </div>
</div>
