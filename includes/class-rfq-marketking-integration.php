<?php
class MarketKing_RFQ_MarketKing_Integration {

    public function __construct() {
        // Hooks para integrar con MarketKing
        add_action('init', [$this, 'setup_marketking_integration']);
        add_filter('marketking_vendor_dashboard_tabs', [$this, 'add_rfq_tab_to_marketking']);
        add_action('marketking_vendor_dashboard_content', [$this, 'render_rfq_tab_content'], 10, 1);
    }

    /**
     * Configura la integración con MarketKing.
     */
    public function setup_marketking_integration() {
        // Verificar si MarketKing está activo
        if (!function_exists('marketking')) {
            add_action('admin_notices', [$this, 'marketking_missing_notice']);
            return;
        }

        // Asegurar que los roles de usuario sean compatibles
        $this->ensure_compatible_roles();
    }

    /**
     * Muestra una advertencia si MarketKing no está activo.
     */
    public function marketking_missing_notice() {
        echo '<div class="notice notice-error"><p>El plugin MarketKing no está activo. Por favor, actívalo para usar esta funcionalidad.</p></div>';
    }

    /**
     * Asegura que los roles de usuario sean compatibles con MarketKing.
     */
    private function ensure_compatible_roles() {
        // Ejemplo: Asegurar que los proveedores tengan acceso a RFQ
        $vendor_role = get_role('marketking_vendor');
        if ($vendor_role && !$vendor_role->has_cap('view_rfq')) {
            $vendor_role->add_cap('view_rfq');
        }
    }

    /**
     * Añade una pestaña de RFQ al dashboard de MarketKing.
     */
    public function add_rfq_tab_to_marketking($tabs) {
        $tabs['rfq'] = __('Cotizaciones', 'marketking-rfq');
        return $tabs;
    }

    /**
     * Renderiza el contenido de la pestaña "Cotizaciones" en el dashboard de MarketKing.
     */
    public function render_rfq_tab_content($current_tab) {
        if ($current_tab === 'rfq') {
            include MARKETKING_RFQ_PATH . 'templates/rfq-vendor-dashboard.php';
        }
    }

    /**
     * Envía notificaciones usando el sistema de MarketKing.
     */
    public static function send_marketking_notification($user_id, $message) {
        if (function_exists('marketking')) {
            marketking()->send_notification($user_id, $message);
        }
    }
}