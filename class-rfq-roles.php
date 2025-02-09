<?php
class MarketKing_RFQ_Roles {

    public function __construct() {
        register_activation_hook(MARKETKING_RFQ_PATH . 'marketking-rfq.php', [$this, 'add_roles']);
        register_deactivation_hook(MARKETKING_RFQ_PATH . 'marketking-rfq.php', [$this, 'remove_roles']);
    }

    public function add_roles() {
        add_role('buyer', 'Comprador', ['read' => true]);
        add_role('seller', 'Proveedor', ['read' => true]);
    }

    public function remove_roles() {
        remove_role('buyer');
        remove_role('seller');
    }
}

new MarketKing_RFQ_Roles();