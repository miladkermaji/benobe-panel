<div>
 @if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
 @endif

 @if ($services->isEmpty())
  <p>هیچ خدمتی یافت نشد.</p>
 @else
  <table class="table table-striped">
   <thead>
    <tr>
     <th>شناسه</th>
     <th>نام خدمت</th>
     <th>مدت زمان</th>
     <th>قیمت</th>
     <th>تخفیف</th>
     <th>قیمت نهایی</th>
     <th>وضعیت</th>
     <th>عملیات</th>
     <th>زیر گروه</th>
    </tr>
   </thead>
   <tbody>
    @foreach ($services as $service)
     @include('dr.panel.dr-services.partials.service-row', ['service' => $service, 'level' => 0])
    @endforeach
   </tbody>
  </table>
 @endif
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.toggle-subservices').forEach(button => {
   button.addEventListener('click', function(event) {
    event.stopPropagation(); // جلوگیری از تداخل با دکمه‌های دیگر

    let serviceId = this.getAttribute('data-id');
    let subRows = document.querySelectorAll(`.subservice[data-parent='${serviceId}']`);
    let isOpened = this.classList.contains('opened');

    if (isOpened) {
     closeSubservices(serviceId);
     this.textContent = "مشاهده";
     this.classList.remove('opened');
    } else {
     subRows.forEach(row => row.style.display = 'table-row');
     this.textContent = "بستن";
     this.classList.add('opened');
    }
   });
  });

  function closeSubservices(parentId) {
   let subRows = document.querySelectorAll(`.subservice[data-parent='${parentId}']`);
   subRows.forEach(row => {
    row.style.display = 'none';
    let childButton = row.querySelector('.toggle-subservices');
    if (childButton) {
     childButton.textContent = "مشاهده";
     childButton.classList.remove('opened');
     closeSubservices(row.getAttribute('data-id'));
    }
   });
  }

  document.querySelectorAll('.delete-service').forEach(button => {
   button.addEventListener('click', function(event) {
    event.preventDefault();

    let deleteUrl = this.getAttribute('data-url');
    let token = '{{ csrf_token() }}';

    Swal.fire({
     title: 'آیا مطمئن هستید؟',
     text: "این عملیات قابل بازگشت نیست!",
     icon: 'warning',
     showCancelButton: true,
     confirmButtonColor: '#d33',
     cancelButtonColor: '#3085d6',
     confirmButtonText: 'بله، حذف شود!',
     cancelButtonText: 'لغو'
    }).then((result) => {
     if (result.isConfirmed) {
      fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
         'X-CSRF-TOKEN': token,
         'Accept': 'application/json'
        }
       })
       .then(response => response.json())
       .then(() => {
        Swal.fire('حذف شد!', 'خدمت با موفقیت حذف شد.', 'success')
         .then(() => {
          let rowToDelete = button.closest('tr');
          if (rowToDelete) rowToDelete.remove();
         });
       });
     }
    });
   });
  });
 });
 $(document).ready(function() {
  let dropdownOpen = false;
  let selectedClinic = localStorage.getItem('selectedClinic');
  let selectedClinicId = localStorage.getItem('selectedClinicId');
  if (selectedClinic && selectedClinicId) {
   $('.dropdown-label').text(selectedClinic);
   $('.option-card').each(function() {
    if ($(this).attr('data-id') === selectedClinicId) {
     $('.option-card').removeClass('card-active');
     $(this).addClass('card-active');
    }
   });
  } else {
   localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
   localStorage.setItem('selectedClinicId', 'default');
  }

  function checkInactiveClinics() {
   var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
   if (hasInactiveClinics) {
    $('.dropdown-trigger').addClass('warning');
   } else {
    $('.dropdown-trigger').removeClass('warning');
   }
  }
  checkInactiveClinics();

  $('.dropdown-trigger').on('click', function(event) {
   event.stopPropagation();
   dropdownOpen = !dropdownOpen;
   $(this).toggleClass('border border-primary');
   $('.my-dropdown-menu').toggleClass('d-none');
   setTimeout(() => {
    dropdownOpen = $('.my-dropdown-menu').is(':visible');
   }, 100);
  });

  $(document).on('click', function() {
   if (dropdownOpen) {
    $('.dropdown-trigger').removeClass('border border-primary');
    $('.my-dropdown-menu').addClass('d-none');
    dropdownOpen = false;
   }
  });

  $('.my-dropdown-menu').on('click', function(event) {
   event.stopPropagation();
  });

  $('.option-card').on('click', function() {
   var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
   var selectedId = $(this).attr('data-id');
   $('.option-card').removeClass('card-active');
   $(this).addClass('card-active');
   $('.dropdown-label').text(selectedText);

   localStorage.setItem('selectedClinic', selectedText);
   localStorage.setItem('selectedClinicId', selectedId);
   checkInactiveClinics();
   $('.dropdown-trigger').removeClass('border border-primary');
   $('.my-dropdown-menu').addClass('d-none');
   dropdownOpen = false;

   // ریلود صفحه با پارامتر جدید
   window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
  });
 });
</script>
