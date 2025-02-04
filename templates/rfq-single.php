<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rfq_requests';

// Obtener detalles de la cotización
$rfq_id = intval($_GET['rfq_id']);
$rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rfq_id));

if ($rfq) {
    echo '<h2>Detalles de la cotización</h2>';
    echo '<strong>Título:</strong> ' . esc_html($rfq->title) . '<br>';
    echo '<strong>Descripción:</strong> ' . esc_html($rfq->description) . '<br>';
    echo '<strong>Cantidad:</strong> ' . esc_html($rfq->quantity) . '<br>';
    echo '<strong>Estado:</strong> ' . esc_html($rfq->status) . '<br>';

    // Mostrar archivos adjuntos
    if (!empty($rfq->attachment_urls)) {
        $attachment_urls = explode(',', $rfq->attachment_urls);
        echo '<strong>Archivos adjuntos:</strong><br>';
        foreach ($attachment_urls as $url) {
            echo '<a href="' . esc_url($url) . '" target="_blank">' . basename($url) . '</a><br>';
        }
    }

    // Mostrar el botón "Convertir en pedido" si el estado es "accepted"
    if ($rfq->status === 'accepted') {
        echo '<button id="convert-to-order" data-rfq-id="' . esc_attr($rfq->id) . '" class="button button-primary">Convertir en pedido</button>';
    }
} else {
    echo '<p>No se encontró la cotización.</p>';
}
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('convert-to-order');
    if (button) {
        button.addEventListener('click', function () {
            const rfqId = this.getAttribute('data-rfq-id');

            // Enviar solicitud AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=convert_rfq_to_order&rfq_id=' + rfqId,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido creado con éxito.');
                    location.reload();
                } else {
                    alert('Error: ' + data.data);
                }
            });
        });
    }
});
</script>