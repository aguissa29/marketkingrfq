<?php
// Verificar si el usuario está logueado
if (!is_user_logged_in()) {
    echo '<p>' . __('Debes iniciar sesión para crear una solicitud de cotización.', 'marketkingrfq') . '</p>';
    return;
}

// Procesar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rfq'])) {
    $title = sanitize_text_field($_POST['rfq_title']);
    $description = sanitize_textarea_field($_POST['rfq_description']);
    $quantity = intval($_POST['rfq_quantity']);
    $delivery_date = sanitize_text_field($_POST['rfq_delivery_date']);
    $price = floatval($_POST['rfq_price']);
    $tax = floatval($_POST['rfq_tax']);

    // Manejar archivos adjuntos múltiples
    $attachment_urls = [];
    if (isset($_FILES['rfq_attachments']) && is_array($_FILES['rfq_attachments']['name'])) {
        $upload_dir = wp_upload_dir();
        foreach ($_FILES['rfq_attachments']['name'] as $key => $name) {
            if ($_FILES['rfq_attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $file_path = $upload_dir['path'] . '/' . basename($name);
                move_uploaded_file($_FILES['rfq_attachments']['tmp_name'][$key], $file_path);
                $attachment_urls[] = $upload_dir['url'] . '/' . basename($name);
            }
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
            'attachment_urls' => implode(',', $attachment_urls), // Guardar URLs separadas por comas
            'price' => $price,
            'tax' => $tax,
        ]
    );
    echo '<p>' . __('Solicitud de cotización enviada con éxito.', 'marketkingrfq') . '</p>';
}
?>

<div class="wrap">
    <h1><?php _e('Solicitar Cotización', 'marketkingrfq'); ?></h1>
    <p><?php _e('Completa el siguiente formulario para solicitar una cotización.', 'marketkingrfq'); ?></p>

    <form method="post" action="" enctype="multipart/form-data">
        <!-- Título del producto -->
        <label for="rfq_title"><?php _e('Título del producto:', 'marketkingrfq'); ?></label>
        <input type="text" id="rfq_title" name="rfq_title" required><br><br>

        <!-- Descripción -->
        <label for="rfq_description"><?php _e('Descripción:', 'marketkingrfq'); ?></label>
        <textarea id="rfq_description" name="rfq_description"></textarea><br><br>

        <!-- Cantidad requerida -->
        <label for="rfq_quantity"><?php _e('Cantidad requerida:', 'marketkingrfq'); ?></label>
        <input type="number" id="rfq_quantity" name="rfq_quantity" required><br><br>

        <!-- Plazo de entrega -->
        <label for="rfq_delivery_date"><?php _e('Plazo de entrega:', 'marketkingrfq'); ?></label>
        <input type="date" id="rfq_delivery_date" name="rfq_delivery_date" required><br><br>

        <!-- Precio estimado -->
        <label for="rfq_price"><?php _e('Precio estimado:', 'marketkingrfq'); ?></label>
        <input type="number" step="0.01" id="rfq_price" name="rfq_price" required><br><br>

        <!-- Impuestos -->
        <label for="rfq_tax"><?php _e('Impuestos (%):', 'marketkingrfq'); ?></label>
        <input type="number" step="0.01" id="rfq_tax" name="rfq_tax"><br><br>

        <!-- Adjuntar archivos -->
        <?php if (get_option('marketking_rfq_enable_attachments', 'yes') === 'yes'): ?>
            <label for="rfq_attachments"><?php _e('Adjuntar archivos:', 'marketkingrfq'); ?></label>
            <input type="file" id="rfq_attachments" name="rfq_attachments[]" multiple><br><br>
        <?php endif; ?>

        <!-- Botón de envío -->
        <button type="submit" name="submit_rfq" class="button button-primary">
            <?php _e('Enviar solicitud', 'marketkingrfq'); ?>
        </button>
    </form>
</div>