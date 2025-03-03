<div>
    <div class="row no-gutters font-size-13 margin-bottom-10">
        <div class="user-panel-content w-100">
            <div class="mt-3">
                <div class="card-header font-weight-bold">اضافه کردن تعرفه بر اساس شرکت بیمه</div>
                <div class="col-12 mt-2 p-4">
                    <h5>روش محاسبه :</h5>
                    <div class="ant-radio-group ant-radio-group-outline mt-3">
                        <label class="mb-2 ant-radio-wrapper">
                            <span class="ant-radio">
                                <input type="radio" wire:model.live="calculation_method" value="0">
                                <span class="ant-radio-inner"></span>
                            </span>
                            <span class="px-1 font-weight-bold">
                                در صورتی که مبلغ نهایی را وارد نمایید و درصد را 0 قرار دهید، دقیقاً آن مبلغ برای بیمار
                                محاسبه می‌شود
                            </span>
                        </label>
                        <label class="d-block mb-2 ant-radio-wrapper">
                            <span class="ant-radio">
                                <input type="radio" wire:model.live="calculation_method" value="1">
                                <span class="ant-radio-inner"></span>
                            </span>
                            <span class="px-1 font-weight-bold">
                                درصدی را از مبلغ باقی‌مانده از سهم بیمه اصلی، کسر می‌کند
                            </span>
                        </label>
                        <label class="mb-2 ant-radio-wrapper">
                            <span class="ant-radio">
                                <input type="radio" wire:model.live="calculation_method" value="2">
                                <span class="ant-radio-inner"></span>
                            </span>
                            <span class="px-1 font-weight-bold">
                                هیچ اثری بر مبلغ قابل پرداخت ندارد و فقط برای گرفتن آمار و ارائه به شرکت بیمه نیاز است
                            </span>
                        </label>
                    </div>
                </div>

                <form wire:submit.prevent="store" class="mt-5">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <label>نام بیمه:</label>
                                <input wire:model.defer="name" type="text" class="form-control h-50"
                                    placeholder="نام شرکت بیمه">
                                @error('name')
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @enderror
                            </div>
                            @if ($calculation_method === '0')
                                <div class="col-lg-6">
                                    <label>مبلغ نهایی (تومان):</label>
                                    <input wire:model.defer="final_price" type="number" class="form-control h-50"
                                        placeholder="فقط عدد">
                                    @error('final_price')
                                        <span class="text-danger">{{ $errors->first('final_price') }}</span>
                                    @enderror
                                </div>
                            @elseif ($calculation_method === '1')
                                <div class="col-lg-6">
                                    <label>درصد سهم بیمه:</label>
                                    <input wire:model.defer="insurance_percent" type="number" class="form-control h-50"
                                        placeholder="فقط عدد">
                                    @error('insurance_percent')
                                        <span class="text-danger">{{ $errors->first('insurance_percent') }}</span>
                                    @enderror
                                </div>
                            @elseif ($calculation_method === '2')
                                <div class="col-lg-3">
                                    <label>مبلغ نوبت (تومان):</label>
                                    <input wire:model.defer="appointment_price" type="number" class="form-control h-50"
                                        placeholder="فقط عدد">
                                    @error('appointment_price')
                                        <span class="text-danger">{{ $errors->first('appointment_price') }}</span>
                                    @enderror
                                </div>
                                <div class="col-lg-3">
                                    <label>درصد سهم بیمه:</label>
                                    <input wire:model.defer="insurance_percent" type="number" class="form-control h-50"
                                        placeholder="فقط عدد">
                                    @error('insurance_percent')
                                        <span class="text-danger">{{ $errors->first('insurance_percent') }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="w-100 d-flex justify-content-end mb-3 p-3">
                        <button type="submit" class="btn btn-sm btn-primary h-50">
                            <i class="mdi mdi-check"></i> ثبت و ذخیره
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        @if ($calculation_method === '0')
                            <tr>
                                <th>شرکت بیمه</th>
                                <th>مبلغ نهایی (تومان)</th>
                                <th>عملیات</th>
                            </tr>
                        @elseif ($calculation_method === '1')
                            <tr>
                                <th>شرکت بیمه</th>
                                <th>درصد سهم بیمه</th>
                                <th>عملیات</th>
                            </tr>
                        @elseif ($calculation_method === '2')
                            <tr>
                                <th>شرکت بیمه</th>
                                <th>مبلغ نوبت (تومان)</th>
                                <th>درصد سهم بیمه</th>
                                <th>عملیات</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse ($insurances as $insurance)
                            <tr>
                                <td>{{ $insurance->name }}</td>
                                @if ($calculation_method === '0')
                                    <td>{{ number_format($insurance->final_price) }} تومان</td>
                                @elseif ($calculation_method === '1')
                                    <td>{{ $insurance->insurance_percent }}%</td>
                                @elseif ($calculation_method === '2')
                                    <td>{{ number_format($insurance->appointment_price) }} تومان</td>
                                    <td>{{ $insurance->insurance_percent }}%</td>
                                @endif
                                <td>
                                    <button wire:click="confirmDelete({{ $insurance->id }})"
                                        class="btn btn-sm btn-light rounded-circle">
                                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="" srcset="">
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @if ($calculation_method === '2')
                                    <td colspan="4">موردی ثبت نشده است</td>
                                @else
                                    <td colspan="3">موردی ثبت نشده است</td>
                                @endif
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const insuranceId = typeof id === 'object' ? id.id : id;
                        Livewire.dispatch('delete', { id: insuranceId });
                    }
                });
            });

            Livewire.on('toast', (event) => {
                toastr.success(event.message);
            });
        });
    </script>
</div>