<?php
// Archivo: includes/class-rfq-multivendor.php

class MarketKing_RFQ_Multivendor {

    public function __construct() {
        // Añadir una nueva pestaña al dashboard de MarketKing
        add_filter('marketking_vendor_dashboard_tabs', [$this, 'add_vendor_rfq_tab']);
        add_action('marketking_vendor_dashboard_content', [$this, 'render_vendor_rfq_tab'], 10, 1);
    }

    /**
     * Añade una nueva pestaña al dashboard de MarketKing.
     */
    public function add_vendor_rfq_tab($tabs) {
        $tabs['rfq'] = __('Cotizaciones', 'marketking-rfq');
        return $tabs;
    }

    /**
     * Renderiza el contenido de la pestaña "Cotizaciones".
     */
    public function render_vendor_rfq_tab($current_tab) {
        if ($current_tab === 'rfq') {
            include MARKETKING_RFQ_PATH . 'templates/rfq-vendor-dashboard.php';
        }
    }

    /**
     * Obtiene las RFQ filtradas para un proveedor específico.
     */
    public static function get_filtered_rfqs_for_vendor($vendor_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_requests';

        // Obtener parámetros de filtrado
        $category_filter = isset($_GET['category_filter']) ? sanitize_text_field($_GET['category_filter']) : '';
        $min_quantity = isset($_GET['min_quantity']) ? intval($_GET['min_quantity']) : '';
        $delivery_date = isset($_GET['delivery_date']) ? sanitize_text_field($_GET['delivery_date']) : '';

        // Construir la consulta base
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE (status = 'open' OR status = 'accepted')");

        // Aplicar filtros
        if (!empty($category_filter)) {
            $query .= $wpdb->prepare(" AND category = %s", $category_filter);
        }
        if (!empty($min_quantity)) {
            $query .= $wpdb->prepare(" AND quantity >= %d", $min_quantity);
        }
        if (!empty($delivery_date)) {
            $query .= $wpdb->prepare(" AND delivery_date <= %s", $delivery_date);
        }

        return $wpdb->get_results($query);
    }
}