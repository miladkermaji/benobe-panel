<script>
  document.addEventListener('livewire:initialized', () => {
    Livewire.on('otpSent', (data) => {
      const otpInput = document.querySelector('input[name="otp"]');
      if (otpInput) {
        otpInput.value = data.otpCode;
        // اعمال تغییرات به Livewire
        otpInput.dispatchEvent(new Event('input', {
          bubbles: true
        }));
      }
    });
  });
</script>
