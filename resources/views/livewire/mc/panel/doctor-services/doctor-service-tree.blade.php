<div class="service-row" style="padding-right: {{ $level * 20 }}px;">
    <div class="service-name">
        @if ($level > 0)
            <span class="tree-line" style="right: {{ $level * 20 - 10 }}px;"></span>
            <span class="tree-connector" style="right: {{ $level * 20 - 10 }}px;"></span>
        @endif
        @if ($service->children->isNotEmpty())
            <span class="tree-toggle-wrapper" wire:click="toggleChildren({{ $service->id }})">
                <svg class="tree-toggle {{ $service->isOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2">
                    <circle cx="12" cy="12" r="10" fill="none" stroke="var(--border-neutral)" />
                    <path d="M16 10l-4 4-4-4" />
                </svg>
            </span>
        @else
            <span class="tree-placeholder"></span>
        @endif
        <input type="checkbox" wire:model.live="selectedDoctorServices" value="{{ $service->id }}" class="form-check-input">
        <span>{{ $service->insurance->name ?? 'بیمه نامشخص' }}</span>
    </div>
    <div class="service-description">{{ $service->description ?? 'بدون توضیحات' }}</div>
    <div class="service-duration">{{ $service->duration ? $service->duration . ' دقیقه' : '---' }}</div>
    <div class="service-price">
        <span class="price-badge {{ $service->price ? 'price-active' : '' }}">{{ $service->price ? number_format($service->price) . ' تومان' : '---' }}</span>
    </div>
    <div class="service-discount">
        <span class="price-badge {{ $service->discount > 0 ? 'discount-active' : '' }}">
            @if ($service->discount > 0 && $service->price)
                {{ number_format(($service->price * $service->discount) / 100) . ' تومان' }}
            @else
                ---
            @endif
        </span>
    </div>
    <div class="service-final-price">
        <span class="price-badge {{ $service->price ? 'final-price-active' : '' }}">{{ $service->price ? number_format($service->price - ($service->price * $service->discount) / 100) . ' تومان' : '---' }}</span>
    </div>
    <div class="service-status">
        <button wire:click="toggleStatus({{ $service->id }})" class="status-badge {{ $service->status ? 'status-active' : 'status-inactive' }}">{{ $service->status ? 'فعال' : 'غیرفعال' }}</button>
    </div>
    <div class="service-actions">
        <a href="{{ route('mc.panel.doctor-services.edit', $service->id) }}" class="btn btn-outline-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
        </a>
        <button wire:click="confirmDelete({{ $service->id }})" class="btn btn-outline-danger btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
        </button>
    </div>
</div>
@foreach ($service->children as $child)
    @if ($service->isOpen ?? true)
        <div class="service-child {{ $service->isOpen ? 'child-open' : '' }}">
            @include('livewire.mc.panel.doctor-services.doctor-service-tree', ['service' => $child, 'level' => $level + 1, 'index' => $index . '.' . ($loop->index + 1)])
        </div>
    @endif
@endforeach