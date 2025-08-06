@if (Auth::guard('medical_center')->check())
  @livewire('mc.panel.layouts.partials.mc-sidebar')
@elseif (Auth::guard('doctor')->check() || Auth::guard('secretary')->check())
  @livewire('dr.panel.layouts.partials.dr-sidebar')
@endif
