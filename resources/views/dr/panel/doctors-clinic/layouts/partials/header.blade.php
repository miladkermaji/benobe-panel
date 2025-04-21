<header class="bg-light text-dark p-3 my-shadow w-100 d-flex align-items-center">
    <div class="col-6">
        <a href="@yield('backUrl', route('dr-panel'))" class="btn btn-light">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none">
                <g id="Arrow / Chevron_Right_MD">
                    <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </g>
            </svg>
            <span class="font-weight-bold">بازگشت</span>
        </a>
    </div>
    <div class="col-6">
        <h5 class="font-weight-bold title-header">@yield('headerTitle', 'پنل دکتر')</h5>
    </div>
</header>