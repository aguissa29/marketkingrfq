<?php
class MarketKing_RFQ_Database {

    public function __construct() {
        register_activation_hook(MARKETKING_RFQ_PATH . 'marketking-rfq.php', [$this, 'create_tables']);
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabla principal de RFQ
        $table_name = $wpdb->prefix . 'rfq_requests';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            buyer_id bigint(20) NOT NULL,
            title text NOT NULL,
            description text,
            quantity int(11) NOT NULL,
            delivery_date date,
            attachment_urls text,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            price decimal(10, 2) DEFAULT 0.00,
            tax decimal(10, 2) DEFAULT 0.00,
            vendor_response text,
            vendor_price decimal(10, 2),
            vendor_delivery_date date,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de historial de acciones
        $history_table = $wpdb->prefix . 'rfq_history';
        $sql_history = "CREATE TABLE $history_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            rfq_id mediumint(9) NOT NULL,
            action varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        dbDelta($sql_history);
    }
}