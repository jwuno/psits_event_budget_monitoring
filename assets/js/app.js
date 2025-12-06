document.addEventListener('DOMContentLoaded', function () {
    const profileIcon = document.getElementById('profileIcon');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const notifIcon = document.getElementById('notificationIcon');
    const notifDropdown = document.getElementById('notificationDropdown');

    if (dropdownMenu) dropdownMenu.style.display = 'none';
    if (notifDropdown) notifDropdown.style.display = 'none';

    if (profileIcon && dropdownMenu) {
        profileIcon.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.style.display =
                dropdownMenu.style.display === 'block' ? 'none' : 'block';
            if (notifDropdown) notifDropdown.style.display = 'none';
        });
    }

    if (notifIcon && notifDropdown) {
        notifIcon.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.style.display =
                notifDropdown.style.display === 'block' ? 'none' : 'block';
            if (dropdownMenu) dropdownMenu.style.display = 'none';
        });
    }

    document.addEventListener('click', function () {
        if (dropdownMenu) dropdownMenu.style.display = 'none';
        if (notifDropdown) notifDropdown.style.display = 'none';
    });
});
