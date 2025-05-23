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
  </div>

  @livewireScripts
  <script>
    // ثبت رویداد show-toastr
    Livewire.on('show-toastr', (data) => {
      // اگر data یک آرایه است، از اولین عنصر استفاده کنید
      const toastrData = Array.isArray(data) ? data[0] : data;

      // اطمینان از عدم تکرار توستر
      toastr.clear(); // پاک کردن توسترهای قبلی
      if (toastrData.type === 'success') {
        toastr.success(toastrData.message);
      } else if (toastrData.type === 'warning') {
        toastr.warning(toastrData.message);
      } else if (toastrData.type === 'error') {
        toastr.error(toastrData.message);
      }
    });
  </script>

  @include('dr.panel.layouts.partials.scripts')
  @yield('scripts')

  <!-- سایر اسکریپت‌ها -->
  <script>
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
<script src="{{ asset('dr-assets/panel/js/clinic-selector.js') }}"></script>

</body>

</html>
