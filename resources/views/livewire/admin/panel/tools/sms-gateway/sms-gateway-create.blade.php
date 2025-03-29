<div>
    <div class="container-fluid py-4">
        <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    <h5 class="mb-0 fw-bold">افزودن پنل پرداخت جدید</h5>
                </div>
                <a href="{{ route('admin.panel.tools.sms-gateways.index') }}" 
                    class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    بازگشت
                </a>
            </div>

            <div class="card-body p-4">
                <form wire:submit.prevent="save">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label fw-bold">نام  پنل <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" id="name" class="form-control rounded-2" 
                                    placeholder="مثال: zarinpal, saman, ..." required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title" class="form-label fw-bold">عنوان پنل <span class="text-danger">*</span></label>
                                <input type="text" wire:model="title" id="title" class="form-control rounded-2" 
                                    placeholder="مثال: پنل زرین‌پال" required>
                                @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
                                <label class="form-check-label fw-bold" for="isActive">
                                    وضعیت: <span class="text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                                </label>
                                @error('is_active') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="settings" class="form-label fw-bold">تنظیمات پنل (JSON) <span class="text-danger">*</span></label>
                                <textarea dir="ltr" wire:model="settings" id="settings" class="form-control rounded-2" 
                                    rows="8" placeholder='{"merchant_id": "xxxx", "sandbox": true}' required
                                    style="resize: vertical; font-family: 'Courier New', monospace; font-size: 0.9rem;"></textarea>
                                @error('settings') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted mt-1 d-block">تنظیمات پنل را به صورت JSON معتبر وارد کنید</small>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <path d="M17 21v-8H7v8M7 3v5h8" />
                                </svg>
                                ایجاد پنل
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', function() {
                Livewire.on('show-alert', (event) => {
                    toastr[event.type](event.message, {
                        positionClass: 'toast-bottom-left',
                        rtl: true
                    });
                });
            });
        </script>
    @endpush
</div>