<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rfq_requests';

// Obtener el ID del comprador actual
$buyer_id = get_current_user_id();

// Filtrar las RFQ del comprador actual con respuestas de proveedores
$rfqs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE buyer_id = %d AND vendor_response IS NOT NULL", $buyer_id));

if (!empty($rfqs)) {
    echo '<h2>Respuestas de los proveedores</h2>';
    echo '<table class="rfq-responses-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Título</th>';
    echo '<th>Respuesta del proveedor</th>';
    echo '<th>Precio propuesto</th>';
    echo '<th>Plazo de entrega propuesto</th>';
    echo '<th>Acciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($rfqs as $rfq) {
        echo '<tr>';
        echo '<td>' . esc_html($rfq->title) . '</td>';
        echo '<td>' . esc_html($rfq->vendor_response) . '</td>';
        echo '<td>' . esc_html($rfq->vendor_price) . '</td>';
        echo '<td>' . esc_html($rfq->vendor_delivery_date) . '</td>';
        echo '<td>';
        if ($rfq->status !== 'accepted' && $rfq->status !== 'rejected') {
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="rfq_id" value="' . esc_attr($rfq->id) . '">';
            echo '<input type="hidden" name="action" value="accept">';
            echo '<button type="submit" class="button button-primary">Aceptar</button>';
            echo '</form>';
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="rfq_id" value="' . esc_attr($rfq->id) . '">';
            echo '<input type="hidden" name="action" value="reject">';
            echo '<button type="submit" class="button button-secondary">Rechazar</button>';
            echo '</form>';
        } else {
            echo '<strong>' . ucfirst($rfq->status) . '</strong>';
        }
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay respuestas de proveedores disponibles.</p>';
}

// Procesar la acción de aceptar o rechazar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfq_id']) && isset($_POST['action'])) {
    $rfq_id = intval($_POST['rfq_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'accept') {
        $wpdb->update(
            $table_name,
            ['status' => 'accepted'],
            ['id' => $rfq_id]
        );
        echo '<div class="notice notice-success"><p>Respuesta aceptada con éxito.</p></div>';
    } elseif ($action === 'reject') {
        $wpdb->update(
            $table_name,
            ['status' => 'rejected'],
            ['id' => $rfq_id]
        );
        echo '<div class="notice notice-error"><p>Respuesta rechazada con éxito.</p></div>';
    }
}
?>