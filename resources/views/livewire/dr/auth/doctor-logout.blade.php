<div class="modal-overlay d-flex justify-content-center align-items-center min-vh-100">
    <div class="modal-container animate__animated animate__zoomIn">
        <div class="modal-content bg-white rounded-3 shadow-lg p-4 p-md-5 text-center">
            <!-- لوگو -->
            <div class="mb-4 d-flex justify-content-center">
                <a href="/">
                    <img src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه" width="70" class="logo-hover">
                </a>
            </div>

            <!-- متن -->
            <div class="mb-5">
                <h5 class="fw-bold text-dark">مطمئنید که می‌خواهید از حساب خود خارج شوید؟</h5>
            </div>

            <!-- دکمه‌ها -->
            <div class="d-flex justify-content-center gap-3">
                <button wire:click="logout" wire:loading.attr="disabled" class="btn btn-danger rounded-pill py-2 px-5 d-flex align-items-center">
                    <span wire:loading.remove wire:target="logout">خروج</span>
                    <div wire:loading wire:target="logout" class="spinner-border spinner-border-sm ms-2" role="status"></div>
                </button>
                <a href="{{ route('dr-panel') }}" class="btn btn-outline-primary rounded-pill py-2 px-5 btn-hover">بازگشت</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* پس‌زمینه مودال */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1050;
        }

        /* کانتینر مودال */
        .modal-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        /* محتوای مودال */
        .modal-content {
            border: none;
            position: relative;
            overflow: hidden;
        }

        /* افکت هاور لوگو */
        .logo-hover {
            transition: transform 0.3s ease;
        }

        .logo-hover:hover {
            transform: scale(1.1);
        }

        /* دکمه خروج */
        .btn-danger {
            background-color: #ff0000; /* قرمز خالص */
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #e60000; /* قرمز تیره‌تر برای هاور */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }

        /* دکمه بازگشت */
        .btn-outline-primary {
            border-color: #003087; /* آبی تیره */
            color: #003087;
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            background-color: #003087; /* آبی تیره برای پس‌زمینه */
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 48, 135, 0.3);
        }

        /* ریسپانسیو */
        @media (max-width: 576px) {
            .modal-container {
                max-width: 90%;
            }

            .modal-content {
                padding: 1.5rem;
            }

            .btn-danger, .btn-outline-primary {
                padding: 0.5rem 2rem;
                font-size: 0.9rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @if (session('swal-success'))
                Swal.fire({
                    icon: 'success',
                    title: 'خروج موفق',
                    text: '{{ session('swal-success') }}',
                    timer: 2000,
                    showConfirmButton: false,
                });
            @endif
        });
    </script>
@endpush