<?php
class MarketKing_RFQ_Frontend {

    public function __construct() {
        // Hooks para shortcodes
        add_shortcode('rfq_responses', [$this, 'render_rfq_responses']);
        add_shortcode('rfq_form', [$this, 'render_rfq_form']);

        // Hook para procesar la actualización de estado
        add_action('init', [$this, 'handle_rfq_status_update']);
    }

    /**
     * Renderiza la vista de respuestas de los proveedores.
     */
    public function render_rfq_responses() {
        ob_start();
        include MARKETKING_RFQ_PATH . 'templates/rfq-responses.php';
        return ob_get_clean();
    }

    /**
     * Procesa la actualización del estado de una RFQ.
     */
    public function handle_rfq_status_update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfq_status'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfq_requests';

            $rfq_id = intval($_POST['rfq_id']);
            $new_status = sanitize_text_field($_POST['rfq_status']);

            $wpdb->update(
                $table_name,
                ['status' => $new_status],
                ['id' => $rfq_id]
            );

            echo '<div class="notice notice-success"><p>Estado actualizado con éxito.</p></div>';
        }
    }

    /**
     * Renderiza el formulario para crear una nueva RFQ.
     */
    public function render_rfq_form() {
        ob_start();

        if (!current_user_can('buyer') && get_option('marketking_rfq_enable_buyers', 'yes') === 'no') {
            echo '<p>No tienes permiso para crear RFQ.</p>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rfq'])) {
            $errors = [];

            // Validaciones
            if (empty($_POST['rfq_title'])) {
                $errors[] = 'El título del producto es obligatorio.';
            }

            if (empty($_POST['rfq_quantity']) || intval($_POST['rfq_quantity']) <= 0) {
                $errors[] = 'La cantidad debe ser un número positivo.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<p style="color:red;">' . esc_html($error) . '</p>';
                }
                return;
            }

            // Procesar el formulario si no hay errores
            $title = sanitize_text_field($_POST['rfq_title']);
            $description = sanitize_textarea_field($_POST['rfq_description']);
            $quantity = intval($_POST['rfq_quantity']);
            $delivery_date = sanitize_text_field($_POST['rfq_delivery_date']);

            // Manejar el archivo adjunto
            $attachment_url = '';
            if (!empty($_FILES['rfq_attachment']['name'])) {
                $upload_dir = wp_upload_dir();
                $file_name = sanitize_file_name($_FILES['rfq_attachment']['name']);
                $file_path = $upload_dir['path'] . '/' . $file_name;

                if (move_uploaded_file($_FILES['rfq_attachment']['tmp_name'], $file_path)) {
                    $attachment_url = $upload_dir['url'] . '/' . $file_name;
                }
            }

            // Guardar en la base de datos
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfq_requests';
            $wpdb->insert(
                $table_name,
                [
                    'buyer_id' => get_current_user_id(),
                    'title' => $title,
                    'description' => $description,
                    'quantity' => $quantity,
                    'delivery_date' => $delivery_date,
                    'attachment_url' => $attachment_url,
                ]
            );

            echo '<p>Solicitud de cotización enviada con éxito.</p>';
        }

        include MARKETKING_RFQ_PATH . 'templates/rfq-form.php';
        return ob_get_clean();
    }
}