document.getElementById('profileImage').addEventListener('click', function(event) {
    event.stopPropagation();
    var dropdown = this.closest('.profile-dropdown');
    dropdown.classList.toggle('active');
});

window.addEventListener('click', function(event) {
    var dropdowns = document.querySelectorAll('.profile-dropdown');
    dropdowns.forEach(function(dropdown) {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
});