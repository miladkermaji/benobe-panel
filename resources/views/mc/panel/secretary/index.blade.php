@extends('mc.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/secretary/secretaries.css') }}" rel="stylesheet">
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('bread-crumb-title', 'مدیریت منشی')

@section('content')
  @livewire('mc.panel.secretary.secretary-list')
@endsection

@section('scripts')
  <script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
  <script>
    var appointmentsSearchUrl = "{{ route('search.appointments') }}";
    var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
    let selectedSecretaries = [];

    // نمایش مودال افزودن منشی
    $('#add-secretary-btn').on('click', function() {
      $('#addSecretaryModal').modal('show');
    });

    // انتخاب همه
    $('#selectAll').on('change', function() {
      const isChecked = $(this).is(':checked');
      $('.select-secretary').prop('checked', isChecked);
      selectedSecretaries = isChecked ? $('.select-secretary').map((i, el) => $(el).val()).get() : [];
      toggleGroupActions();
    });

    // انتخاب تکی
    $(document).on('change', '.select-secretary', function() {
      selectedSecretaries = $('.select-secretary:checked').map((i, el) => $(el).val()).get();
      $('#selectAll').prop('checked', selectedSecretaries.length === $('.select-secretary').length);
      toggleGroupActions();
    });

    // نمایش/مخفی کردن بخش عملیات گروهی
    function toggleGroupActions() {
      $('#groupActions').toggle(selectedSecretaries.length > 0);
      $('#executeGroupAction').prop('disabled', selectedSecretaries.length === 0 || !$('#groupActionSelect').val());
    }

    // تغییر در منوی کشویی
    $('#groupActionSelect').on('change', function() {
      $('#executeGroupAction').prop('disabled', !$(this).val() || selectedSecretaries.length === 0);
    });

    // اجرای عملیات گروهی
    $('#executeGroupAction').on('click', function() {
      if (selectedSecretaries.length === 0) {
        toastr.warning('هیچ منشی‌ای انتخاب نشده است.');
        return;
      }

      const action = $('#groupActionSelect').val();
      if (!action) {
        toastr.warning('لطفاً یک عملیات را انتخاب کنید.');
        return;
      }

      const button = $(this);
      const loader = button.find('.loader');
      const buttonText = button.find('span').first();
      button.prop('disabled', true);
      buttonText.hide();
      loader.show();

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: `آیا می‌خواهید عملیات "${$('#groupActionSelect option:selected').text()}" را روی ${selectedSecretaries.length} منشی اجرا کنید؟`,

        showCancelButton: true,
        confirmButtonText: 'بله، اجرا کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('mc-secretary-group-action') }}",
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              secretary_ids: selectedSecretaries,
              action: action,
              selectedClinicId: localStorage.getItem('selectedClinicId') || 'default'
            },
            success: function(response) {
              if (response.success) {
                toastr.success(response.message);
                updateSecretaryList(response.secretaries);
                selectedSecretaries = [];
                $('#selectAll').prop('checked', false);
                toggleGroupActions();
              } else {
                toastr.error(response.message);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || 'خطا در اجرای عملیات گروهی!');
            },
            complete: function() {
              button.prop('disabled', false);
              buttonText.show();
              loader.hide();
            }
          });
        } else {
          button.prop('disabled', false);
          buttonText.show();
          loader.hide();
        }
      });
    });

    // به‌روزرسانی لیست منشی‌ها
    function updateSecretaryList(secretaries) {
      const container = $('#secretary-list tbody');
      container.empty();

      if (secretaries.length === 0) {
        container.append(
          `<tr><td colspan="8" class="text-center">شما منشی فعالی ندارید</td></tr>`
        );
      } else {
        secretaries.forEach((secretary, index) => {
          const row = `
                        <tr data-id="${secretary.id}">
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" class="form-check-input m-0 align-middle select-secretary" value="${secretary.id}">
                                </div>
                            </td>
                            <td>${index + 1}</td>
                            <td>${secretary.first_name} ${secretary.last_name}</td>
                            <td>${secretary.mobile}</td>
                            <td>${secretary.national_code}</td>
                            <td>${secretary.gender === 'male' ? 'مرد' : 'زن'}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        data-id="${secretary.id}"
                                        data-status="${secretary.status}"
                                        ${secretary.status == 1 ? 'checked' : ''}
                                        onchange="toggleStatus(this)"
                                        style="width: 3em; height: 1.5em; margin-top: 0;">
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${secretary.id}" title="ویرایش">
                                    <img src="{{ asset('mc-assets/icons/edit.svg') }}" alt="ویرایش">
                                </button>
                                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${secretary.id}" title="حذف">
                                    <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                                </button>
                            </td>
                        </tr>`;
          container.append(row);
        });
      }
    }

    $('#add-secretary-form').on('submit', function(e) {
      e.preventDefault();

      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      // پاکسازی خطاهای قبلی
      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');
      form.find('.upper-row').removeClass('upper-row-error');
      form.removeClass('both-upper-fields-error');

      $.ajax({
        url: "{{ route('mc-secretary-store') }}",
        method: 'POST',
        data: form.serialize() + '&selectedClinicId=' + (localStorage.getItem('selectedClinicId') || 'default'),
        success: function(response) {
          toastr.success('✅ منشی با موفقیت اضافه شد!');
          $('#addSecretaryModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSecretaryList(response.secretaries);
          form[0].reset();
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;

            Object.keys(errors).forEach(function(key) {
              const errorElement = form.find(`.error-${key}`);
              errorElement.text(errors[key][0]);
              errorElement.closest('.field-wrapper').addClass('has-error');
            });

            const hasFirstNameError = !!form.find('.error-first_name').text();
            const hasLastNameError = !!form.find('.error-last_name').text();

            if (hasFirstNameError || hasLastNameError) {
              form.find('.upper-row').addClass('upper-row-error');
            }

            if (hasFirstNameError && hasLastNameError) {
              form.addClass('both-upper-fields-error');
            }
          } else {
            toastr.error('❌ خطا در ذخیره اطلاعات!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    // دریافت اطلاعات منشی برای ویرایش
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';

      $.get(
        "{{ route('mc-secretary-edit', ':id') }}".replace(':id', id) + '?selectedClinicId=' + selectedClinicId,
        function(response) {
          $('#edit-secretary-id').val(response.id);
          $('#edit-first-name').val(response.first_name);
          $('#edit-last-name').val(response.last_name);
          $('#edit-mobile').val(response.mobile);
          $('#edit-national-code').val(response.national_code);
          $('#edit-gender').val(response.gender);
          $('#editSecretaryModal').modal('show');
        }
      ).fail(function() {
        toastr.error('❌ خطا در دریافت اطلاعات منشی!');
      });
    });

    // ارسال فرم ویرایش منشی
    $('#edit-secretary-form').on('submit', function(e) {
      e.preventDefault();
      const id = $('#edit-secretary-id').val();
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      form.find('.text-danger').text('');
      form.find('.field-wrapper').removeClass('has-error');
      form.removeClass('both-upper-fields-error');
      form.find('.upper-row').removeClass('upper-row-error');

      const formData = form.serialize() + '&selectedClinicId=' + selectedClinicId;

      $.ajax({
        url: "{{ route('mc-secretary-update', ':id') }}".replace(':id', id),
        method: 'POST',
        data: formData,
        success: function(response) {
          toastr.success('✅ منشی با موفقیت ویرایش شد!');
          $('#editSecretaryModal').modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          updateSecretaryList(response.secretaries);
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;

            Object.keys(errors).forEach(function(key) {
              form.find(`.error-${key}`).text(errors[key][0]);
              form.find(`.field-${key}`).addClass('has-error');
            });

            const hasFirstNameError = form.find('.field-first_name').hasClass('has-error');
            const hasLastNameError = form.find('.field-last_name').hasClass('has-error');
            if (hasFirstNameError || hasLastNameError) {
              form.find('.upper-row').addClass('upper-row-error');
            }

            if (hasFirstNameError && hasLastNameError) {
              form.addClass('both-upper-fields-error');
            }
          } else {
            toastr.error('❌ خطا در ویرایش منشی!');
          }
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    // حذف منشی
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این عمل قابل بازگشت نیست!',

        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('mc-secretary-delete', ':id') }}".replace(':id', id) + '?selectedClinicId=' +
              selectedClinicId,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              toastr.success('✅ منشی با موفقیت حذف شد!');
              updateSecretaryList(response.secretaries);
              selectedSecretaries = selectedSecretaries.filter(secretaryId => secretaryId != id);
              toggleGroupActions();
            },
            error: function() {
              toastr.error('❌ خطا در حذف منشی!');
            },
          });
        }
      });
    });

    // تغییر وضعیت منشی
    function toggleStatus(element) {
      const id = $(element).data('id');
      const currentStatus = $(element).data('status');
      const newStatus = currentStatus === 1 ? 0 : 1;
      const statusText = newStatus === 1 ? 'فعال' : 'غیرفعال';

      Swal.fire({
        title: 'تغییر وضعیت',
        text: `آیا می‌خواهید وضعیت این منشی را به "${statusText}" تغییر دهید؟`,

        showCancelButton: true,
        confirmButtonText: 'بله، تغییر کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('mc-secretary-update-status') }}",
            method: 'PATCH',
            data: {
              _token: '{{ csrf_token() }}',
              id: id,
              status: newStatus,
              selectedClinicId: localStorage.getItem('selectedClinicId') || 'default'
            },
            success: function(response) {
              if (response.success) {
                $(element)
                  .prop('checked', newStatus === 1)
                  .data('status', newStatus);
                toastr.success(response.message);
                updateSecretaryList(response.secretaries);
              } else {
                toastr.error(response.message);
                $(element).prop('checked', currentStatus === 1);
              }
            },
            error: function(xhr) {
              toastr.error(xhr.responseJSON?.message || '❌ خطا در تغییر وضعیت!');
              $(element).prop('checked', currentStatus === 1);
            }
          });
        } else {
          $(element).prop('checked', currentStatus === 1);
        }
      });
    }
  </script>
@endsection
