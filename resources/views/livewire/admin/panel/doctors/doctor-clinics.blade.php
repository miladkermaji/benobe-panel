<div>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">مدیریت کلینیک‌های {{ $doctor->full_name }}</h4>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="input-group">
            <input type="text" wire:model.live="search" class="form-control" placeholder="جستجو در کلینیک‌ها...">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">کلینیک‌های اختصاص داده شده</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>نام کلینیک</th>
                      <th>آدرس</th>
                      <th>عملیات</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($doctorClinics as $clinic)
                      <tr>
                        <td>{{ $clinic->name }}</td>
                        <td>{{ $clinic->address }}</td>
                        <td>
                          <button wire:click="detachClinic({{ $clinic->id }})" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> حذف
                          </button>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="3" class="text-center">هیچ کلینیکی به این پزشک اختصاص داده نشده است.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="mt-4">
                {{ $doctorClinics->links() }}
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">کلینیک‌های موجود</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>نام کلینیک</th>
                      <th>آدرس</th>
                      <th>عملیات</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($availableClinics as $clinic)
                      <tr>
                        <td>{{ $clinic->name }}</td>
                        <td>{{ $clinic->address }}</td>
                        <td>
                          <button wire:click="attachClinic({{ $clinic->id }})" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> اضافه کردن
                          </button>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="3" class="text-center">هیچ کلینیک دیگری موجود نیست.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
