/* Dropdown Base Styles */
.profile-menu, .notification-menu {
    position: relative;
    display: inline-block;
}

.profile-icon, .notification-icon {
    cursor: pointer;
    font-size: 24px;
    color: #333;
    padding: 8px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.profile-icon:hover, .notification-icon:hover {
    background: #e9ecef;
    border-color: #007bff;
    transform: scale(1.05);
}

.dropdown, .notification-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    min-width: 250px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-radius: 8px;
    z-index: 1000;
    padding: 15px;
    border: 1px solid #e0e0e0;
}

.dropdown.show, .notification-dropdown.show {
    display: block;
}

.user-name {
    font-weight: bold;
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.user-role {
    color: #7f8c8d;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.dropdown hr, .notification-dropdown hr {
    margin: 10px 0;
    border: none;
    border-top: 1px solid #ecf0f1;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #e74c3c;
    text-decoration: none;
    padding: 8px 12px;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.2s;
    font-size: 14px;
}

.logout-btn:hover {
    background-color: #ffeaea;
    color: #c0392b;
}