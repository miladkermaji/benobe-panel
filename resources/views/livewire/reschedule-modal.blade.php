<div class="modal-content rounded-2xl shadow-2xl">
    <div class="modal-header bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-100 p-6 rounded-t-2xl">
        <h6 class="modal-title text-lg font-bold text-blue-900">جابجایی نوبت</h6>
        <button type="button" class="btn-close text-gray-600 hover:text-gray-800" wire:click="hideModal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-6 bg-white rounded-b-2xl">
        <x-reschedule-calendar :appointmentId="$appointmentId" />
    </div>
</div>