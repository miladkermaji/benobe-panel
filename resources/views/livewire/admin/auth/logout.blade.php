<div class="justify-content-center align-items-center">
    <div class="col-md-6 login-container position-relative">
        <div class="login-card custom-rounded custom-shadow p-7">
            <div class="logo-wrapper w-100 d-flex justify-content-center mb-4">
                <img class="position-absolute mt-3 cursor-pointer" onclick="location.href='/'" width="85px"
                    src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
                    <span class="text-custom-gray px-1 fw-bold">خروج از حساب کاربری</span>
                </div>
            </div>
            <div class="text-center mb-4">
                <p class="text-custom-gray fw-bold">آیا مطمئن هستید که می‌خواهید از حساب خود خارج شوید؟</p>
            </div>
            <div class="d-flex justify-content-center gap-3">
                <button wire:click="logout" wire:loading.attr="disabled"
                    class="btn btn-danger custom-rounded py-2 px-4 d-flex justify-content-center align-items-center">
                    <span wire:loading.remove wire:target="logout">خروج</span>
                    <div wire:loading wire:target="logout" class="loader spinner-border spinner-border-sm ms-2"
                        role="status"></div>
                </button>
                <a href="{{ route('admin-panel') }}" class="btn btn-outline-primary custom-rounded py-2 px-4">
                    بازگشت
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // نمایش پیام موفقیت بعد از لاگ‌اوت
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