<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <h5 class="mb-0 fw-bold">افزودن doctor جدید</h5>
            </div>
            <a href="{{ route('admin.panel.doctors.index') }}"
                class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 hover:shadow-md transition-all">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                بازگشت
            </a>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="name" class="form-label fw-bold text-dark mb-2">نام</label>
                        <input type="text" wire:model="name" class="form-control input-shiny" id="name" placeholder="نام doctor" required>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" wire:model="status">
                            <label class="form-check-label fw-medium" for="isActive">
                                وضعیت: <span class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="description" class="form-label fw-bold text-dark mb-2">توضیحات (اختیاری)</label>
                        <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3" placeholder="توضیحات doctor"></textarea>
                    </div>
                </div>
                <div class="col-12 text-end mt-3">
                    <button wire:click="store" class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        افزودن doctor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });
        });
    </script>
</div>