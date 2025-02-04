<?php
class MarketKing_RFQ_Notifications {

    public function __construct() {
        add_action('wp_insert_post', [$this, 'notify_suppliers'], 10, 3);
    }

    public function notify_suppliers($post_id, $post, $update) {
        // Verificar si el post es un RFQ
        if ($post->post_type === 'rfq_request') {
            // Obtener proveedores relevantes
            $suppliers = get_users(['role' => 'seller']);

            // Obtener configuraciones personalizadas
            $subject = get_option('marketking_rfq_email_subject', 'Nueva solicitud de cotización disponible');
            $message_template = get_option('marketking_rfq_email_message', 'Hola {supplier_name},\n\nSe ha publicado una nueva solicitud de cotización.');
            $max_days = get_option('marketking_rfq_max_response_days', 7);
            $deadline = date('Y-m-d', strtotime("+$max_days days"));

            // Personalizar y enviar correos a cada proveedor
            foreach ($suppliers as $supplier) {
                // Reemplazar marcadores en el mensaje
                $message = str_replace('{supplier_name}', $supplier->display_name, $message_template);

                // Agregar información sobre el plazo máximo
                $message .= "\n\nPor favor, responde antes del $deadline.";

                // Enviar correo
                wp_mail($supplier->user_email, $subject, $message);
            }
        }
    }
}