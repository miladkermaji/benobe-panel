<div>
  <div class="shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header glass-header fw-bold p-3">اضافه کردن تعرفه بر اساس شرکت بیمه</div>
    <div class="card-body p-4">
      <h5>روش محاسبه:</h5>
      <div class="ant-radio-group ant-radio-group-outline mt-3">
        <label class="mb-2 ant-radio-wrapper d-block">
          <span class="ant-radio">
            <input type="radio" wire:model.live="calculation_method" value="0">
            <span class="ant-radio-inner"></span>
          </span>
          <span class="px-1 fw-bold">
            مبلغ ثابت: مبلغ نهایی مشخص برای بیمار محاسبه می‌شود.
          </span>
        </label>
        <label class="mb-2 ant-radio-wrapper d-block">
          <span class="ant-radio">
            <input type="radio" wire:model.live="calculation_method" value="1">
            <span class="ant-radio-inner"></span>
          </span>
          <span class="px-1 fw-bold">
            درصد از مبلغ نوبت: درصدی از مبلغ نوبت کسر شده و مبلغ نهایی محاسبه می‌شود.
          </span>
        </label>
        <label class="mb-2 ant-radio-wrapper d-block">
          <span class="ant-radio">
            <input type="radio" wire:model.live="calculation_method" value="2">
            <span class="ant-radio-inner"></span>
          </span>
          <span class="px-1 fw-bold">
            مبلغ ثابت + درصد: مبلغ پایه ثابت به علاوه درصد تخفیف اعمال می‌شود.
          </span>
        </label>
        <label class="mb-2 ant-radio-wrapper d-block">
          <span class="ant-radio">
            <input type="radio" wire:model.live="calculation_method" value="3">
            <span class="ant-radio-inner"></span>
          </span>
          <span class="px-1 fw-bold">
            فقط برای آمار: هیچ تغییری در مبلغ قابل پرداخت ایجاد نمی‌کند.
          </span>
        </label>
        <label class="mb-2 ant-radio-wrapper d-block">
          <span class="ant-radio">
            <input type="radio" wire:model.live="calculation_method" value="4">
            <span class="ant-radio-inner"></span>
          </span>
          <span class="px-1 fw-bold">
            پویا: ترکیبی از مبلغ نوبت و درصد، با حداقل مبلغ نهایی (اختیاری).
          </span>
        </label>
      </div>

      <form wire:submit.prevent="store" class="mt-5">
        <div class="row">
          <div class="col-lg-6">
            <label>نام بیمه:</label>
            <input wire:model.defer="name" type="text" class="form-control h-50" placeholder="نام شرکت بیمه">
            @error('name')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <!-- روش 0: مبلغ ثابت -->
          @if ($calculation_method === '0')
            <div class="col-lg-6">
              <label>مبلغ نهایی (تومان):</label>
              <input wire:model.defer="final_price" type="number" class="form-control h-50" placeholder="فقط عدد">
              @error('final_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <!-- روش 1: درصد از مبلغ نوبت -->
          @elseif ($calculation_method === '1')
            <div class="col-lg-3">
              <label>مبلغ نوبت (تومان):</label>
              <input wire:model.defer="appointment_price" type="number" class="form-control h-50"
                placeholder="فقط عدد">
              @error('appointment_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-3">
              <label>درصد سهم بیمه:</label>
              <input wire:model.defer="insurance_percent" type="number" class="form-control h-50" placeholder="0-100">
              @error('insurance_percent')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <!-- روش 2: مبلغ ثابت + درصد -->
          @elseif ($calculation_method === '2')
            <div class="col-lg-3">
              <label>مبلغ نهایی (تومان):</label>
              <input wire:model.defer="final_price" type="number" class="form-control h-50" placeholder="فقط عدد">
              @error('final_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-3">
              <label>درصد سهم بیمه:</label>
              <input wire:model.defer="insurance_percent" type="number" class="form-control h-50" placeholder="0-100">
              @error('insurance_percent')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <!-- روش 3: فقط آمار -->
          @elseif ($calculation_method === '3')
            <div class="col-lg-3">
              <label>مبلغ نوبت (تومان، اختیاری):</label>
              <input wire:model.defer="appointment_price" type="number" class="form-control h-50"
                placeholder="فقط عدد">
              @error('appointment_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-3">
              <label>درصد سهم بیمه (اختیاری):</label>
              <input wire:model.defer="insurance_percent" type="number" class="form-control h-50" placeholder="0-100">
              @error('insurance_percent')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <!-- روش 4: پویا -->
          @elseif ($calculation_method === '4')
            <div class="col-lg-2">
              <label>مبلغ نوبت (تومان، اختیاری):</label>
              <input wire:model.defer="appointment_price" type="number" class="form-control h-50"
                placeholder="فقط عدد">
              @error('appointment_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-2">
              <label>درصد سهم بیمه (اختیاری):</label>
              <input wire:model.defer="insurance_percent" type="number" class="form-control h-50" placeholder="0-100">
              @error('insurance_percent')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-2">
              <label>حداقل مبلغ نهایی (تومان، اختیاری):</label>
              <input wire:model.defer="final_price" type="number" class="form-control h-50" placeholder="فقط عدد">
              @error('final_price')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          @endif
        </div>
        <div class="w-100 d-flex justify-content-end mt-3">
          <button type="submit" class="btn btn-sm btn-primary h-50">
            <i class="mdi mdi-check"></i> ثبت و ذخیره
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="mt-4">
    <table class="table table-modern table-striped table-bordered table-hover">
      <thead>
        @if ($calculation_method === '0')
          <tr>
            <th>ردیف</th>
            <th>شرکت بیمه</th>
            <th>مبلغ نهایی (تومان)</th>
            <th>عملیات</th>
          </tr>
        @elseif ($calculation_method === '1')
          <tr>
            <th>ردیف</th>
            <th>شرکت بیمه</th>
            <th>مبلغ نوبت (تومان)</th>
            <th>درصد سهم بیمه</th>
            <th>مبلغ نهایی (تومان)</th>
            <th>عملیات</th>
          </tr>
        @elseif ($calculation_method === '2')
          <tr>
            <th>ردیف</th>
            <th>شرکت بیمه</th>
            <th>مبلغ نهایی (تومان)</th>
            <th>درصد سهم بیمه</th>
            <th>عملیات</th>
          </tr>
        @elseif ($calculation_method === '3')
          <tr>
            <th>ردیف</th>
            <th>شرکت بیمه</th>
            <th>مبلغ نوبت (تومان)</th>
            <th>درصد سهم بیمه</th>
            <th>مبلغ نهایی (تومان)</th>
            <th>عملیات</th>
          </tr>
        @elseif ($calculation_method === '4')
          <tr>
            <th>ردیف</th>
            <th>شرکت بیمه</th>
            <th>مبلغ نوبت (تومان)</th>
            <th>درصد سهم بیمه</th>
            <th>مبلغ نهایی (تومان)</th>
            <th>عملیات</th>
          </tr>
        @endif
      </thead>
      <tbody>
        @forelse ($insurances as $index => $insurance)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $insurance->name }}</td>
            @if ($calculation_method === '0')
              <td>{{ number_format($insurance->final_price) }} تومان</td>
            @elseif ($calculation_method === '1')
              <td>{{ $insurance->appointment_price ? number_format($insurance->appointment_price) : '-' }} تومان</td>
              <td>{{ $insurance->insurance_percent ? $insurance->insurance_percent . '%' : '-' }}</td>
              <td>{{ $insurance->final_price ? number_format($insurance->final_price) : '-' }} تومان</td>
            @elseif ($calculation_method === '2')
              <td>{{ number_format($insurance->final_price) }} تومان</td>
              <td>{{ $insurance->insurance_percent }}%</td>
            @elseif ($calculation_method === '3')
              <td>{{ $insurance->appointment_price ? number_format($insurance->appointment_price) : '-' }} تومان</td>
              <td>{{ $insurance->insurance_percent ? $insurance->insurance_percent . '%' : '-' }}</td>
              <td>{{ $insurance->final_price ? number_format($insurance->final_price) : '-' }} تومان</td>
            @elseif ($calculation_method === '4')
              <td>{{ $insurance->appointment_price ? number_format($insurance->appointment_price) : '-' }} تومان</td>
              <td>{{ $insurance->insurance_percent ? $insurance->insurance_percent . '%' : '-' }}</td>
              <td>{{ $insurance->final_price ? number_format($insurance->final_price) : '-' }} تومان</td>
            @endif
            <td>
              <button wire:click="confirmDelete({{ $insurance->id }})" class="btn btn-sm btn-light rounded-circle"
                title="حذف">
                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $calculation_method === '0' || $calculation_method === '2' ? 4 : 6 }}"
              class="text-center">موردی ثبت نشده است</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <script>
    document.addEventListener('livewire:init', () => {
      toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 3000,
      };

      Livewire.on('confirmDelete', (id) => {
        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'این بیمه حذف خواهد شد و قابل بازگشت نیست!',

          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'بله، حذف کن!',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            const insuranceId = typeof id === 'object' ? id.id : id;
            Livewire.dispatch('delete', {
              id: insuranceId
            });
          }
        });
      });

      Livewire.on('toast', (event) => {
        toastr[event.type || 'success'](event.message);
      });
    });
  </script>
</div>
