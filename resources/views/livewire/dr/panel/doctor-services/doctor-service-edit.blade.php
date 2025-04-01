<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <h5 class="mb-0 fw-bold">ویرایش خدمت: {{ $name }}</h5>
            </div>
            <a href="{{ route('dr.panel.doctor-services.index') }}" class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 hover:shadow-md transition-all">
                <svg width="16" height="16" style="transform: rotate(180deg)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                بازگشت
            </a>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="name" class="form-label fw-bold text-dark mb-2">نام خدمت</label>
                        <input type="text" wire:model="name" class="form-control input-shiny" id="name" placeholder="نام خدمت" required>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="duration" class="form-label fw-bold text-dark mb-2">مدت زمان (دقیقه)</label>
                        <input type="number" wire:model="duration" class="form-control input-shiny" id="duration" placeholder="مدت زمان" required>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="price" class="form-label fw-bold text-dark mb-2">قیمت (تومان)</label>
                        <input type="number" wire:model="price" class="form-control input-shiny" id="price" placeholder="قیمت" required>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
                        <label for="discount" class="form-label fw-bold text-dark mb-2">تخفیف (درصد)</label>
                        <input type="number" wire:model="discount" wire:click="openDiscountModal" class="form-control input-shiny cursor-pointer" id="discount" placeholder="تخفیف (اختیاری)" readonly>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="parent_id" class="form-label fw-bold text-dark mb-2">خدمت مادر (اختیاری)</label>
                        <select wire:model="parent_id" class="form-control input-shiny" id="parent_id">
                            <option value="">بدون خدمت مادر</option>
                            @foreach($parentServices as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all d-flex align-items-center">
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                            <label class="form-check-label fw-medium mx-4" for="status">
                                وضعیت: <span class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="description" class="form-label fw-bold text-dark mb-2">توضیحات (اختیاری)</label>
                        <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3" placeholder="توضیحات خدمت"></textarea>
                    </div>
                </div>
                <div class="col-12 text-end mt-3">
                    <button wire:click="update" class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                            <path d="M17 21v-8H7v8M7 3v5h8" />
                        </svg>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تخفیف -->
<!-- مودال تخفیف -->
@if($showDiscountModal)
    <div class="modal fade show d-block" id="discountModal" tabindex="-1" role="dialog" aria-labelledby="discountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="discountModalLabel">محاسبه تخفیف</h5>
                    <button type="button" class="btn-close" wire:click="closeDiscountModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="discountPercent" class="form-label">درصد تخفیف</label>
                        <input type="number" wire:model.live="discountPercent" class="form-control" id="discountPercent" placeholder="درصد را وارد کنید">
                    </div>
                    <div class="mb-4">
                        <label for="discountAmount" class="form-label">مبلغ تخفیف (تومان)</label>
                        <input type="number" wire:model.live="discountAmount" class="form-control" id="discountAmount" placeholder="مبلغ را وارد کنید">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" wire:click="closeDiscountModal">لغو</button>
                    <button type="button" class="btn btn-primary" style="background: linear-gradient(to right, #4B5EAA, #8B5CF6); border: none;" wire:click="applyDiscount">تأیید</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });
        });
    </script>
</div>