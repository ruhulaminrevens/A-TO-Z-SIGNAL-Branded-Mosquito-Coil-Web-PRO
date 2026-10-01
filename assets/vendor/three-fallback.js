(function(){
  if(window.THREE) return;
  window.THREE = null;
  document.documentElement.classList.add('three-fallback-active');
})();
