<?php
class MarketKing_RFQ {

    public function __construct() {
        // Cargar funcionalidades del frontend
        add_action('init', [$this, 'load_frontend']);
        
        // Cargar funcionalidades del backend
        if (is_admin()) {
            add_action('init', [$this, 'load_admin']);
        }

        // Compatibilidad con HPOS
        add_action('init', [$this, 'load_hpos_compatibility']);
    }

    public function load_frontend() {
        require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-frontend.php';
        new MarketKing_RFQ_Frontend();
    }

    public function load_admin() {
        require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-admin.php';
        new MarketKing_RFQ_Admin();
    }

    public function load_hpos_compatibility() {
        require_once MARKETKING_RFQ_PATH . 'includes/class-rfq-hpos.php';
        new MarketKing_RFQ_HPOS();
    }
}