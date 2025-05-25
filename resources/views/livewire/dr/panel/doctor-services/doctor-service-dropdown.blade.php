<tr>
  <td class="text-center align-middle">
    <input type="checkbox" wire:model.live="selectedDoctorServices" value="{{ $service->id }}"
      class="form-check-input m-0">
  </td>
  <td class="text-center align-middle">{{ $index }}</td>
  <td class="align-middle">
    <div class="d-flex align-items-center gap-2">
      @if ($service->children->isNotEmpty())
        <button class="dropdown-toggle btn btn-link p-0" data-bs-target="children-{{ $service->id }}">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6" />
          </svg>
        </button>
      @endif
      {{ $service->name }}
    </div>
  </td>
  <td class="align-middle">{{ $service->description }}</td>
  <td class="text-center align-middle">
    <button wire:click="toggleStatus({{ $service->id }})"
      class="badge {{ $service->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
      {{ $service->status ? 'فعال' : 'غیرفعال' }}
    </button>
  </td>
  <td class="text-center align-middle">
    <div class="d-flex justify-content-center gap-2">
      <a href="{{ route('dr.panel.doctor-services.edit', $service->id) }}"
        class="btn btn-gradient-success rounded-pill px-3">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path
            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
      </a>
      <button wire:click="confirmDelete({{ $service->id }})" class="btn btn-gradient-danger rounded-pill px-3">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
      </button>
    </div>
  </td>
</tr>

<!-- زیرمجموعه‌ها به‌صورت دراپ‌داون -->
@if ($service->children->isNotEmpty())
  <tr id="children-{{ $service->id }}" class="d-none nested-row">
    <td colspan="6" class="p-0">
      <table class="table table-bordered w-100 m-0">
        <tbody>
          @foreach ($service->children as $child)
            @include('livewire.dr.panel.doctor-services.doctor-service-dropdown', [
                'service' => $child,
                'level' => $level + 1,
                'index' => $index . '.' . ($loop->index + 1),
            ])
          @endforeach
        </tbody>
      </table>
    </td>
  </tr>
@endif
