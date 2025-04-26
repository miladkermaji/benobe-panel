<div>
    <div class="modal-header">
        <h5 class="modal-title" id="blockUserModalLabel">مسدود کردن کاربر</h5>
        <button type="button" class="btn-close" wire:click="$dispatch('hideModal')" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form wire:submit.prevent="blockUser">
            <div class="mb-4 position-relative">
                <label for="blockedAt" class="label-top-input-special-takhasos">تاریخ شروع مسدودیت</label>
                <input data-jdp type="text" class="form-control h-50" id="blockedAt" wire:model.live="blockedAt">
                @error('blockedAt')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                <label for="unblockedAt" class="label-top-input-special-takhasos">تاریخ پایان مسدودیت (اختیاری)</label>
                <input data-jdp type="text" class="form-control h-50" id="unblockedAt" wire:model.live="unblockedAt">
                @error('unblockedAt')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4 position-relative">
                <textarea class="form-control" id="blockReason" rows="3" wire:model.live="blockReason"
                    placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
                @error('blockReason')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn my-btn-primary w-100 h-50">مسدود کردن</button>
        </form>
    </div>
</div>