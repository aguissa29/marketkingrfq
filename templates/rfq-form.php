<?php
// Verificar si el usuario está logueado
if (!is_user_logged_in()) {
    echo '<p>Debes iniciar sesión para crear una solicitud de cotización.</p>';
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

    echo '<p>Solicitud de cotización enviada con éxito.</p>';
}
?>

<form method="post" action="" enctype="multipart/form-data">
    <label for="rfq_title">Título del producto:</label>
    <input type="text" id="rfq_title" name="rfq_title" required>

    <label for="rfq_description">Descripción:</label>
    <textarea id="rfq_description" name="rfq_description"></textarea>

    <label for="rfq_quantity">Cantidad requerida:</label>
    <input type="number" id="rfq_quantity" name="rfq_quantity" required>

    <label for="rfq_delivery_date">Plazo de entrega:</label>
    <input type="date" id="rfq_delivery_date" name="rfq_delivery_date" required>

    <label for="rfq_price">Precio estimado:</label>
    <input type="number" step="0.01" id="rfq_price" name="rfq_price" required>

    <label for="rfq_tax">Impuestos (%):</label>
    <input type="number" step="0.01" id="rfq_tax" name="rfq_tax">

    <!-- Campo para múltiples archivos adjuntos -->
    <?php if (get_option('marketking_rfq_enable_attachments', 'yes') === 'yes'): ?>
        <label for="rfq_attachments">Adjuntar archivos:</label>
        <input type="file" id="rfq_attachments" name="rfq_attachments[]" multiple>
    <?php endif; ?>

    <button type="submit" name="submit_rfq">Enviar solicitud</button>
</form>