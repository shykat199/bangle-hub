document.addEventListener('DOMContentLoaded', function(){
  var t = document.getElementById('sidebarToggle');
  var s = document.querySelector('.sidebar, .left-sidebar, .app-sidebar');
  if (t && s){
    t.addEventListener('click', function(e){
      e.preventDefault();
      s.classList.toggle('show');
    });
  }
});
