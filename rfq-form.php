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
$allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf'];
$max_file_size = 5 * 1024 * 1024; // 5 MB

foreach ($_FILES['rfq_attachments']['name'] as $key => $name) {
    if ($_FILES['rfq_attachments']['error'][$key] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['rfq_attachments']['type'][$key];
        $file_size = $_FILES['rfq_attachments']['size'][$key];

        if (!in_array($file_type, $allowed_mime_types)) {
            echo '<p>El archivo "' . esc_html($name) . '" no es un tipo válido.</p>';
            continue;
        }

        if ($file_size > $max_file_size) {
            echo '<p>El archivo "' . esc_html($name) . '" excede el tamaño máximo permitido.</p>';
            continue;
        }

        // Procesar el archivo...
    }
}
if (empty($title) || empty($description)) {
    echo '<p>' . __('Todos los campos obligatorios deben completarse.', 'marketkingrfq') . '</p>';
    return;
}

$delivery_date_timestamp = strtotime($delivery_date);
if (!$delivery_date_timestamp || $delivery_date_timestamp < time()) {
    echo '<p>' . __('La fecha de entrega debe ser una fecha válida en el futuro.', 'marketkingrfq') . '</p>';
    return;
}
if (empty($title) || empty($description)) {
    echo '<p>' . __('Todos los campos obligatorios deben completarse.', 'marketkingrfq') . '</p>';
    return;
}

$delivery_date_timestamp = strtotime($delivery_date);
if (!$delivery_date_timestamp || $delivery_date_timestamp < time()) {
    echo '<p>' . __('La fecha de entrega debe ser una fecha válida en el futuro.', 'marketkingrfq') . '</p>';
    return;
}
// En el formulario
wp_nonce_field('rfq_form_action', 'rfq_form_nonce');

// Al procesar el formulario
if (!isset($_POST['rfq_form_nonce']) || !wp_verify_nonce($_POST['rfq_form_nonce'], 'rfq_form_action')) {
    echo '<p>' . __('Error de seguridad: intento no autorizado.', 'marketkingrfq') . '</p>';
    return;
}
add_action('init', 'cargar_textdomain_marketkingrfq');
function cargar_textdomain_marketkingrfq() {
    load_plugin_textdomain('marketkingrfq', false, dirname(plugin_basename(__FILE__)) . '/languages/');
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
        <button type="submit" class="button button-primary">
          <?php _e('Enviar solicitud', 'marketkingrfq'); ?>
          </button>
    </form>
</div>