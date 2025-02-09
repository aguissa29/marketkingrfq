<?php
// Obtener mensajes relacionados con un RFQ específico
$rfq_id = isset($_GET['rfq_id']) ? intval($_GET['rfq_id']) : 0;

if ($rfq_id) {
    global $wpdb;
    $messages_table = $wpdb->prefix . 'rfq_messages';
    $messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM $messages_table WHERE rfq_id = %d", $rfq_id));

    if ($messages) {
        echo '<ul>';
        foreach ($messages as $message) {
            echo '<li>';
            echo '<strong>De:</strong> ' . esc_html(get_userdata($message->sender_id)->display_name) . '<br>';
            echo '<strong>Mensaje:</strong> ' . esc_html($message->message) . '<br>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No hay mensajes disponibles.</p>';
    }

    // Formulario para enviar un nuevo mensaje
    ?>
    <form method="post" action="">
        <input type="hidden" name="rfq_id" value="<?php echo esc_attr($rfq_id); ?>">
        <label for="message">Nuevo mensaje:</label>
        <textarea id="message" name="message" required></textarea>
        <button type="submit" name="submit_message">Enviar</button>
    </form>
    <?php

    // Procesar el envío del mensaje
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
        $rfq_id = intval($_POST['rfq_id']);
        $message = sanitize_textarea_field($_POST['message']);
        $sender_id = get_current_user_id();

        $wpdb->insert(
            $messages_table,
            [
                'rfq_id' => $rfq_id,
                'sender_id' => $sender_id,
                'message' => $message,
                'date_sent' => current_time('mysql'),
            ]
        );

        echo '<p>Mensaje enviado con éxito.</p>';
    }
} else {
    echo '<p>ID de RFQ no válido.</p>';
}
?>