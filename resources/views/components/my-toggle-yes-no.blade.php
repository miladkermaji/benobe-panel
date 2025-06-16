@props([
    'isChecked' => false,
    'id' => '',
    'model' => '',
    'day' => '',
    'class' => '',
])

<div class="toggle-yes-no {{ $class }}" dir="rtl">
  <div class="toggle-wrapper">
    <input type="checkbox" id="{{ $id }}" class="toggle-input" {{ $isChecked ? 'checked' : '' }}
      wire:model.live="{{ $model }}" wire:change="updateAutoScheduling">
    <label for="{{ $id }}" class="toggle-label">
      <span class="toggle-text">{{ $day }}</span>
      <div class="toggle-switch">
        <div class="toggle-slider">
          <span class="toggle-text-yes">بله</span>
          <span class="toggle-text-no">خیر</span>
        </div>
      </div>
    </label>
  </div>
</div>

<style>
  .toggle-yes-no {
    position: relative;
    display: inline-block;
  }

  .toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .toggle-input {
    display: none;
  }

  .toggle-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background: var(--background-card);
    border-radius: var(--radius-button);
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
    border: 1px solid var(--border-neutral);
    box-shadow: 0 1px 2px var(--shadow);
  }

  .toggle-text {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
    margin-left: 6px;
    white-space: nowrap;
  }

  .toggle-switch {
    position: relative;
    width: 70px;
    height: 22px;
    background: var(--background-light);
    border-radius: var(--radius-button);
    transition: all 0.3s ease;
  }

  .toggle-slider {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 32px;
    height: 18px;
    background: var(--text-original);
    border-radius: var(--radius-button);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 1px 2px var(--shadow);
  }

  .toggle-text-yes,
  .toggle-text-no {
    position: absolute;
    font-size: 11px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: white;
    opacity: 0;
  }

  .toggle-text-yes {
    right: 8px;
  }

  .toggle-text-no {
    left: 8px;
  }

  .toggle-input:checked+.toggle-label .toggle-switch {
    background: var(--background-light);
  }

  .toggle-input:checked+.toggle-label .toggle-slider {
    right: 36px;
    background: var(--text-discount);
  }

  .toggle-input:checked+.toggle-label .toggle-text-yes {
    opacity: 1;
  }

  .toggle-input:not(:checked)+.toggle-label .toggle-switch {
    background: var(--background-light);
  }

  .toggle-input:not(:checked)+.toggle-label .toggle-slider {
    background: var(--text-original);
  }

  .toggle-input:not(:checked)+.toggle-label .toggle-text-no {
    opacity: 1;
  }

  .toggle-label:hover {
    box-shadow: 0 2px 4px var(--shadow);
  }

  .toggle-label:active .toggle-slider {
    transform: scale(0.95);
  }
</style>
