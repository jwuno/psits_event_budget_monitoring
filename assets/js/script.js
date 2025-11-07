document.addEventListener("DOMContentLoaded", function () {
    // Profile dropdown functionality
    const profileIcon = document.getElementById("profileIcon");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (profileIcon && dropdownMenu) {
        profileIcon.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle("show");
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function () {
            dropdownMenu.classList.remove("show");
        });

        // Prevent dropdown from closing when clicking inside it
        dropdownMenu.addEventListener("click", function (e) {
            e.stopPropagation();
        });

        // Add logout functionality to dropdown item
        const logoutBtn = document.getElementById("logoutBtn"); // Add this ID to your logout button
        if (logoutBtn) {
            logoutBtn.addEventListener("click", function() {
                window.location.href = 'logout.php';
            });
        }
    }


    // Notifications dropdown functionality
    const notificationIcon = document.getElementById("notificationIcon");
    const notificationDropdown = document.getElementById("notificationDropdown");

    if (notificationIcon && notificationDropdown) {
        notificationIcon.addEventListener("click", function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle("show");
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function () {
            notificationDropdown.classList.remove("show");
        });

        // Prevent dropdown from closing when clicking inside it
        notificationDropdown.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }

    // Login form validation
    const loginForm = document.querySelector(".login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            const user = document.getElementById("username")?.value.trim();
            const pass = document.getElementById("password")?.value.trim();

            if (!user || !pass) {
                alert("Please fill in all fields.");
                e.preventDefault();
            }
        });
    }
});

// Mark all notifications as read
function markAllAsRead() {
    fetch('../mark_notifications_read.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove badge and refresh notifications
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.remove();
                }
                
                // Mark all notifications as read in the UI
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                });
                
                // Hide mark as read button
                const markReadBtn = document.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.style.display = 'none';
                }
            } else {
                alert('Error marking notifications as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error marking notifications as read');
        });
}