<div>
    <div class="modal-header">
        <h6 class="modal-title fw-bold" id="endVisitModalCenterTitle">توضیحات درمان</h6>
        <button type="button" class="btn-close" wire:click="hideModal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <textarea class="form-control" rows="5" wire:model="endVisitDescription"
            placeholder="توضیحات درمان را وارد کنید..."></textarea>
        <button class="btn my-btn-primary w-100 mt-3 shadow-sm end-visit-btn"
            wire:click="endVisit">ثبت</button>
    </div>
</div>