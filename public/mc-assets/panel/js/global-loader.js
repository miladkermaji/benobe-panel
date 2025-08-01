document.addEventListener("DOMContentLoaded", function () {
 // وقتی صفحه کاملاً لود شد، لودینگ را مخفی کن
 window.onload = function () {
  document.getElementById("global-loader").classList.add("hidden");
 };
});