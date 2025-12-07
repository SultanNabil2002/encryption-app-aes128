document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebarToggle && sidebar) { 
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active'); 
        });
    }

    // Menutup sidebar jika klik di luar area sidebar pada mode mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 992 && sidebar && sidebarToggle && sidebar.classList.contains('active')) { 
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggler = sidebarToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggler) {
                sidebar.classList.remove('active');
            }
        }
    });
});