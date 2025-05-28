<div>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">افزودن حق نوبت جدید</h3>
    </div>
    <div class="card-body">
      <form wire:submit="save">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="name">نام</label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                wire:model="name">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="price">قیمت (تومان)</label>
              <input type="number" class="form-control @error('price') is-invalid @enderror" id="price"
                wire:model="price">
              @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <div class="form-group">
              <label for="discount">تخفیف (درصد)</label>
              <input type="number" class="form-control @error('discount') is-invalid @enderror" id="discount"
                wire:model="discount">
              @error('discount')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="status">وضعیت</label>
              <select class="form-control @error('status') is-invalid @enderror" id="status" wire:model="status">
                <option value="1">فعال</option>
                <option value="0">غیرفعال</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12">
            <div class="form-group">
              <label for="description">توضیحات</label>
              <textarea class="form-control @error('description') is-invalid @enderror" id="description" wire:model="description"
                rows="3"></textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-12">
            <button type="submit" class="btn btn-primary">ذخیره</button>
            <a href="{{ route('admin.user-appointment-fees.index') }}" class="btn btn-secondary">بازگشت</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
