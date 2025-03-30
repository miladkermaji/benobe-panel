@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
  <style>
    .myPanelOption {
      display: none;
    }
  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', ' مدیریت مطب ')

<!-- مودال ویرایش مطب (دست‌نخورده) -->
<div class="modal fade" id="clinicEditModal" tabindex="-1" role="dialog" aria-labelledby="clinicEditModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="clinicEditModalLabel">ویرایش مطب</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="edit-clinic-form">
          @csrf
          <input type="hidden" name="id" id="edit-clinic-id">
          <div class="d-flex flex-column mt-2">
            <div class="position-relative d-flex gap-4 w-100">
              <div class="position-relative w-100">
                <label class="label-top-input-special-takhasos" for="edit-clinic-name">نام مطب: </label>
                <input type="text" class="form-control h-50 w-100" id="edit-clinic-name" name="name">
                <small class="text-danger error-name"></small>
              </div>
            </div>
          </div>
          <div>
            <div class="w-100 position-relative mt-4">
              <label class="label-top-input-special-takhasos" for="edit-clinic-province">استان: </label>
              <select class="form-control h-50 w-100" id="edit-clinic-province" name="province_id">
                <option value="">انتخاب استان</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
              </select>
              <small class="text-danger error-province_id"></small>
            </div>
            <div class="w-100 position-relative mt-4">
              <label class="label-top-input-special-takhasos" for="edit-clinic-city">شهر: </label>
              <select class="form-control h-50 w-100" id="edit-clinic-city" name="city_id" disabled>
                <option value="">انتخاب شهر</option>
              </select>
              <small class="text-danger error-city_id"></small>
            </div>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="edit-clinic-phone">شماره موبایل: </label>
            <input type="text" class="form-control h-50 w-100" id="edit-clinic-phone" name="phone_numbers[]">
          </div>
          <small class="text-danger error-phone_numbers"></small>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="edit-clinic-address">آدرس: </label>
            <textarea class="form-control h-50 w-100" id="edit-clinic-address" name="address" placeholder="آدرس" rows="3"></textarea>
            <small class="text-danger error-address"></small>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="edit-clinic-description">توضیحات: </label>
            <textarea class="form-control h-50 w-100" id="edit-clinic-description" name="description" placeholder="توضیحات"
              rows="3"></textarea>
            <small class="text-danger error-description"></small>
          </div>
          <div class="w-100 mt-4">
            <button type="submit" class="btn btn-primary w-100 d-flex justify-content-center align-items-center h-50">
              <span class="button_text">ذخیره تغییرات</span>
              <div class="loader" style="display: none;"></div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- مودال افزودن مطب (دست‌نخورده) -->
<div class="modal fade" id="clinicModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"> افزودن مطب </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-clinic-form">
          @csrf
          <div class="d-flex flex-column mt-2">
            <div class="position-relative w-100">
              <label class="label-top-input-special-takhasos" for="clinic-name">نام مطب: </label>
              <input type="text" class="form-control h-50 w-100" id="clinic-name" name="name">
              <small class="text-danger error-name"></small>
            </div>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="clinic-province">استان: </label>
            <select class="form-control h-50 w-100" id="clinic-province" name="province_id">
              <option value="">انتخاب استان</option>
              @foreach ($provinces as $province)
                <option value="{{ $province->id }}">{{ $province->name }}</option>
              @endforeach
            </select>
            <small class="text-danger error-province_id"></small>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="clinic-city">شهر: </label>
            <select class="form-control h-50 w-100" id="clinic-city" name="city_id" disabled>
              <option value="">انتخاب شهر</option>
            </select>
            <small class="text-danger error-city_id"></small>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="clinic-phone">شماره موبایل: </label>
            <input type="text" class="form-control h-50 w-100" id="clinic-phone" name="phone_numbers[]">
          </div>
          <small class="text-danger error-phone_numbers"></small>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="clinic-address"> آدرس: </label>
            <textarea class="form-control h-50 w-100" placeholder="آدرس" id="clinic-address" name="address" cols="30"
              rows="3"></textarea>
            <small class="text-danger error-address"></small>
          </div>
          <div class="w-100 position-relative mt-4">
            <label class="label-top-input-special-takhasos" for="clinic-description">توضیحات: </label>
            <textarea name="description" class="form-control h-50 w-100" placeholder="توضیحات" id="clinic-description"
              cols="30" rows="3"></textarea>
            <small class="text-danger error-description"></small>
          </div>
          <div class="w-100 mt-4">
            <button type="submit"
              class="btn btn-primary w-100 d-flex justify-content-center align-items-center h-50">
              <span class="button_text">ذخیره</span>
              <div class="loader" style="display: none;"></div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- بخش محتوا با جدول بوت‌استرپ -->
<div class="subuser-content w-100 d-flex justify-content-center mt-4">
  <div class="subuser-content-wrapper p-3 w-100">
    <div class="w-100 d-flex justify-content-end">
      <button class="btn btn-primary h-50" id="add-clinic-btn">افزودن مطب جدید</button>
    </div>
    <div class="p-3">
      <h4 class="text-dark font-weight-bold">لیست مطب‌های من</h4>
    </div>
    <div class="mt-2">
      <table class="table table-modern table-striped table-bordered table-hover" id="clinic-list">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>نام مطب</th>
            <th>استان</th>
            <th>شهر</th>
            <th>آدرس</th>
            <th>توضیحات</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($clinics as $index => $clinic)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $clinic->name }}</td>
              <td>{{ $clinic->province->name }}</td>
              <td>{{ $clinic->city->name }}</td>
              <td>{{ $clinic->address ?? 'نامشخص' }}</td>
              <td>{{ $clinic->description ?? '---' }}</td>
              <td><span
                  class="{{ $clinic->is_active ? 'text-success' : 'text-danger' }}">{{ $clinic->is_active ? 'تایید شده' : 'تایید نشده' }}</span>
              </td>
              <td>
                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="{{ $clinic->id }}"
                  title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $clinic->id }}"
                  title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
                <a href="{{ route('dr.panel.clinics.gallery', $clinic->id) }}"
                  class="btn btn-light btn-sm rounded-circle gallery-btn" data-id="{{ $clinic->id }}"
                  title="گالری تصاویر">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M4 16v4h4M4 20l4-4M20 8v-4h-4M20 4l-4 4M4 4v4M4 4h4M20 20v-4h-4M20 20l-4-4"></path>
                  </svg>
                </a>

              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center">هیچ مطبی یافت نشد</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  const cities = @json($cities);

  function handleValidationErrors(xhr, form) {
    if (xhr.status === 422) {
      $('.label-top-input-special-takhasos').css({
        'position': '',
        'bottom': ''
      });
      const errors = xhr.responseJSON.errors;
      form.find('.text-danger').text('');
      Object.keys(errors).forEach(function(key) {
        const fieldKey = key.replace(/\.\d+$/, '');
        form.find(`.error-${fieldKey}`).text(errors[key][0]);
        const relatedLabel = form.find(`input[name="${key}"], select[name="${key}"]`).siblings(
          '.label-top-input-special-takhasos');
        if (relatedLabel.length > 0) {
          relatedLabel.css({
            'position': 'absolute',
            'bottom': relatedLabel.hasClass('password-label') ? '32px' : '56px'
          });
        }
      });
    }
  }

  function loadClinics() {
    $.get("{{ route('dr-clinic-management') }}", function(response) {
      const container = $('#clinic-list tbody');
      container.empty();
      if (response.clinics.length === 0) {
        container.append('<tr><td colspan="5" class="text-center">مطب فعالی ندارید.</td></tr>');
      } else {
        response.clinics.forEach(function(clinic, index) {
          const clinicRow = `
            <tr>
              <td>${index + 1}</td>
              <td>${clinic.name}</td>
              <td>${clinic.province.name}</td>
              <td>${clinic.city.name}</td>
              <td>${clinic.address ?? 'نامشخص'}</td>
              <td>${clinic.description ?? '---'}</td>
              <td><span class="${clinic.is_active ? 'text-success' : 'text-danger'}">${clinic.is_active ? 'تایید شده' : 'تایید نشده'}</span></td>
              <td>
                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${clinic.id}" title="ویرایش">
                  <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                </button>
                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${clinic.id}" title="حذف">
                  <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
                <a href="{{ route('dr.panel.clinics.gallery', $clinic->id) }}" class="btn btn-light btn-sm rounded-circle gallery-btn" data-id="${clinic.id}" title="گالری تصاویر">
                   <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
                    <path d="M4 16v4h4M4 20l4-4M20 8v-4h-4M20 4l-4 4M4 4v4M4 4h4M20 20v-4h-4M20 20l-4-4"></path>
                  </svg>
                </a>
                
              </td>
            </tr>`;
          container.append(clinicRow);
        });
      }
    });
  }

  function populateCities(provinceId, citySelect, selectedCityId = null) {
    citySelect.empty().append('<option value="">انتخاب شهر</option>');
    if (provinceId && cities[provinceId]) {
      cities[provinceId].forEach(function(city) {
        const isSelected = selectedCityId === city.id ? 'selected' : '';
        citySelect.append(`<option value="${city.id}" ${isSelected}>${city.name}</option>`);
      });
      citySelect.prop('disabled', false);
    } else {
      citySelect.prop('disabled', true);
    }
  }

  $(document).ready(function() {
    $('#clinic-province').on('change', function() {
      const provinceId = $(this).val();
      const citySelect = $('#clinic-city');
      citySelect.empty().append('<option value="">انتخاب شهر</option>');
      if (provinceId && cities[provinceId]) {
        cities[provinceId].forEach(function(city) {
          citySelect.append(`<option value="${city.id}">${city.name}</option>`);
        });
        citySelect.prop('disabled', false);
      } else {
        citySelect.prop('disabled', true);
      }
    });

    $('#add-clinic-btn').on('click', function() {
      $('#clinicModal').modal('show');
      if ($('#clinic-form').length) {
        $('#clinic-form').trigger('reset');
      }
      $('#clinic-id').val('');
      $('#clinic-city').empty().append('<option value="">انتخاب شهر</option>').prop('disabled', true);
    });

    $(document).on('click', '.edit-btn', function() {
      const clinicId = $(this).data('id');
      $.ajax({
        url: "{{ route('dr-clinic-edit', ':id') }}".replace(':id', clinicId),
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          $('#clinicEditModal').modal('show');
          $('#edit-clinic-id').val(clinicId);
          $('#edit-clinic-name').val(response.name);
          $('#edit-clinic-address').val(response.address);
          $('#edit-clinic-description').val(response.description);
          $('#edit-clinic-province').val(response.province_id);
          populateCities(response.province_id, $('#edit-clinic-city'), response.city_id);
          const phoneContainer = $('#edit-clinic-phone').parent();
          phoneContainer.empty();
          if (response.phone_numbers && response.phone_numbers.length > 0) {
            response.phone_numbers.forEach((phone, index) => {
              phoneContainer.append(`
                <div class="w-100 position-relative mt-2">
                  <label class="label-top-input-special-takhasos">شماره موبایل ${index + 1}:</label>
                  <input type="text" class="form-control h-50 w-100" name="phone_numbers[]" value="${phone}">
                </div>
              `);
            });
          } else {
            phoneContainer.append(`
              <div class="w-100 position-relative mt-2">
                <label class="label-top-input-special-takhasos">شماره موبایل:</label>
                <input type="text" class="form-control h-50 w-100" name="phone_numbers[]">
              </div>
            `);
          }
        },
        error: function() {
          toastr.error('خطا در دریافت اطلاعات مطب!');
        },
      });
    });

    $('#edit-clinic-province').on('change', function() {
      const provinceId = $(this).val();
      populateCities(provinceId, $('#edit-clinic-city'));
    });

    $('#add-clinic-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');
      buttonText.hide();
      loader.show();
      $.ajax({
        url: "{{ route('dr-clinic-store') }}",
        method: 'POST',
        data: form.serialize(),
        success: function() {
          toastr.success('مطب با موفقیت اضافه شد!');
          $('#clinicModal').modal('hide');
          loadClinics();
        },
        error: function(xhr) {
          handleValidationErrors(xhr, form);
          toastr.error('خطا در ذخیره اطلاعات!');
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        },
      });
    });

    $('#edit-clinic-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');
      const clinicId = $('#edit-clinic-id').val();
      buttonText.hide();
      loader.show();
      $.ajax({
        url: "{{ route('dr-clinic-update', ':id') }}".replace(':id', clinicId),
        method: 'POST',
        data: form.serialize(),
        success: function() {
          toastr.success('مطب با موفقیت ویرایش شد!');
          $('#clinicEditModal').modal('hide');
          loadClinics();
        },
        error: function(xhr) {
          handleValidationErrors(xhr, form);
          toastr.error('خطا در ذخیره اطلاعات!');
        },
        complete: function() {
          buttonText.show();
          loader.hide();
        }
      });
    });

    $(document).on('click', '.delete-btn', function() {
      const clinicId = $(this).data('id');
      Swal.fire({
        title: 'حذف مطب',
        text: 'آیا از حذف این مطب اطمینان دارید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'لغو',
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('dr-clinic-destroy', ':id') }}".replace(':id', clinicId),
            method: 'DELETE',
            data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function() {
              toastr.success('مطب با موفقیت حذف شد!');
              loadClinics();
            },
            error: function() {
              toastr.error('خطا در حذف مطب!');
            },
          });
        }
      });
    });

    loadClinics();
  });
</script>
@endsection
