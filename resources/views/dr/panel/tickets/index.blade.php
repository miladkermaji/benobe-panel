@extends('dr.panel.layouts.master')
@section('styles')
   <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
   <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
   <link type="text/css" href="{{ asset('dr-assets/panel/tickets/tickets.css') }}" rel="stylesheet" />
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
@section('bread-crumb-title', ' تیکت ها ')

<div class="container-fluid mt-4">
 <div class="row">
  <div class="col-md-12">
   <div class="card">
    <div class="card-header">
     <h4 class="card-title text-dark">مدیریت تیکت ها</h5>
      <button type="button" class="btn btn-primary h-50 float-right" data-toggle="modal"
       data-target="#add-ticket-modal">
       افزودن تیکت جدید
      </button>
    </div>
    <div class="card-body">
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>شناسه</th>
          <th>عنوان تیکت</th>
          <th>متن</th>
          <th>وضعیت</th> <!-- 🆕 اضافه شد -->
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody id="ticket-list">
        @foreach ($tickets as $ticket)
      <tr>
        <td>{{ $ticket->id }}</td>
        <td>{{ $ticket->title }}</td>
        <td>{{ Str::limit($ticket->description, 50) }}</td>
        <td>
        @if($ticket->status == 'open')
      <span class="badge badge-success">باز</span>
    @elseif($ticket->status == 'answered')
    <span class="badge badge-primary">پاسخ داده شده</span>
  @else
  <span class="badge badge-danger">بسته</span>
@endif
        </td>
        <td>
        <button class="btn btn-light rounded-circle btn-sm delete-btn" data-id="{{ $ticket->id }}">
          <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="">
        </button>
        <button onclick="location.href='{{ route('dr-panel-tickets.show', $ticket->id) }}'"
          class="btn btn-light rounded-circle btn-sm view-btn" data-id="{{ $ticket->id }}">
          <img src="{{ asset('dr-assets/icons/eye.svg') }}" alt="">
        </button>
        </td>
      </tr>
    @endforeach
      </tbody>
    </table>
    <div id="pagination-links" class="w-100 d-flex justify-content-center">
      {{ $tickets->links('pagination::bootstrap-4') }}
    </div>
  </div>

    </div>

   </div>
  </div>
 </div>
</div>

<!-- Modal برای افزودن تیکت -->
<div class="modal fade" id="add-ticket-modal" tabindex="-1" role="dialog" aria-labelledby="add-ticket-modal-label"
 aria-hidden="true">
 <div class="modal-dialog" role="document">
  <div class="modal-content border-radius-6">
   <div class="modal-header">
    <h5 class="modal-title" id="add-ticket-modal-label">افزودن تیکت جدید</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
     <span aria-hidden="true">&times;</span>
    </button>
   </div>
   <div class="modal-body">
    <form id="add-ticket-form">
     <div class="form-group position-relative">
      <label class="label-top-input-special-takhasos" for="title">عنوان تیکت</label>
      <input type="text" class="form-control h-50" id="title" name="title"
       placeholder="عنوان تیکت را وارد کنید">
     </div>
     <div class="form-group position-relative mt-3">
      <textarea class="form-control" id="description" name="description" placeholder="توضیحات تیکت را وارد کنید"></textarea>
     </div>
     <div class="d-flex w-100 justify-content-end mt-3">
      <button type="submit" class="btn btn-primary h-50 col-12 d-flex justify-content-center align-items-center"
       id="save-work-schedule">
       <span class="button_text"> ارسال تیکت</span>
       <div class="loader"></div>
      </button>
     </div>
    </form>
   </div>
  </div>
 </div>
</div>

@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
 var appointmentsSearchUrl = "{{ route('search.appointments') }}";
 var updateStatusAppointmentUrl =
  "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
 document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('showModal')) {
   // فرض کنید ID مودال شما "activation-modal" است  
   $('#activation-modal').modal('show');
  }
 });
 $('#add-ticket-modal').on('hidden.bs.modal', function() {
  $('body').removeClass('modal-open'); // حذف کلاس باز شدن مودال
  $('.modal-backdrop').remove(); // حذف بک‌دراپ
 });
 $(document).on('click', '#pagination-links a', function(e) {
  e.preventDefault();
  let page = $(this).attr('href').split('page=')[1]; // گرفتن شماره صفحه از URL
  fetchTickets(page);
 });

 function fetchTickets(page) {
  $.ajax({
   url: "?page=" + page,
   type: "GET",
   success: function(response) {
    $('#ticket-list').html($(response).find('#ticket-list').html());
    $('#pagination-links').html($(response).find('#pagination-links').html());
   },
   error: function() {
    toastr.error("❌ خطا در بارگذاری تیکت‌ها!");
   }
  });
 }


  $(document).ready(function () {

    // 📌 مدیریت ارسال تیکت جدید
    $('#add-ticket-form').on('submit', function (e) {
      e.preventDefault();

      const form = $(this);
      const submitButton = form.find('button[type="submit"]');
      const loader = submitButton.find('.loader');
      const buttonText = submitButton.find('.button_text');

      buttonText.hide();
      loader.show();

      $.ajax({
        url: "{{ route('dr-panel-tickets.store') }}",
        method: 'POST',
        data: form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          toastr.success('✅ تیکت با موفقیت اضافه شد!');
          $('#add-ticket-modal').modal('hide');
          updateTicketList(response.tickets);
        },
        error: function () {
          toastr.error('❌ خطا در افزودن تیکت!');
        },
        complete: function () {
          buttonText.show();
          loader.hide();
        }
      });
    });

    // 📌 مدیریت حذف تیکت
    $(document).on('click', '.delete-btn', function () {
      const id = $(this).data('id');

      Swal.fire({
        title: '❗ آیا مطمئن هستید؟',
        text: 'این عمل قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('dr-panel-tickets.destroy', ':id') }}".replace(':id', id),
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              toastr.success('✅ تیکت با موفقیت حذف شد!');
              updateTicketList(response.tickets);
            },
            error: function () {
              toastr.error('❌ خطا در حذف تیکت!');
            }
          });
        }
      });
    });

    // 📌 تابع بروزرسانی لیست تیکت‌ها
    function updateTicketList(tickets) {
      const container = $('#ticket-list');
      container.empty();

      if (tickets.length === 0) {
        container.append('<tr><td colspan="5" class="text-center">هیچ تیکتی یافت نشد.</td></tr>');
      } else {
        tickets.forEach(ticket => {
          let statusBadge;
          if (ticket.status === 'open') {
            statusBadge = '<span class="badge badge-success">باز</span>';
          } else if (ticket.status === 'answered') {
            statusBadge = '<span class="badge badge-info">پاسخ داده شده</span>';
          } else if (ticket.status === 'pending') {
            statusBadge = '<span class="badge badge-warning">  درحال بررسی</span>';
          } else {
            statusBadge = '<span class="badge badge-danger">بسته</span>';
          }

          const row = `
                    <tr>
                        <td>${ticket.id}</td>
                        <td>${ticket.title}</td>
                        <td>${ticket.description.substring(0, 50)}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-light rounded-circle btn-sm delete-btn" data-id="${ticket.id}">
                                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="">
                            </button>
                            <button onclick="window.location.href='${getShowRoute(ticket.id)}'"
                                class="btn btn-light rounded-circle btn-sm view-btn">
                                <img src="{{ asset('dr-assets/icons/eye.svg') }}" alt="">
                            </button>
                        </td>
                    </tr>
                `;
          container.append(row);
        });
      }
    }

    // 📌 تابع برای ایجاد آدرس `show` برای هر تیکت
    function getShowRoute(ticketId) {
      return "{{ route('dr-panel-tickets.show', ':id') }}".replace(':id', ticketId);
    }

  });

</script>
@endsection
