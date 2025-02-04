<?php
class MarketKing_RFQ_Comparison {

    public function __construct() {
        add_shortcode('rfq_comparison', [$this, 'render_comparison_table']);
    }

    /**
     * Renderiza la tabla de comparación de cotizaciones.
     */
    public function render_comparison_table() {
        ob_start();
        include MARKETKING_RFQ_PATH . 'templates/rfq-comparison.php';
        return ob_get_clean();
    }
}