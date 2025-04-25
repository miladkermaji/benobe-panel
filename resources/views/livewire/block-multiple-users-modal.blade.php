<div>
    <div class="modal-header">
        <h5 class="modal-title" id="blockMultipleUsersModalLabel">مسدود کردن کاربران</h5>
        <button type="button" class="btn-close" wire:click="hideModal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form wire:submit.prevent="blockMultipleUsers">
            <div class="mb-3">
                <label for="blockedAtMultiple" class="form-label">تاریخ شروع مسدودیت</label>
                <input type="text" class="form-control" id="blockedAtMultiple" wire:model.live="blockedAt" data-jdp>
                @error('blockedAt')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="unblockedAtMultiple" class="form-label">تاریخ پایان مسدودیت (اختیاری)</label>
                <input type="text" class="form-control" id="unblockedAtMultiple" wire:model.live="unblockedAt" data-jdp>
                @error('unblockedAt')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="blockReasonMultiple" class="form-label">دلیل مسدودیت (اختیاری)</label>
                <textarea class="form-control" id="blockReasonMultiple" rows="3" wire:model.live="blockReason"
                    placeholder="دلیل مسدودیت را وارد کنید..."></textarea>
                @error('blockReason')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn my-btn-primary w-100" id="blockMultipleUsersSubmit">مسدود کردن</button>
        </form>
    </div>
</div>