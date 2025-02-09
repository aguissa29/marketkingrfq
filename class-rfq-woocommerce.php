<?php
class MarketKing_RFQ_WooCommerce {

    public function __construct() {
        add_action('wp_ajax_convert_rfq_to_order', [$this, 'convert_rfq_to_order']);
    }

    public function convert_rfq_to_order() {
        // Verificar permisos y nonce
        if (!current_user_can('seller') || !isset($_POST['rfq_id'])) {
            wp_send_json_error('Acceso denegado.');
        }

        $rfq_id = intval($_POST['rfq_id']);

        // Obtener datos de la cotización
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_requests';
        $rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rfq_id));

        // Validar que la cotización exista y esté aceptada
        if (!$rfq || $rfq->status !== 'accepted') {
            wp_send_json_error('La cotización no está aceptada o no existe.');
        }

        // Validar que los campos de precio e impuestos existan
        if (!isset($rfq->price) || !isset($rfq->tax)) {
            wp_send_json_error('Los detalles de precio e impuestos no están disponibles.');
        }

        // Calcular el total con impuestos
        $total = $rfq->price + ($rfq->price * $rfq->tax / 100);

        // Crear el pedido en WooCommerce
        $order = wc_create_order();
        $order->set_customer_id($rfq->buyer_id);
        $order->add_product(wc_get_product(0), 1, [
            'name' => $rfq->title,
            'total' => $total,
        ]);
        $order->set_status('pending'); // Estado inicial del pedido
        $order->save();

        // Notificar al comprador
        $buyer_email = get_userdata($rfq->buyer_id)->user_email;
        $subject = 'Tu pedido ha sido creado';
        $message = "Hola,\n\nTu cotización ha sido convertida en un pedido. Aquí están los detalles:\n\n";
        $message .= "- Título: {$rfq->title}\n";
        $message .= "- Precio: {$rfq->price}\n";
        $message .= "- Impuestos: {$rfq->tax}%\n";
        $message .= "- Total: " . $total . "\n\n";
        $message .= "Gracias por tu compra.";

        wp_mail($buyer_email, $subject, $message);

        // Actualizar el estado del RFQ a "completed"
        $wpdb->update(
            $table_name,
            ['status' => 'completed'],
            ['id' => $rfq_id]
        );

        wp_send_json_success('Pedido creado con éxito.');
    }
}