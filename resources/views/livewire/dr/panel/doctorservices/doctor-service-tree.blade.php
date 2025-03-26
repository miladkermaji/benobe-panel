<tr class="tree-row" style="background-color: {{ $level % 2 === 0 ? '#f8fafc' : '#ffffff' }}; transition: background-color 0.3s;">
    <td class="text-center align-middle" style="width: 50px; padding: 0;">
        <input type="checkbox" wire:model.live="selectedDoctorServices" value="{{ $service->id }}" class="form-check-input m-0">
    </td>
    <td class="text-center align-middle" style="width: 70px; font-size: 0.9rem; color: #6b7280;">{{ $index }}</td>
    <td class="align-middle" style="padding-right: {{ $level * 30 + 20 }}px; position: relative; font-size: 0.95rem;">
        <span class="d-flex align-items-center gap-2 position-relative">
            @if ($level > 0)
                <span class="tree-line" style="position: absolute; right: {{ $level * 30 - 15 }}px; top: -50%; height: 100%; width: 1px; background: #d1d5db; z-index: 0;"></span>
                <span class="tree-connector" style="position: absolute; right: {{ $level * 30 - 15 }}px; top: 50%; width: 15px; height: 1px; background: #d1d5db; z-index: 0;"></span>
            @endif
            @if ($service->children->isNotEmpty())
                <svg wire:click="toggleChildren({{ $service->id }})" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" class="tree-toggle cursor-pointer transition-transform duration-200 {{ $service->isOpen ? 'rotate-90' : '' }}">
                    <path d="M9 18l6-6-6-6" />
                </svg>
            @else
                <span style="width: 16px; height: 16px;"></span>
            @endif
            <span class="service-name" style="font-weight: 600; color: #1f2937; z-index: 1;">{{ $service->name }}</span>
        </span>
    </td>
    <td class="align-middle" style="color: #6b7280; font-size: 0.9rem; padding: 15px;">{{ $service->description ?? 'بدون توضیحات' }}</td>
    <td class="text-center align-middle" style="width: 100px;">
        <button wire:click="toggleStatus({{ $service->id }})" class="badge rounded-pill {{ $service->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0 cursor-pointer px-3 py-1 transition-colors duration-200 hover:brightness-110">
            {{ $service->status ? 'فعال' : 'غیرفعال' }}
        </button>
    </td>
    <td class="text-center align-middle" style="width: 150px;">
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('dr.panel.doctor-services.edit', $service->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 transition-colors duration-200 hover:bg-success hover:text-white">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
            </a>
            <button wire:click="confirmDelete({{ $service->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 transition-colors duration-200 hover:bg-danger hover:text-white">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                </svg>
            </button>
        </div>
    </td>
</tr>

@foreach ($service->children as $child)
    @if ($service->isOpen ?? true) <!-- نمایش زیرمجموعه‌ها فقط در صورت باز بودن -->
        @include('livewire.dr.panel.doctorservices.doctor-service-tree', [
            'service' => $child,
            'level' => $level + 1,
            'index' => $index . '.' . ($loop->index + 1)
        ])
    @endif
@endforeach