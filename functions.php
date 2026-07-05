<?php

defined('ABSPATH') || exit;

const YOGASTUDIO_BERGAMOT_VITE_SERVER = 'http://localhost:5173';
const YOGASTUDIO_BERGAMOT_VITE_ENTRY_MAIN = 'src/js/main.js';

function yogastudio_bergamot_asset_version($path) {
    $file = trailingslashit(get_stylesheet_directory()) . ltrim($path, '/');

    return file_exists($file) ? date('YmdGis', filemtime($file)) : null;
}

function yogastudio_bergamot_is_vite_dev() {
    if (!in_array(wp_get_environment_type(), ['local', 'development'], true)) {
        return false;
    }

    $response = wp_remote_get(YOGASTUDIO_BERGAMOT_VITE_SERVER . '/@vite/client', [
        'timeout' => 0.2,
    ]);

    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

function yogastudio_bergamot_vite_entries() {
    return [
        'main' => YOGASTUDIO_BERGAMOT_VITE_ENTRY_MAIN,
    ];
}

function yogastudio_bergamot_vite_manifest() {
    $manifest_path = get_stylesheet_directory() . '/assets/.vite/manifest.json';

    if (!file_exists($manifest_path)) {
        return [];
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);

    return is_array($manifest) ? $manifest : [];
}

function yogastudio_bergamot_enqueue_vite_dev_assets($entries) {
    wp_enqueue_script('vite-client', YOGASTUDIO_BERGAMOT_VITE_SERVER . '/@vite/client', [], null, true);

    foreach ($entries as $handle => $entry) {
        wp_enqueue_script(
            'vite-' . $handle,
            YOGASTUDIO_BERGAMOT_VITE_SERVER . '/' . ltrim($entry, '/'),
            ['vite-client'],
            null,
            true
        );
    }
}

function yogastudio_bergamot_enqueue_vite_build_assets($entries) {
    $manifest = yogastudio_bergamot_vite_manifest();

    foreach ($entries as $handle => $entry) {
        if (empty($manifest[$entry]['file'])) {
            continue;
        }

        $file = $manifest[$entry]['file'];

        wp_enqueue_script(
            'vite-' . $handle,
            trailingslashit(get_stylesheet_directory_uri()) . ltrim($file, '/'),
            [],
            yogastudio_bergamot_asset_version($file),
            true
        );
    }
}

add_action('wp_enqueue_scripts', function () {
    $entries = yogastudio_bergamot_vite_entries();

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', [], null);

    if (yogastudio_bergamot_is_vite_dev()) {
        yogastudio_bergamot_enqueue_vite_dev_assets($entries);
        return;
    }

    wp_enqueue_style(
        'main-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style'],
        yogastudio_bergamot_asset_version('style.css')
    );

    yogastudio_bergamot_enqueue_vite_build_assets($entries);
});

add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if ($handle === 'vite-client' || strpos($handle, 'vite-') === 0) {
        return '<script type="module" src="' . esc_url($src) . '"></script>' . "\n";
    }

    return $tag;
}, 10, 3);
