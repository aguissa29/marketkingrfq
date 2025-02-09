<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rfq_history';

if (!isset($_GET['rfq_id'])) {
    echo '<p>ID de RFQ no proporcionado.</p>';
    return;
}

$rfq_id = intval($_GET['rfq_id']);

// Obtener parámetros de filtrado
$action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

// Construir la consulta base
$query = $wpdb->prepare("SELECT * FROM $table_name WHERE rfq_id = %d", $rfq_id);

// Aplicar filtros
if (!empty($action_filter)) {
    $query .= $wpdb->prepare(" AND action = %s", $action_filter);
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= $wpdb->prepare(" AND created_at BETWEEN %s AND %s", $start_date, $end_date);
}

$query .= " ORDER BY created_at DESC";

$history = $wpdb->get_results($query);

// Formulario de filtrado
echo '<h2>Filtrar historial</h2>';
echo '<form method="get" class="rfq-history-filter">';
echo '<input type="hidden" name="rfq_id" value="' . esc_attr($rfq_id) . '">';
echo '<label for="action_filter">Filtrar por acción:</label>';
echo '<select name="action_filter" id="action_filter">';
echo '<option value="">Todas las acciones</option>';
echo '<option value="Nueva RFQ creada" ' . selected($action_filter, 'Nueva RFQ creada', false) . '>Nueva RFQ creada</option>';
echo '<option value="Respuesta enviada por proveedor" ' . selected($action_filter, 'Respuesta enviada por proveedor', false) . '>Respuesta enviada por proveedor</option>';
echo '<option value="Respuesta aceptada" ' . selected($action_filter, 'Respuesta aceptada', false) . '>Respuesta aceptada</option>';
echo '<option value="Respuesta rechazada" ' . selected($action_filter, 'Respuesta rechazada', false) . '>Respuesta rechazada</option>';
echo '</select>';

echo '<label for="start_date">Fecha inicial:</label>';
echo '<input type="date" name="start_date" id="start_date" value="' . esc_attr($start_date) . '">';

echo '<label for="end_date">Fecha final:</label>';
echo '<input type="date" name="end_date" id="end_date" value="' . esc_attr($end_date) . '">';

echo '<button type="submit">Filtrar</button>';
echo '</form>';

// Mostrar el historial
if (!empty($history)) {
    echo '<h2>Historial de acciones</h2>';
    echo '<table class="rfq-history-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Acción</th>';
    echo '<th>Usuario</th>';
    echo '<th>Detalles</th>';
    echo '<th>Fecha</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($history as $entry) {
        echo '<tr>';
        echo '<td>' . esc_html($entry->action) . '</td>';
        echo '<td>' . esc_html(get_userdata($entry->user_id)->display_name) . '</td>';
        echo '<td>' . esc_html($entry->details) . '</td>';
        echo '<td>' . esc_html($entry->created_at) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay historial disponible para esta RFQ con los filtros aplicados.</p>';
}
?>