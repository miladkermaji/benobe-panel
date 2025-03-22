<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <h5 class="mb-0 fw-bold">افزودن یادداشت جدید</h5>
            </div>
            <a href="{{ route('dr.panel.doctornotes.index') }}" class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 hover:shadow-md transition-all">
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
                        <label for="appointment_type" class="form-label fw-bold text-dark mb-2">نوع نوبت</label>
                        <select wire:model="appointment_type" class="form-control input-shiny" id="appointment_type" required>
                            <option value="in_person">حضوری</option>
                            <option value="online_phone">تلفنی</option>
                            <option value="online_text">متنی</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="clinic_id" class="form-label fw-bold text-dark mb-2">کلینیک (اختیاری)</label>
                        <select wire:model="clinic_id" class="form-control input-shiny" id="clinic_id">
                            <option value="">بدون کلینیک</option>
                            @foreach ($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 shadow-sm hover:shadow-md transition-all">
                        <label for="notes" class="form-label fw-bold text-dark mb-2">یادداشت (اختیاری)</label>
                        <textarea wire:model="notes" class="form-control input-shiny" id="notes" rows="3" placeholder="یادداشت پزشک"></textarea>
                    </div>
                </div>
                <div class="col-12 text-end mt-3">
                    <button wire:click="store" class="btn btn-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        افزودن یادداشت
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function() {
            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });
        });
    </script>
</div>