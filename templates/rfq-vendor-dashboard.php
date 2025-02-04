<?php
global $wpdb;

// Obtener el ID del proveedor actual
$vendor_id = get_current_user_id();

// Usar la clase para obtener las RFQ filtradas
$rfqs = MarketKing_RFQ_Multivendor::get_filtered_rfqs_for_vendor($vendor_id);

// Formulario de filtrado
echo '<h2>Filtrar RFQ</h2>';
echo '<form method="get" class="rfq-filter-form">';
echo '<input type="hidden" name="tab" value="rfq">'; // Mantener la pestaña activa
echo '<label for="category_filter">Filtrar por categoría:</label>';
echo '<select name="category_filter" id="category_filter">';
echo '<option value="">Todas las categorías</option>';
echo '<option value="Electrónica" ' . selected($_GET['category_filter'] ?? '', 'Electrónica', false) . '>Electrónica</option>';
echo '<option value="Ropa" ' . selected($_GET['category_filter'] ?? '', 'Ropa', false) . '>Ropa</option>';
echo '<option value="Hogar" ' . selected($_GET['category_filter'] ?? '', 'Hogar', false) . '>Hogar</option>';
echo '</select>';

echo '<label for="min_quantity">Cantidad mínima:</label>';
echo '<input type="number" name="min_quantity" id="min_quantity" value="' . esc_attr($_GET['min_quantity'] ?? '') . '">';

echo '<label for="delivery_date">Plazo de entrega máximo:</label>';
echo '<input type="date" name="delivery_date" id="delivery_date" value="' . esc_attr($_GET['delivery_date'] ?? '') . '">';

echo '<button type="submit">Filtrar</button>';
echo '</form>';

// Mostrar la lista de RFQ
if (!empty($rfqs)) {
    echo '<h2 class="marketking-title">Mis Cotizaciones</h2>';
    echo '<table class="marketking-table rfq-vendor-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Título</th>';
    echo '<th>Descripción</th>';
    echo '<th>Cantidad</th>';
    echo '<th>Plazo de entrega</th>';
    echo '<th>Estado</th>';
    echo '<th>Responder</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($rfqs as $rfq) {
        echo '<tr>';
        echo '<td>' . esc_html($rfq->title) . '</td>';
        echo '<td>' . esc_html($rfq->description) . '</td>';
        echo '<td>' . esc_html($rfq->quantity) . '</td>';
        echo '<td>' . esc_html($rfq->delivery_date) . '</td>';
        echo '<td><span class="marketking-status marketking-status-' . esc_attr($rfq->status) . '">' . ucfirst($rfq->status) . '</span></td>';
        echo '<td>';
        echo '<form method="post" class="rfq-response-form">';
        echo '<input type="hidden" name="rfq_id" value="' . esc_attr($rfq->id) . '">';
        echo '<label for="response">Respuesta:</label>';
        echo '<textarea name="response" placeholder="Escribe tu respuesta aquí..." required></textarea>';
        echo '<label for="vendor_price">Precio propuesto:</label>';
        echo '<input type="number" step="0.01" name="vendor_price" placeholder="Precio" required>';
        echo '<label for="vendor_delivery_date">Plazo de entrega propuesto:</label>';
        echo '<input type="date" name="vendor_delivery_date" required>';
        echo '<button type="submit" name="submit_response">Enviar respuesta</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay cotizaciones disponibles con los filtros aplicados.</p>';
}

// Procesar la respuesta del proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    $rfq_id = intval($_POST['rfq_id']);
    $response = sanitize_textarea_field($_POST['response']);
    $vendor_price = floatval($_POST['vendor_price']);
    $vendor_delivery_date = sanitize_text_field($_POST['vendor_delivery_date']);

    // Guardar la respuesta en la base de datos
    $wpdb->update(
        $wpdb->prefix . 'rfq_requests',
        [
            'vendor_response' => $response,
            'vendor_price' => $vendor_price,
            'vendor_delivery_date' => $vendor_delivery_date,
        ],
        ['id' => $rfq_id]
    );

    echo '<div class="notice notice-success"><p>Respuesta enviada con éxito.</p></div>';
}

?>