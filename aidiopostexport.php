<?php
/**
 * Plugin Name: Aidiopostexport
 * Description: Export WordPress posts to DOCX or PDF files.
 * Version: 0.1.0
 * Author: Codex
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Add export buttons on the post edit screen.
 */
function ape_add_export_meta_box() {
    add_meta_box(
        'ape-export-box',
        __('Export Post', 'aidiopostexport'),
        'ape_render_export_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ape_add_export_meta_box');

/**
 * Display buttons inside the meta box.
 *
 * @param WP_Post $post Current post object.
 */
function ape_render_export_meta_box($post) {
    $nonce = wp_create_nonce('ape_export_' . $post->ID);
    $base_url = admin_url('admin-post.php');

    $docx_url = add_query_arg(
        array(
            'action' => 'ape_export',
            'format' => 'docx',
            'post'   => $post->ID,
            '_wpnonce' => $nonce,
        ),
        $base_url
    );

    $pdf_url = add_query_arg(
        array(
            'action' => 'ape_export',
            'format' => 'pdf',
            'post'   => $post->ID,
            '_wpnonce' => $nonce,
        ),
        $base_url
    );

    echo '<p><a class="button" href="' . esc_url($docx_url) . '">' . esc_html__('Export DOCX', 'aidiopostexport') . '</a></p>';
    echo '<p><a class="button" href="' . esc_url($pdf_url) . '">' . esc_html__('Export PDF', 'aidiopostexport') . '</a></p>';
}

/**
 * Handle export requests.
 */
function ape_handle_export() {
    $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
    $format  = isset($_GET['format']) ? sanitize_key($_GET['format']) : 'docx';

    if (!$post_id || !in_array($format, array('docx', 'pdf'), true)) {
        wp_die(__('Invalid export request.', 'aidiopostexport'));
    }

    check_admin_referer('ape_export_' . $post_id);

    if (!current_user_can('edit_post', $post_id)) {
        wp_die(__('You do not have permission to export this post.', 'aidiopostexport'));
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_die(__('Post not found.', 'aidiopostexport'));
    }

    $content = apply_filters('the_content', $post->post_content);

    if ('docx' === $format) {
        ape_export_docx($post->post_title, $content, $post->post_name . '.docx');
    } else {
        ape_export_pdf($post->post_title, $content, $post->post_name . '.pdf');
    }

    exit;
}
add_action('admin_post_ape_export', 'ape_handle_export');

/**
 * Export content to DOCX using PHPWord.
 */
function ape_export_docx($title, $html, $filename) {
    $phpWord  = new \PhpOffice\PhpWord\PhpWord();
    $section  = $phpWord->addSection();
    $section->addTitle($title, 1);
    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html);

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');
}

/**
 * Export content to PDF using Dompdf.
 */
function ape_export_pdf($title, $html, $filename) {
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml('<h1>' . esc_html($title) . '</h1>' . $html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, array('Attachment' => true));
}

