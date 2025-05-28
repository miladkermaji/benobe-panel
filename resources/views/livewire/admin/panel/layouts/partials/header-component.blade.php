<div
  class="header d-flex item-center bg-white dark:bg-gray-800 width-100 custom-border-bottom padding-12-30 transition-colors duration-300">
  <div class="header__right d-flex flex-grow-1 item-center">
    <span class="bars"></span>
  </div>
  <div class="header__left d-flex flex-end item-center margin-top-2">
    <div class="d-flex notif-option mx-3">
      <div>
        <span class="bell-red-badge"></span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="24px" role="img">
          <path
            d="M12.02 2.91c-3.31 0-6 2.69-6 6v2.89c0 .61-.26 1.54-.57 2.06L4.3 15.77c-.71 1.18-.22 2.49 1.08 2.93 4.31 1.44 8.96 1.44 13.27 0 1.21-.4 1.74-1.83 1.08-2.93l-1.15-1.91c-.3-.52-.56-1.45-.56-2.06V8.91c0-3.3-2.7-6-6-6z"
            stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
          <path d="M13.87 3.2a6.754 6.754 0 00-3.7 0c.29-.74 1.01-1.26 1.85-1.26.84 0 1.56.52 1.85 1.26z"
            stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
            stroke-linejoin="round"></path>
          <path d="M15.02 19.06c0 1.65-1.35 3-3 3-.82 0-1.58-.34-2.12-.88a3.01 3.01 0 01-.88-2.12" stroke="currentColor"
            stroke-width="1.5" stroke-miterlimit="10"></path>
        </svg>
      </div>
    </div>

    <!-- دکمه دارک مود -->
    <button id="theme-toggle"
      class="mx-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 transition-colors duration-300">
      <!-- آیکون خورشید -->
      <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-gray-800 dark:text-gray-200" fill="currentColor"
        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z">
        </path>
      </svg>
      <!-- آیکون ماه -->
      <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-gray-800 dark:text-gray-200" fill="currentColor"
        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
      </svg>
    </button>

    <!-- تغییر لینک لاگ‌اوت به نویگیشن Livewire -->
    <a href="#" wire:click.prevent="$dispatch('navigateTo', { url: '{{ route('admin.auth.logout') }}' })"
      class="logout" title="خروج"></a>
  </div>
</div>

<script>
  // تنظیم تم اولیه
  if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
      '(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    document.getElementById('theme-toggle-dark-icon').classList.remove('hidden');
  } else {
    document.documentElement.classList.remove('dark');
    document.getElementById('theme-toggle-light-icon').classList.remove('hidden');
  }

  // تغییر تم با کلیک روی دکمه
  document.getElementById('theme-toggle').addEventListener('click', function() {
    // تغییر آیکون‌ها
    document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden');
    document.getElementById('theme-toggle-light-icon').classList.toggle('hidden');

    // تغییر تم
    if (localStorage.getItem('color-theme')) {
      if (localStorage.getItem('color-theme') === 'light') {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('color-theme', 'light');
      }
    } else {
      if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('color-theme', 'light');
      } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
      }
    }
  });
</script>
