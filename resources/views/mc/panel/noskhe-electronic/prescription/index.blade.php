@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/noskhe-electronic/prescription/prescription.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' نسخه های ثبت شده')

<div class="prescription-wrapper">
  <div class="top-prescription-d w-100 mt-3">
    <div class="d-flex justify-content-between w-100 gap-20 align-items-center p-3">
      <div class="w-100">

        <form action="" method="get" class="w-100">
          <input type="text" placeholder="جستجو بین نسخه ها" class="my-form-control col-12 w-100">
        </form>

      </div>
      <div class="">
        <button class="btn btn-light h-50" data-bs-toggle="modal" data-bs-target="#exampleModalCenterAddSick">

          <svg width="20px" height="20px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            class="sc-8d06d158-0 jrjHxO">
            <path fill="#292D32" fill-rule="evenodd"
              d="M10.943 1.35H18.6a2.756 2.756 0 0 1 2.75 2.75v2.2c0 .542-.165 1.1-.38 1.573-.216.475-.513.93-.84 1.258a.778.778 0 0 1-.033.031l-4.3 3.8a.766.766 0 0 1-.017.014c-.183.153-.371.417-.515.755a2.591 2.591 0 0 0-.215.97V19c0 .448-.144.917-.347 1.308-.2.382-.503.781-.906 1.027l-1.392.895-.012.008C10.631 23.323 8.15 22.13 8.15 19.9v-5.3c0-.222-.067-.523-.196-.846a3.302 3.302 0 0 0-.422-.762l-3.77-3.969a4.065 4.065 0 0 1-.784-1.179c-.187-.417-.328-.9-.328-1.344V4.2c0-1.59 1.212-2.85 2.75-2.85h5.518a.72.72 0 0 1 .025 0m-4.108 8.73 4.511-7.23H18.6c.686 0 1.25.564 1.25 1.25v2.2c0 .259-.085.6-.245.952a2.98 2.98 0 0 1-.52.803l-4.274 3.777c-.413.346-.721.829-.927 1.313a4.08 4.08 0 0 0-.334 1.555V19c0 .151-.056.382-.178.617-.124.239-.267.386-.358.44l-.02.012-1.393.896c-.838.51-1.951-.098-1.951-1.065v-5.3c0-.478-.133-.976-.304-1.403a4.729 4.729 0 0 0-.66-1.165.748.748 0 0 0-.042-.048l-1.81-1.905ZM5.768 8.955l-.924-.972-.014-.014a2.573 2.573 0 0 1-.483-.74c-.138-.306-.197-.573-.197-.73V4.2c0-.81.588-1.35 1.25-1.35h4.178z"
              clip-rule="evenodd"></path>
          </svg>
        </button>
        <div class="modal fade " id="exampleModalCenterAddSick" tabindex="-1" role="dialog"
          aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content border-radius-6">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"> فیلترها </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="">
                  <div class="border  d-flex justify-content-between p-2-5 border-radius-6" id="top-modal-div">
                    <div class="d-flex gap-4">
                      <div><span class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer">تامین
                          اجتماعی</span></div>
                      <div><span
                          class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer">سلامت</span>
                      </div>
                    </div>
                    <div><span
                        class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer border border-primary">سلامت</span>
                    </div>
                  </div>
                  <div
                    class="border d-flex justify-content-between gap-4 p-2-5 border-radius-6 turning_selectDate__MLRSb w-100 mt-2">
                    <button class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50  w-100 ">
                      <div class="d-flex justify-content-start w-100">
                        <label for="name" class="label-top-input"> شروع</label>

                        <input type="text" class="form-control h-50 text-center cursor-pointer"
                          placeholder="1404/05/08" data-jdp="" readonly="">
                      </div>


                    </button>
                    <button class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50  w-100 ">
                      <div class="d-flex justify-content-start w-100">
                        <label for="name" class="label-top-input"> پایان</label>

                        <input type="text" class="form-control h-50 text-center cursor-pointer"
                          placeholder="1404/05/08" data-jdp="" readonly="">
                      </div>


                    </button>
                  </div>
                  <div class="border d-flex justify-content-between p-2-5 border-radius-6 mt-3" id="top-modal-div">
                    <div class="d-flex gap-4">
                      <div><span class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer">آقا
                        </span></div>
                      <div><span
                          class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer">خانم</span>
                      </div>
                    </div>
                    <div><span
                        class="font-size-13 bg-light-blue p-2 border-radius-6 fw-bold cursor-pointer border border-primary">همه</span>
                    </div>
                  </div>
                  <div class="w-100 d-flex justify-content-between gap-4 mt-3">
                    <div class="w-100"><button class="btn my-btn-primary h-50 w-100">اعمال فیلتر</button></div>
                    <div class="w-100"><button class="btn btn-outline-info h-50 w-100"> حذف فیلتر</button></div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <div class="vakeshi-noskhe  p-3">
    <button class="btn btn-outline-secondary h-50" data-bs-toggle="modal" data-bs-target="#exampleModalCenterVakeshi">واکشی
      نسخه</button>
    <div class="modal fade " id="exampleModalCenterVakeshi" tabindex="-1" role="dialog"
      aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered " role="document">
        <div class="modal-content border-radius-6">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle"> واکشی نسخه </h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="">
              <div class="position-relative">
                <label for="" class="label-top-input-special">کد پیگیری</label>
                <input type="text" class="my-form-control-light h-50 border-radius-4 w-100 position-relative">
              </div>
              <div class="mt-3">
                <label for="" class="label-top-input-special">کدملی/کد اتباع بیمار</label>
                <input type="text" class="my-form-control-light h-50 border-radius-4 w-100 position-relative">
              </div>
              <div class="w-100 mt-3">
                <button class="w-100 btn my-btn-primary h-50 border-radius-4">جستجو</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="all-noskhe-list mt-2">
  <div class="table-responsive">
    <table class="table table-light">
      <tr>
        <th>نام بیمار </th>
        <th>کد توالی</th>
        <th>کد پیگیری</th>
        <th>بیمه</th>
        <th>زمان ثبت</th>
      </tr>

    </table>
    <div class="d-flex flex-column  align-items-center mt-5">
      <svg width="167" height="102" viewBox="0 0 167 102" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M46 85H129C132.866 85 136 81.866 136 78C136 74.134 132.866 71 129 71C129 71 123 67.866 123 64C123 60.134 126.952 57 131.826 57H142C145.866 57 149 53.866 149 50C149 46.134 145.866 43 142 43H120C123.866 43 127 39.866 127 36C127 32.134 123.866 29 120 29H160C163.866 29 167 25.866 167 22C167 18.134 163.866 15 160 15H62C58.134 15 55 18.134 55 22C55 25.866 58.134 29 62 29H22C18.134 29 15 32.134 15 36C15 39.866 18.134 43 22 43H47C50.866 43 54 46.134 54 50C54 53.866 50.866 57 47 57H7C3.13401 57 0 60.134 0 64C0 67.866 3.13401 71 7 71H46C42.134 71 39 74.134 39 78C39 81.866 42.134 85 46 85ZM153 50C153 53.866 156.134 57 160 57C163.866 57 167 53.866 167 50C167 46.134 163.866 43 160 43C156.134 43 153 46.134 153 50Z"
          fill="#bac7d469" fill-opacity="0.6"></path>
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M106.672 14L115.974 81.8427L116.809 88.649C117.079 90.8417 115.519 92.8375 113.327 93.1067L54.7665 100.297C52.5738 100.566 50.578 99.007 50.3088 96.8143L41.2931 23.3868C41.1584 22.2904 41.9381 21.2925 43.0344 21.1579C43.0413 21.1571 43.0483 21.1563 43.0552 21.1555L47.9136 20.6105M51.8421 20.1698L56.4291 19.6553L51.8421 20.1698Z"
          fill="white"></path>
        <path
          d="M107.663 13.8642C107.588 13.317 107.083 12.9343 106.536 13.0093C105.989 13.0843 105.606 13.5887 105.681 14.1358L107.663 13.8642ZM115.974 81.8427L116.966 81.7209L116.964 81.7069L115.974 81.8427ZM116.809 88.649L117.802 88.5272L116.809 88.649ZM113.327 93.1067L113.449 94.0993L113.327 93.1067ZM54.7665 100.297L54.8884 101.29L54.7665 100.297ZM50.3088 96.8143L51.3014 96.6924L50.3088 96.8143ZM41.2931 23.3868L42.2856 23.2649L41.2931 23.3868ZM43.0552 21.1555L43.1667 22.1493L43.0552 21.1555ZM48.025 21.6043C48.5739 21.5427 48.9689 21.0479 48.9073 20.499C48.8458 19.9502 48.3509 19.5552 47.8021 19.6167L48.025 21.6043ZM51.7306 19.1761C51.1818 19.2376 50.7868 19.7325 50.8483 20.2813C50.9099 20.8301 51.4047 21.2252 51.9536 21.1636L51.7306 19.1761ZM56.5406 20.6491C57.0895 20.5875 57.4845 20.0927 57.4229 19.5438C57.3613 18.995 56.8665 18.6 56.3177 18.6615L56.5406 20.6491ZM105.681 14.1358L114.983 81.9786L116.964 81.7069L107.663 13.8642L105.681 14.1358ZM114.981 81.9646L115.817 88.7709L117.802 88.5272L116.966 81.7209L114.981 81.9646ZM115.817 88.7709C116.019 90.4154 114.849 91.9122 113.205 92.1142L113.449 94.0993C116.189 93.7627 118.138 91.268 117.802 88.5272L115.817 88.7709ZM113.205 92.1142L54.6446 99.3045L54.8884 101.29L113.449 94.0993L113.205 92.1142ZM54.6446 99.3045C53.0001 99.5064 51.5033 98.3369 51.3014 96.6924L49.3163 96.9362C49.6528 99.677 52.1475 101.626 54.8884 101.29L54.6446 99.3045ZM51.3014 96.6924L42.2856 23.2649L40.3005 23.5086L49.3163 96.9362L51.3014 96.6924ZM42.2856 23.2649C42.2183 22.7167 42.6081 22.2178 43.1563 22.1505L42.9125 20.1654C41.268 20.3673 40.0986 21.8641 40.3005 23.5086L42.2856 23.2649ZM43.1563 22.1505C43.1597 22.1501 43.1632 22.1496 43.1667 22.1493L42.9437 20.1617C42.9333 20.1629 42.9229 20.1641 42.9125 20.1654L43.1563 22.1505ZM43.1667 22.1493L48.025 21.6043L47.8021 19.6167L42.9437 20.1617L43.1667 22.1493ZM51.9536 21.1636L56.5406 20.6491L56.3177 18.6615L51.7306 19.1761L51.9536 21.1636Z"
          fill="#DFE7EF"></path>
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M104.14 18.2692L112.56 79.7529L113.317 85.9212C113.561 87.9083 112.167 89.7146 110.203 89.9557L57.7613 96.3948C55.7977 96.6359 54.0081 95.2204 53.7641 93.2333L45.6141 26.8568C45.4795 25.7605 46.2591 24.7626 47.3555 24.628L53.8431 23.8314"
          fill="#E0E8EF" fill-opacity="0.4"></path>
        <path
          d="M60.6719 4C60.6719 2.34315 62.015 1 63.6719 1H109.229C110.024 1 110.787 1.31582 111.349 1.87802L112.054 1.17296L111.349 1.87802L124.793 15.3129C125.356 15.8756 125.672 16.639 125.672 17.4349V80C125.672 81.6569 124.329 83 122.672 83H63.6719C62.015 83 60.6719 81.6569 60.6719 80V4Z"
          fill="white" stroke="#DFE7EF" stroke-width="2"></path>
        <path d="M109.672 2.40283V14C109.672 15.6569 111.015 17 112.672 17H120.605" stroke="#DFE7EF" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round"></path>
        <path
          d="M71.6719 22.9527C71.1196 22.9527 70.6719 23.4004 70.6719 23.9527H71.6719V22.9527ZM97.6719 22.9527H71.6719V23.9527H97.6719V22.9527ZM98.6719 23.9527C98.6719 23.4004 98.2242 22.9527 97.6719 22.9527V23.9527H98.6719ZM97.6719 24.9527C98.2242 24.9527 98.6719 24.505 98.6719 23.9527H97.6719V24.9527ZM71.6719 24.9527H97.6719V23.9527H71.6719V24.9527ZM70.6719 23.9527C70.6719 24.505 71.1196 24.9527 71.6719 24.9527V23.9527H70.6719ZM71.6719 32.9529C71.1196 32.9529 70.6719 33.4007 70.6719 33.9529H71.6719V32.9529ZM114.672 32.9529H71.6719V33.9529H114.672V32.9529ZM115.672 33.9529C115.672 33.4007 115.224 32.9529 114.672 32.9529V33.9529H115.672ZM114.672 34.9529C115.224 34.9529 115.672 34.5052 115.672 33.9529H114.672V34.9529ZM71.6719 34.9529H114.672V33.9529H71.6719V34.9529ZM70.6719 33.9529C70.6719 34.5052 71.1196 34.9529 71.6719 34.9529V33.9529H70.6719ZM71.6719 43.9532V42.9532C71.1196 42.9532 70.6719 43.4009 70.6719 43.9532H71.6719ZM71.6719 43.9532H70.6719C70.6719 44.5055 71.1196 44.9532 71.6719 44.9532V43.9532ZM114.672 43.9532H71.6719V44.9532H114.672V43.9532ZM114.672 43.9532V44.9532C115.224 44.9532 115.672 44.5055 115.672 43.9532H114.672ZM114.672 43.9532H115.672C115.672 43.4009 115.224 42.9532 114.672 42.9532V43.9532ZM71.6719 43.9532H114.672V42.9532H71.6719V43.9532ZM71.6719 52.9534C71.1196 52.9534 70.6719 53.4011 70.6719 53.9534H71.6719V52.9534ZM114.672 52.9534H71.6719V53.9534H114.672V52.9534ZM115.672 53.9534C115.672 53.4011 115.224 52.9534 114.672 52.9534V53.9534H115.672ZM114.672 54.9534C115.224 54.9534 115.672 54.5057 115.672 53.9534H114.672V54.9534ZM71.6719 54.9534H114.672V53.9534H71.6719V54.9534ZM70.6719 53.9534C70.6719 54.5057 71.1196 54.9534 71.6719 54.9534V53.9534H70.6719ZM71.6719 63.9537V62.9537C71.1196 62.9537 70.6719 63.4014 70.6719 63.9537H71.6719ZM71.6719 63.9537H70.6719C70.6719 64.506 71.1196 64.9537 71.6719 64.9537V63.9537ZM97.6719 63.9537H71.6719V64.9537H97.6719V63.9537ZM97.6719 63.9537V64.9537C98.2242 64.9537 98.6719 64.506 98.6719 63.9537H97.6719ZM97.6719 63.9537H98.6719C98.6719 63.4014 98.2242 62.9537 97.6719 62.9537V63.9537ZM71.6719 63.9537H97.6719V62.9537H71.6719V63.9537Z"
          fill="#E0E8EF"></path>
      </svg><span class="mt-3">نسخه ای وجود ندارد</span>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/noskhe-electronic/prescription/prescription.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection
