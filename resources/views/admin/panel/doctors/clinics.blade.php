@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <livewire:admin.panel.doctors.doctor-clinics :doctorId="$doctor->id" />
      </div>
    </div>
  </div>
@endsection
