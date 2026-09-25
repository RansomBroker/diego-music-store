@vite('resources/css/app.css')

<!-- Leaflet.js Map Library -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Custom Sidebar Styles */
    .fi-sidebar {
        border-right: 1px solid rgb(226, 232, 240) !important;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .dark .fi-sidebar {
        border-right: 1px solid rgb(30, 41, 59) !important;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2) !important;
    }
    .fi-sidebar-header {
        border-bottom: 1px solid rgb(226, 232, 240) !important;
    }
    .dark .fi-sidebar-header {
        border-bottom: 1px solid rgb(30, 41, 59) !important;
    }

    /* Modern Backoffice Login / Simple Layout Styles */
    .fi-simple-layout {
        background-color: #f8fafc !important; /* slate-50 */
        position: relative !important;
        overflow-x: hidden !important;
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    .dark .fi-simple-layout {
        background-color: #0f172a !important; /* slate-900 */
    }

    /* Ambient Glow Behind Login Card */
    .fi-simple-layout::before {
        content: '';
        position: absolute;
        top: 5%;
        left: 50%;
        transform: translateX(-50%);
        width: 650px;
        height: 450px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.14) 0%, rgba(99, 102, 241, 0.06) 45%, transparent 70%);
        border-radius: 9999px;
        pointer-events: none;
        filter: blur(50px);
        z-index: 0;
    }
    .dark .fi-simple-layout::before {
        background: radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(99, 102, 241, 0.1) 45%, transparent 70%);
    }

    /* Login Main Container */
    .fi-simple-main-ctn {
        position: relative !important;
        z-index: 10 !important;
        padding: 1.5rem !important;
    }

    /* Login Card */
    .fi-simple-main {
        position: relative !important;
        border-radius: 1.75rem !important; /* rounded-3xl */
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02) !important;
        padding: 2.25rem 2.5rem !important;
        max-width: 28rem !important; /* max-w-md */
        margin: 1.5rem auto !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .dark .fi-simple-main {
        border-color: rgba(51, 65, 85, 0.8) !important; /* slate-700 */
        background-color: rgba(30, 41, 59, 0.92) !important; /* slate-800 */
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
    }

    /* Subtle Card Elevation on Hover */
    .fi-simple-main:hover {
        box-shadow: 0 25px 55px -10px rgba(59, 130, 246, 0.12), 0 0 0 1px rgba(59, 130, 246, 0.15) !important;
    }
    .dark .fi-simple-main:hover {
        box-shadow: 0 25px 55px -10px rgba(59, 130, 246, 0.22), 0 0 0 1px rgba(59, 130, 246, 0.25) !important;
    }

    /* Heading & Subheading Typography */
    .fi-simple-header-heading {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.025em !important;
        color: rgb(15 23 42) !important;
        margin-top: 0.25rem !important;
    }
    .dark .fi-simple-header-heading {
        color: rgb(255 255 255) !important;
    }

    .fi-simple-header-subheading {
        font-size: 0.875rem !important;
        color: rgb(100 116 139) !important; /* slate-500 */
        line-height: 1.45 !important;
        margin-top: 0.35rem !important;
    }
    .dark .fi-simple-header-subheading {
        color: rgb(148 163 184) !important; /* slate-400 */
    }

    /* Form Inputs Modern Polish */
    .fi-simple-main .fi-input-wrp {
        border-radius: 0.875rem !important;
        transition: all 0.2s ease !important;
    }
    .fi-simple-main .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
    }

    /* Primary Submit Button Modern Polish */
    .fi-simple-main .fi-btn-primary {
        border-radius: 0.875rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.35) !important;
        transition: all 0.25s ease !important;
    }
    .fi-simple-main .fi-btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        box-shadow: 0 6px 20px 0 rgba(37, 99, 235, 0.45) !important;
        transform: translateY(-1px);
    }
    .fi-simple-main .fi-btn-primary:active {
        transform: translateY(0);
    }
</style>
