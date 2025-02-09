<?php
class MarketKing_RFQ_History {

    public function __construct() {
        add_action('init', [$this, 'log_rfq_actions']);
        add_shortcode('rfq_history', [$this, 'render_rfq_history']);
    }

    /**
     * Registra una acción en el historial.
     */
    public static function log_action($rfq_id, $action, $details = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_history';

        $wpdb->insert(
            $table_name,
            [
                'rfq_id' => intval($rfq_id),
                'action' => sanitize_text_field($action),
                'user_id' => get_current_user_id(),
                'details' => sanitize_textarea_field($details),
            ]
        );
    }

    /**
     * Renderiza el historial de acciones de una RFQ.
     */
    public function render_rfq_history() {
        if (!isset($_GET['rfq_id'])) {
            echo '<p>ID de RFQ no proporcionado.</p>';
            return;
        }

        $rfq_id = intval($_GET['rfq_id']);
        $history = $this->get_filtered_history($rfq_id);

        if (!empty($history)) {
            echo '<h2>Historial de acciones</h2>';
            echo '<table class="rfq-history-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Acción</th>';
            echo '<th>Usuario</th>';
            echo '<th>Detalles</th>';
            echo '<th>Fecha</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($history as $entry) {
                echo '<tr>';
                echo '<td>' . esc_html($entry->action) . '</td>';
                echo '<td>' . esc_html(get_userdata($entry->user_id)->display_name) . '</td>';
                echo '<td>' . esc_html($entry->details) . '</td>';
                echo '<td>' . esc_html($entry->created_at) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No hay historial disponible para esta RFQ con los filtros aplicados.</p>';
        }
    }

    /**
     * Obtiene el historial filtrado de una RFQ.
     */
    private function get_filtered_history($rfq_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_history';

        // Obtener parámetros de filtrado
        $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

        // Construir la consulta base
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE rfq_id = %d", $rfq_id);

        // Aplicar filtros
        if (!empty($action_filter)) {
            $query .= $wpdb->prepare(" AND action = %s", $action_filter);
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= $wpdb->prepare(" AND created_at BETWEEN %s AND %s", $start_date, $end_date);
        }

        $query .= " ORDER BY created_at DESC";

        return $wpdb->get_results($query);
    }

    /**
     * Loggea automáticamente ciertas acciones.
     */
    public function log_rfq_actions() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['submit_rfq'])) {
                $rfq_id = isset($_POST['rfq_id']) ? intval($_POST['rfq_id']) : null;
                self::log_action($rfq_id, 'Nueva RFQ creada');
            } elseif (isset($_POST['submit_response'])) {
                $rfq_id = intval($_POST['rfq_id']);
                self::log_action($rfq_id, 'Respuesta enviada por proveedor', $_POST['response']);
            } elseif (isset($_POST['action']) && in_array($_POST['action'], ['accept', 'reject'])) {
                $rfq_id = intval($_POST['rfq_id']);
                $action = $_POST['action'] === 'accept' ? 'Respuesta aceptada' : 'Respuesta rechazada';
                self::log_action($rfq_id, $action);
            }
        }
    }
}