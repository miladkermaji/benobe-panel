<script src="{{ asset('dr-assets/panel/js/js.js') }}"></script>




<script src="{{ asset('dr-assets/panel/js/popper.min.js') }}"></script>
{{-- <script src="{{ asset('dr-assets/panel/js/home/bootstrap/bootstrap.bootstrap.min.js') }}"></script> --}}

<script src="{{ asset('app-assets/js/select2/select2.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/chart/chart.js') }}"></script>
<script src="{{ asset('app-assets/js/timepicker/timepicker.js') }}"></script>
<script src="{{ asset('app-assets/js/all-custom.js') }}"></script>
<script type="text/javascript" src="{{ asset('dr-assets/panel/jalali-datepicker/jalalidatepicker.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('dr-assets/panel/jalali-datepicker/date.js') }}"></script>
{{-- tom select --}}
<script src="{{ asset('dr-assets/panel/js/tom-select.complete.min.js') }}"></script>
{{-- tom select --}}
<script src="{{ asset('dr-assets/panel/js/moment/jalali-moment.browser.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/global-loader.js') }}"></script>

<script>
  function initializeTimepicker() {

    const DOMElement = $(".timepicker-ui");

    const options = {
      clockType: '24h',
      theme: 'basic',
      mobile: true,
      enableScrollbar: true,
      disableTimeRangeValidation: false,
      autoClose: true
    };
    DOMElement.each(function() {

      const newTimepicker = new window.tui.TimepickerUI(this, options);
      newTimepicker.create();
    });
  }
</script>
