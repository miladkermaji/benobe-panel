<!DOCTYPE html>
<html lang="en">

<head>
  @include('dr.panel.layouts.partials.head-tags')
  @yield('styles')
  <title>
    @if (Auth::guard('doctor')->check())
      پنل دکتر | به نوبه
    @elseif (Auth::guard('secretary')->check())
      پنل منشی | به نوبه
    @else
      به نوبه
    @endif
  </title>

  @include('dr.panel.my-tools.loader-btn')
  @livewireStyles

</head>

<body>
  @include('dr.panel.layouts.partials.sidebar')
  <div class="content">
    <x-global-loader />


    @include('dr.panel.layouts.partials.header')
    @yield('content')
    @livewireScripts
    {{--  @networkStatus --}}
</body>
@include('dr.panel.layouts.partials.scripts')
@yield('scripts')
<script>
  // اسکریپت نویگیشن Livewire
  Livewire.on('navigateTo', (event) => {
    window.Livewire.navigate(event.url);
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    Livewire.on('refreshDeleteButton', (data) => {
      document.getElementById('deleteButton').disabled = !data.hasSelectedRows;
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('show-toastr', (data) => {
      toastr.options = {
        progressBar: true,
        positionClass: "toast-top-right", // نمایش در سمت راست بالا
        timeOut: 3000 // زمان نمایش
      };

      if (data.type === 'success') {
        toastr.success(data.message);
      } else {
        toastr.warning(data.message);
      }
    });
  });
  jalaliDatepicker.startWatch({
    minDate: "attr",
    maxDate: "attr",
    showTodayBtn: true,
    showEmptyBtn: true,
    time: false,
    dateFormatter: function(unix) {
      return new Date(unix).toLocaleDateString('fa-IR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    }
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    Livewire.on('show-delete-confirmation', () => {
      Swal.fire({
        title: "آیا مطمئن هستید؟",
        text: "این عملیات غیرقابل بازگشت است!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "بله، حذف شود!",
        cancelButtonText: "لغو"
      }).then((result) => {
        if (result.isConfirmed) {
          Livewire.dispatch('doDeleteSelected');
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {
    $('.modal').on('show.bs.modal', function() {
      let modalDialog = $(this).find('.modal-dialog');
      modalDialog.css({
        transform: 'translateY(100%)',
        opacity: 0
      });
      setTimeout(function() {
        modalDialog.css({
          transition: 'transform 0.5s ease-out, opacity 0.5s ease-out',
          transform: 'translateY(0)',
          opacity: 1
        });
      }, 10);
    });

    $('.modal').on('hide.bs.modal', function() {
      let modalDialog = $(this).find('.modal-dialog');
      modalDialog.css({
        transition: 'transform 0.4s ease-in, opacity 0.4s ease-in',
        transform: 'translateY(100%)',
        opacity: 0
      });
    });

    $('.modal').on('hidden.bs.modal', function() {
      $(this).find('.modal-dialog').css({
        transform: '',
        opacity: '',
        transition: ''
      });
    });
  });
</script>

</html>
