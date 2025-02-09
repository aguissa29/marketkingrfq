<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rfq_requests';

// Obtener RFQ según el rol del usuario
if (current_user_can('seller')) {
    // Proveedores ven todas las RFQ abiertas
    $rfqs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'open'");
} else {
    // Compradores ven sus propias RFQ
    $user_id = get_current_user_id();
    $rfqs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE buyer_id = %d", $user_id));
}

// Verificar si hay RFQ disponibles
if (!empty($rfqs)) {
    echo '<h2>Mis RFQ</h2>';
    echo '<ul>';
    foreach ($rfqs as $rfq) {
        echo '<li>';
        echo '<strong>Título:</strong> ' . esc_html($rfq->title) . '<br>';
        echo '<strong>Descripción:</strong> ' . esc_html($rfq->description) . '<br>';
        echo '<strong>Cantidad:</strong> ' . esc_html($rfq->quantity) . '<br>';
        echo '<strong>Plazo de entrega:</strong> ' . esc_html($rfq->delivery_date) . '<br>';

        // Mostrar archivos adjuntos si existen
        if (!empty($rfq->attachment_url)) {
            echo '<strong>Archivo adjunto:</strong> <a href="' . esc_url($rfq->attachment_url) . '" target="_blank">Ver archivo</a><br>';
        }

        // Estado editable
        echo '<strong>Estado:</strong> ';
        echo '<form method="post" style="display:inline;">';
        echo '<input type="hidden" name="rfq_id" value="' . esc_attr($rfq->id) . '">';
        echo '<select name="rfq_status" onchange="this.form.submit()">';
        echo '<option value="pending" ' . selected($rfq->status, 'pending', false) . '>Pendiente</option>';
        echo '<option value="in_review" ' . selected($rfq->status, 'in_review', false) . '>En revisión</option>';
        echo '<option value="accepted" ' . selected($rfq->status, 'accepted', false) . '>Aceptado</option>';
        echo '<option value="rejected" ' . selected($rfq->status, 'rejected', false) . '>Rechazado</option>';
        echo '<option value="completed" ' . selected($rfq->status, 'completed', false) . '>Completado</option>';
        echo '</select>';
        echo '</form><br>';

        // Botones de exportación
        echo '<strong>Exportar:</strong> ';
        echo '<a href="' . admin_url('admin-ajax.php?action=export_rfq&rfq_id=' . $rfq->id . '&format=pdf') . '" class="button">PDF</a>';
        echo '<a href="' . admin_url('admin-ajax.php?action=export_rfq&rfq_id=' . $rfq->id . '&format=excel') . '" class="button">Excel</a>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No hay solicitudes de cotización disponibles.</p>';
}
?>