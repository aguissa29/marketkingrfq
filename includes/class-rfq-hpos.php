<?php
class MarketKing_RFQ_HPOS {

    public function __construct() {
        // Verificar si HPOS está activo
        add_action('init', [$this, 'check_hpos']);
    }

    public function check_hpos() {
        if (function_exists('wc_get_container') && wc_get_container()->get(HPOS\OrdersTableDataStore::class)) {
            // HPOS está activo
            add_filter('woocommerce_order_data_store', [$this, 'use_hpos_data_store']);
        }
    }

    public function use_hpos_data_store($data_store) {
        return '\Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore';
    }
}