<?php
/*
Plugin Name: Muloreset
Description: Reset WordPress ke kondisi awal (plugin, tema, dan laman).
Version: 0.1.0
Author: Mus Mus
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tambah menu di Tools
 */
add_action('admin_menu', function () {
    add_management_page(
        'Muloreset',
        'Muloreset',
        'manage_options',
        'muloreset',
        'muloreset_page'
    );
});

/**
 * Halaman admin
 */
function muloreset_page() {
    if ( ! current_user_can('manage_options') ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Muloreset</h1>
        <p><strong>PERINGATAN:</strong> Ini akan menghapus plugin, tema, dan laman.</p>

        <form method="post">
            <?php wp_nonce_field('muloreset_action', 'muloreset_nonce'); ?>
            <input type="submit" name="muloreset_run" class="button button-danger"
                   value="RESET WORDPRESS">
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {

    if ( ! isset($_POST['muloreset_run']) ) return;
    if ( ! current_user_can('manage_options') ) return;

    if (
        ! isset($_POST['muloreset_nonce']) ||
        ! wp_verify_nonce($_POST['muloreset_nonce'], 'muloreset_action')
    ) {
        wp_die('Nonce tidak valid');
    }

    // 1. Nonaktifkan & hapus plugin (kecuali muloreset)
    $plugins = get_plugins();

    foreach ($plugins as $plugin_path => $plugin_data) {
        if ( strpos($plugin_path, 'muloreset.php') === false ) {
            deactivate_plugins($plugin_path);
            delete_plugins([$plugin_path]);
        }
    }

    // 2. Ganti ke tema default
    $default_theme = wp_get_theme('twentytwentyfour')->exists()
        ? 'twentytwentyfour'
        : wp_get_theme()->get_stylesheet();

    switch_theme($default_theme);

    // 3. Hapus semua laman (pages)
    $pages = get_posts([
        'post_type' => 'page',
        'numberposts' => -1,
        'post_status' => 'any'
    ]);

    foreach ($pages as $page) {
        wp_delete_post($page->ID, true);
    }

    wp_safe_redirect(admin_url('tools.php?page=muloreset&reset=success'));
    exit;
});
