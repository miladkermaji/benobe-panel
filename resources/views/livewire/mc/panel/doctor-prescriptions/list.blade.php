<div>
  <div class="row mb-3">
    <div class="col-md-4">
      <input type="text" class="form-control" placeholder="جستجو (نام بیمار یا کد رهگیری)"
        wire:model.debounce.500ms="search">
    </div>
    <div class="col-md-3">
      <select class="form-control" wire:model="status">
        <option value="">همه وضعیت‌ها</option>
        <option value="pending">در انتظار پاسخ</option>
        <option value="completed">پایان یافته</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-light">
        <tr>
          <th>#</th>
          <th>نام بیمار</th>
          <th>بیمه</th>
          <th>نوع نسخه</th>
          <th>وضعیت</th>
          <th>توضیحات</th>
          <th>انسولین‌ها</th>
          <th>کد رهگیری</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($prescriptions as $prescription)
          <tr>
            <td>{{ $prescription->id }}</td>
            <td>{{ $prescription->patient->name ?? '-' }}</td>
            <td>{{ $prescription->insurance->name ?? '-' }}</td>
            <td>{{ $prescription->type_label }}</td>
            <td>
              @if ($prescription->status == 'pending')
                <span class="badge badge-warning">در انتظار پاسخ</span>
              @elseif($prescription->status == 'completed')
                <span class="badge badge-success">پایان یافته</span>
              @else
                <span class="badge badge-secondary">{{ $prescription->status }}</span>
              @endif
            </td>
            <td>{{ $prescription->description }}</td>
            <td>
              @if ($prescription->insulins && $prescription->insulins->count())
                @foreach ($prescription->insulins as $insulin)
                  <span class="badge badge-info">{{ $insulin->name }} ({{ $insulin->pivot->count }})</span>
                @endforeach
              @else
                -
              @endif
            </td>
            <td>{{ $prescription->tracking_code ?? '-' }}</td>
            <td>
              @if ($prescription->status == 'pending')
                <button class="btn btn-sm btn-primary"
                  onclick="openXModal('prescriptionModal'); $wire.openModal({{ $prescription->id }})">پاسخ
                  نسخه</button>
              @else
                <span class="text-success">پاسخ داده شد</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center">موردی یافت نشد.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>
    {{ $prescriptions->links() }}
  </div>

  <!-- Modal -->
  <x-custom-modal id="prescriptionModal" title="ثبت کد رهگیری نسخه" size="md">
    <form wire:submit.prevent="submitTrackingCode">
      <div class="form-group">
        <label>کد رهگیری</label>
        <input type="text" class="form-control" wire:model.defer="tracking_code">
        @error('tracking_code')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="button" class="btn btn-secondary flex-grow-1" wire:click="closeModal">انصراف</button>
        <button type="submit" class="btn btn-primary flex-grow-1">ثبت</button>
      </div>
    </form>
  </x-custom-modal>
</div>
