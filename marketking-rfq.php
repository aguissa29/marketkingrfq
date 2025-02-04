<?php
/**
 * Plugin Name: MarketKing RFQ
 * Description: A Request for Quotation (RFQ) module for MarketKing, compatible with HPOS.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: marketking-rfq
 * Stable tag: 1.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Definir constantes
define('MARKETKING_RFQ_VERSION', '1.0.0');
define('MARKETKING_RFQ_PATH', plugin_dir_path(__FILE__));
define('MARKETKING_RFQ_URL', plugin_dir_url(__FILE__));

// Incluir la clase de comparación
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-comparison.php';

// Inicializar la clase de comparación
new MarketKing_RFQ_Comparison();

// el shortcode es [rfq_comparison]

// Incluir la clase de soporte multivendor
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-multivendor.php';

// Inicializar la clase de soporte multivendor
new MarketKing_RFQ_Multivendor();

// Cargar archivos necesarios
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq.php';
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-admin.php';
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-frontend.php';
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-hpos.php';
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-database.php';
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-roles.php';

// Incluir la clase de integración con MarketKing
require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-marketking-integration.php';

// Inicializar la clase de integración con MarketKing
new MarketKing_RFQ_MarketKing_Integration();

// Inicializar el plugin
add_action('plugins_loaded', function () {
    new MarketKing_RFQ();
});
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('marketking-rfq-admin', MARKETKING_RFQ_URL . 'assets/css/styles.css');
});