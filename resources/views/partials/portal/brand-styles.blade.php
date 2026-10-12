@php
    $primary = config('aggregpay.colors.primary', '#10149a');
    $secondary = config('aggregpay.colors.secondary', '#f89f4d');
    $primaryDark = '#0c1180';
@endphp
<style>
:root,
[data-bs-theme=light] {
    --portal-primary: {{ $primary }};
    --portal-primary-dark: {{ $primaryDark }};
    --portal-primary-rgb: 16, 20, 154;
    --portal-secondary: {{ $secondary }};
    --portal-secondary-rgb: 248, 159, 77;
    --bs-primary: {{ $primary }};
    --bs-secondary: {{ $secondary }};
    --bs-primary-rgb: 16, 20, 154;
    --bs-secondary-rgb: 248, 159, 77;
    --bs-link-color: {{ $primary }};
    --bs-link-hover-color: {{ $primaryDark }};
    --bs-header-dark-bg: linear-gradient(135deg, {{ $primary }} 0%, {{ $primaryDark }} 100%);
    --bs-menu-item-active-color: {{ $primary }};
    --bs-menu-item-active-border-color: {{ $primary }};
    --bs-sidebar-menu-item-active-color: {{ $primary }};
    --bs-pagination-active-bg: {{ $primary }};
    --bs-pagination-active-border-color: {{ $primary }};
    --bs-nav-pills-link-active-bg: {{ $primary }};
    --bs-progress-bar-bg: {{ $primary }};
}
body[data-topbar=dark] #page-topbar,
body[data-layout=horizontal][data-topbar=dark] #page-topbar,
body[data-topbar=dark] .dash-content,
body[data-topbar=dark] .dash-troggle-icon,
body[data-topbar=dark] .verti-dash-content {
    background: linear-gradient(135deg, {{ $primary }} 0%, {{ $primaryDark }} 100%) !important;
}
.dash-header-box {
    background: linear-gradient(135deg, {{ $primary }} 0%, {{ $primaryDark }} 100%) !important;
}
.dash-content,
.dash-content .page-title-box,
.dash-content .breadcrumb-item,
.dash-content .breadcrumb-item.active,
.dash-content .portal-stat-mini,
.dash-content .portal-stat-mini p,
.dash-content .portal-stat-mini h3 {
    color: #fff !important;
}
.portal-stat-mini .stat-label {
    color: rgba(255, 255, 255, 0.88) !important;
}
.portal-stat-mini .stat-value {
    color: #fff !important;
}
.portal-stat-mini .stat-sub {
    color: rgba(255, 255, 255, 0.75) !important;
}
.portal-period-tag {
    color: rgba(255, 255, 255, 0.92) !important;
}
.btn-primary,
.bg-primary,
.badge.bg-primary {
    background-color: {{ $primary }} !important;
    border-color: {{ $primary }} !important;
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
    background-color: {{ $primaryDark }} !important;
    border-color: {{ $primaryDark }} !important;
}
.text-primary { color: {{ $primary }} !important; }
.bg-primary-subtle { background-color: rgba(16, 20, 154, 0.12) !important; }
body[data-topbar=dark] .topnav .navbar-nav .nav-link.active,
body[data-topbar=dark] .topnav .navbar-nav .nav-item .nav-link.active,
body[data-topbar=dark] .topnav .navbar-nav .nav-link.active i {
    color: {{ $secondary }} !important;
}
.portal-stat-mini .stat-icon { color: {{ $secondary }} !important; }
.form-check-input:checked {
    background-color: {{ $primary }} !important;
    border-color: {{ $primary }} !important;
}
</style>
