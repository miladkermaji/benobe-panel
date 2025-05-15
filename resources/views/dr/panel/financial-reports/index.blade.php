@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/financial-reports/financial-reports.css') }}" rel="stylesheet" />
@endsection

@section('site-header')
  {{ 'به نوبه | گزارش مالی' }}
@endsection

@section('content')
@section('bread-crumb-title', 'گزارش مالی')

@livewire('dr.panel.financial.financial-report')

@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      let chartInstance = null;
      Livewire.on('updateChart', (event) => {
        const ctx = document.getElementById('financialChart').getContext('2d');
        if (chartInstance) {
          chartInstance.destroy();
        }
        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: event.labels,
            datasets: [{
              label: 'مبلغ تراکنش‌ها (ریال)',
              data: event.values,
              borderColor: '#2E86C1',
              backgroundColor: 'rgba(46, 134, 193, 0.2)',
              fill: true,
              tension: 0.4,
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: {
                display: true,
                title: {
                  display: true,
                  text: 'تاریخ'
                }
              },
              y: {
                display: true,
                title: {
                  display: true,
                  text: 'مبلغ (ریال)'
                }
              }
            }
          }
        });
      });
    });
    
  </script>
@endsection
@endsection
