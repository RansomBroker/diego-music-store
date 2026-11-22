<script>
    try {
        if (! localStorage.getItem('bo_sidebar_default_collapsed_v2')) {
            localStorage.setItem('isOpenDesktop', 'false');
            localStorage.setItem('isOpen', 'false');
            localStorage.removeItem('_x_isOpenDesktop');
            localStorage.removeItem('_x_isOpen');
            localStorage.removeItem('bo_sidebar_default_collapsed_v1');
            localStorage.setItem('bo_sidebar_default_collapsed_v2', '1');
        }
    } catch (e) {
        // Ignore storage restrictions
    }
</script>

