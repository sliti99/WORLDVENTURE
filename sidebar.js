// sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    
    document.querySelectorAll('.sidebar-nav li').forEach(item => {
      item.classList.remove('active');
      const link = item.querySelector('a');
      if(link && link.getAttribute('href') === currentPage) {
        item.classList.add('active');
      }
    });
  });