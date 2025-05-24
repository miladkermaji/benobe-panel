<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <h5 class="mb-0 fw-bold text-shadow">ویرایش مسدودیت کاربر</h5>
            </div>
            <a href="{{ route('admin.panel.user-blockings.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
                <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                بازگشت
            </a>
        </div>

        <div class="card-body p-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <form wire:submit="update">
                        <div class="row g-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">نوع کاربر</label>
                                <select wire:model="type" class="form-select" id="type">
                                    <option value="">انتخاب کنید</option>
                                    <option value="user">کاربر</option>
                                    <option value="doctor">پزشک</option>
                                </select>
                                @error('type')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">کاربر</label>
                                <select wire:model="user_id" class="form-select select2-user" id="user_id">
                                    <option value="">انتخاب کنید</option>
                                    @if ($type == 'user')
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name . ' (' . $user->mobile . ')' }}
                                            </option>
                                        @endforeach
                                    @elseif ($type == 'doctor')
                                        @foreach ($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">
                                                {{ $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->mobile . ')' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('user_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاریخ شروع</label>
                                <input type="text" wire:model="blocked_at" class="form-control jalali-datepicker text-end" id="blocked_at" data-jdp>
                                @error('blocked_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاریخ پایان</label>
                                <input type="text" wire:model="unblocked_at" class="form-control jalali-datepicker text-end" id="unblocked_at" data-jdp>
                                @error('unblocked_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">دلیل</label>
                                <textarea wire:model="reason" class="form-control" rows="3"></textarea>
                                @error('reason')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="status" class="form-check-input" id="status">
                                    <label class="form-check-label" for="status">فعال</label>
                                </div>
                                @error('status')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end mt-4 w-100 d-flex justify-content-end">
                            <button type="submit" class="btn btn-gradient-success px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <path d="M17 21v-8H7v8M7 3v5h8" />
                                </svg>
                                ذخیره تغییرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(90deg, #2e86c1, #3498db);
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2e86c1;
            box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
            background: #f8f9fa;
        }

        .form-label {
            position: absolute;
            top: -25px;
            right: 15px;
            color: #333;
            font-size: 12px;
            background: #fff;
            padding: 0 5px;
            pointer-events: none;
        }

        .btn-gradient-success {
            background: linear-gradient(90deg, #28a745, #34c759);
            border: none;
            color: #fff;
            font-weight: 600;
        }

        .btn-gradient-success:hover {
            background: linear-gradient(90deg, #34c759, #28a745);
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.8);
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .status-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .status-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-toggle .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .status-toggle .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .status-toggle input:checked+.slider {
            background-color: #28a745;
        }

        .status-toggle input:checked+.slider:before {
            transform: translateX(26px);
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .text-shadow {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-outline-light {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 575px) {
            .card-body {
                padding: 1.5rem;
            }

            .btn-gradient-success {
                width: 100%;
                justify-content: center;
            }

            .form-control, .form-select {
                font-size: 13px;
                padding: 10px 12px;
            }

            .form-label {
                font-size: 11px;
                top: -20px;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', function () {
            // مقداردهی اولیه Select2
            function initializeSelect2() {
                $('#user_id').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید',
                    allowClear: true,
                    width: '100%',
                }).on('change', function () {
                    @this.set('user_id', this.value);
                });
            }

            // مقداردهی اولیه Datepicker
            function initializeDatepicker() {
                jalaliDatepicker.startWatch({
                    minDate: "attr",
                    maxDate: "attr",
                    showTodayBtn: true,
                    showEmptyBtn: true,
                    time: false,
                    zIndex: 1050,
                    dateFormatter: function(unix) {
                        return new Date(unix).toLocaleDateString('fa-IR', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    }
                });

                document.getElementById('blocked_at').addEventListener('change', function() {
                    @this.set('blocked_at', this.value);
                });
                document.getElementById('unblocked_at').addEventListener('change', function() {
                    @this.set('unblocked_at', this.value);
                });
            }

            // اجرای اولیه
            initializeSelect2();
            initializeDatepicker();

            // به‌روزرسانی Select2 پس از تغییر نوع کاربر
            Livewire.on('select2:refresh', () => {
                $('#user_id').select2('destroy');
                initializeSelect2();
                // اطمینان از فعال شدن فیلد
                $('#user_id').prop('disabled', @json(!$type));
            });

            // نمایش اعلان‌ها
            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });
        });
    </script>
</div>