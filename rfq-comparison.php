<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rfq_requests';

// Obtener todas las cotizaciones aceptadas para el comprador actual
$user_id = get_current_user_id();
$rfqs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE buyer_id = %d AND status = 'accepted'", $user_id));

if (!empty($rfqs)) {
    echo '<h2 class="marketking-title">Comparación de Cotizaciones</h2>';
    echo '<table class="marketking-table rfq-comparison-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Título</th>';
    echo '<th>Precio</th>';
    echo '<th>Cantidad</th>';
    echo '<th>Plazo de entrega</th>';
    echo '<th>Proveedor</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($rfqs as $rfq) {
        echo '<tr>';
        echo '<td>' . esc_html($rfq->title) . '</td>';
        echo '<td>' . esc_html($rfq->price) . '</td>';
        echo '<td>' . esc_html($rfq->quantity) . '</td>';
        echo '<td>' . esc_html($rfq->delivery_date) . '</td>';
        echo '<td>' . esc_html(get_userdata($rfq->buyer_id)->display_name) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay cotizaciones disponibles para comparar.</p>';
}
?>