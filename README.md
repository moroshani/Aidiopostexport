# Aidiopostexport

Aidiopostexport is a WordPress plugin that lets administrators export posts to DOCX or PDF files. It uses PHPWord and Dompdf libraries to create documents directly on your server, without external services.

## Installation
1. Upload the plugin files to the `/wp-content/plugins/` directory or install through the WordPress plugin screen.
2. Ensure Composer is available on your system. On Debian/Ubuntu you can install it with `apt-get install composer php-xml` which also enables the DOM extension needed by PHPWord and Dompdf.
3. Run `composer install` inside the plugin directory to fetch the PHP dependencies.
4. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
Once activated, edit any post in the WordPress admin and use the "Export DOCX" or "Export PDF" buttons in the sidebar to download the post as a document.
