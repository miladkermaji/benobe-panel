<div id="ticket-create-form" class="container py-4" dir="rtl">
  <div class="card shadow-sm rounded-2">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">افزودن تیکت جدید</h4>
      <a href="{{ route('admin.panel.tickets.index') }}" class="btn btn-light btn-sm">بازگشت به لیست</a>
    </div>
    <div class="card-body">
      <form wire:submit.prevent="submit">
        <div class="mb-3" wire:ignore>
          <label for="user_id" class="label-top-input-special-takhasos">کاربر</label>
          <select id="user_id" class="form-select select2-ajax" wire:model.defer="user_id"></select>
          @error('user_id')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3" wire:ignore>
          <label for="doctor_id" class="label-top-input-special-takhasos">پزشک</label>
          <select id="doctor_id" class="form-select select2-ajax-doctor" wire:model.defer="doctor_id"></select>
          @error('doctor_id')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label for="title" class="label-top-input-special-takhasos">عنوان تیکت <span class="text-danger">*</span></label>
          <input id="title" type="text" class="form-control" wire:model.defer="title">
          @error('title')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label for="description" class="label-top-input-special-takhasos">توضیحات <span class="text-danger">*</span></label>
          <textarea id="description" class="form-control" rows="4" wire:model.defer="description"></textarea>
          @error('description')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="mb-3">
          <label for="status" class="label-top-input-special-takhasos">وضعیت</label>
          <select id="status" class="form-select" wire:model.defer="status">
            <option value="open">باز</option>
            <option value="answered">پاسخ داده شده</option>
            <option value="pending">در حال بررسی</option>
            <option value="closed">بسته</option>
          </select>
          @error('status')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <button type="submit" class="btn btn-success w-100">ثبت تیکت</button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
  <script>
    function initSelect2TicketCreate() {
      const form = document.getElementById('ticket-create-form');
      if (!form) return;
      const wireId = form.closest('[wire\\:id]')?.getAttribute('wire:id');
      const livewireComponent = window.Livewire.find(wireId);
      if (!livewireComponent) return;
      // کاربر
      $('#user_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%',
        ajax: {
          url: '/admin/api/users/search',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              q: params.term
            };
          },
          processResults: function(data) {
            return {
              results: data.results
            };
          },
          cache: true
        }
      });
      $('#user_id').off('change').on('change', function() {
        livewireComponent.set('user_id', $(this).val());
      });
      if (livewireComponent.get('user_id')) {
        $.ajax({
          type: 'GET',
          url: '/admin/api/users/search',
          data: {
            q: ''
          },
          success: function(data) {
            var user = data.results.find(u => u.id == livewireComponent.get('user_id'));
            if (user) {
              var option = new Option(user.text, user.id, true, true);
              $('#user_id').append(option).trigger('change');
            }
          }
        });
      }
      // پزشک
      $('#doctor_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%',
        ajax: {
          url: '/admin/api/doctors/search',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              q: params.term
            };
          },
          processResults: function(data) {
            return {
              results: data.results
            };
          },
          cache: true
        }
      });
      $('#doctor_id').off('change').on('change', function() {
        livewireComponent.set('doctor_id', $(this).val());
      });
      if (livewireComponent.get('doctor_id')) {
        $.ajax({
          type: 'GET',
          url: '/admin/api/doctors/search',
          data: {
            q: ''
          },
          success: function(data) {
            var doctor = data.results.find(u => u.id == livewireComponent.get('doctor_id'));
            if (doctor) {
              var option = new Option(doctor.text, doctor.id, true, true);
              $('#doctor_id').append(option).trigger('change');
            }
          }
        });
      }
    }
    document.addEventListener('DOMContentLoaded', function() {
      Livewire.hook('message.processed', (message, component) => {
        initSelect2TicketCreate();
      });
      // اجرای اولیه برای بار اول
      initSelect2TicketCreate();
    });
  </script>
@endpush
