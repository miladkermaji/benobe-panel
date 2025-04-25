<div class="modal fade bootstrap-scope" id="livewire-bootstrap-modal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 1055;"
     wire:ignore.self>
    <div class="modal-dialog {{ $size ?? 'modal-md' }} modal-dialog-centered modal-dialog-scrollable {{ $animation }}-animation">
        <div class="modal-content">
            @if ($alias)
                @livewire($alias, $params, key($activeModal))
            @endif
        </div>
    </div>
</div>