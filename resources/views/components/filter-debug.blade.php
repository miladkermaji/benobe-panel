@props(['filters' => [], 'searchPlaceholder' => 'جستجو...'])

<div class="filter-debug-container" x-data="{ showDebug: false }">
  <!-- Debug Toggle -->
  <button @click="showDebug = !showDebug" class="btn btn-sm btn-outline-secondary mb-2">
    <x-svg-icon name="settings" size="16" />
    Debug Filters
  </button>

  <!-- Debug Panel -->
  <div x-show="showDebug" x-transition class="debug-panel p-3 bg-light border rounded mb-3">
    <h6>Filter Debug Info:</h6>
    <div class="row">
      <div class="col-md-6">
        <strong>Current Filters:</strong>
        <pre id="currentFilters" class="mt-1"></pre>
      </div>
      <div class="col-md-6">
        <strong>Livewire Status:</strong>
        <div id="livewireStatus" class="mt-1"></div>
      </div>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="filter-controls">
    @foreach ($filters as $filter)
      <div class="filter-group mb-3">
        @if ($filter['type'] === 'search')
          <div class="search-box position-relative">
            <input type="text" wire:model.live.debounce.300ms="{{ $filter['model'] }}" class="form-control ps-5"
              placeholder="{{ $filter['placeholder'] ?? $searchPlaceholder }}" x-on:input="updateDebugInfo()">
            <x-svg-icon name="search" size="16" class="search-icon" />
          </div>
        @elseif($filter['type'] === 'select')
          <label class="form-label">{{ $filter['label'] ?? '' }}</label>
          <select class="form-select" wire:model.live="{{ $filter['model'] }}" x-on:change="updateDebugInfo()">
            @foreach ($filter['options'] as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        @endif
      </div>
    @endforeach
  </div>

  <script>
    function updateDebugInfo() {
      // Get current filter values
      const filters = {};
      document.querySelectorAll('[wire\\:model\\.live]').forEach(el => {
        const model = el.getAttribute('wire:model.live') || el.getAttribute('wire:model.live.debounce.300ms');
        if (model) {
          filters[model] = el.value;
        }
      });

      // Update debug display
      document.getElementById('currentFilters').textContent = JSON.stringify(filters, null, 2);

      // Check Livewire status
      const livewireStatus = document.getElementById('livewireStatus');
      if (window.Livewire) {
        livewireStatus.innerHTML = '<span class="text-success">✓ Livewire Active</span>';
      } else {
        livewireStatus.innerHTML = '<span class="text-danger">✗ Livewire Not Found</span>';
      }
    }

    // Initialize debug info
    document.addEventListener('DOMContentLoaded', function() {
      updateDebugInfo();

      // Update on any filter change
      document.addEventListener('livewire:update', updateDebugInfo);
    });
  </script>

  <style>
    .debug-panel {
      font-size: 12px;
    }

    .debug-panel pre {
      background: #f8f9fa;
      padding: 8px;
      border-radius: 4px;
      font-size: 11px;
    }

    .search-icon {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #6c757d;
    }
  </style>
</div>
