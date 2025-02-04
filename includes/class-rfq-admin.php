<?php
class MarketKing_RFQ_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'MarketKing RFQ',
            'RFQ Manager',
            'manage_options',
            'marketking-rfq',
            [$this, 'render_admin_page'],
            'dashicons-cart',
            6
        );
        add_submenu_page(
            'marketking-rfq',
            'Configuración',
            'Configuración',
            'manage_options',
            'marketking-rfq-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_requests';

        // Consultas para obtener datos
        $total_rfqs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $open_rfqs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'open'");
        $closed_rfqs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'closed'");

        // Mostrar el gráfico
        echo '<h2>Estadísticas de RFQ</h2>';
        echo '<canvas id="rfq-stats-chart" width="400" height="200"></canvas>';

        // Script para renderizar el gráfico
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'; // Cargar Chart.js desde CDN
        echo '<script>
            const ctx = document.getElementById("rfq-stats-chart").getContext("2d");
            const rfqStatsChart = new Chart(ctx, {
                type: "bar", // Tipo de gráfico (barras)
                data: {
                    labels: ["Total", "Abiertos", "Cerrados"], // Etiquetas del eje X
                    datasets: [{
                        label: "RFQ", // Título del conjunto de datos
                        data: [' . $total_rfqs . ', ' . $open_rfqs . ', ' . $closed_rfqs . '], // Datos dinámicos
                        backgroundColor: ["#0073aa", "#28a745", "#dc3545"], // Colores de las barras
                    }]
                },
                options: {
                    responsive: true, // Hacer el gráfico responsive
                    plugins: {
                        legend: { display: false } // Ocultar leyenda
                    }
                }
            });
        </script>';
    }

    public function render_settings_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
            // Guardar todas las configuraciones
            update_option('marketking_rfq_email_subject', sanitize_text_field($_POST['email_subject']));
            update_option('marketking_rfq_email_message', sanitize_textarea_field($_POST['email_message']));
            update_option('marketking_rfq_max_response_days', intval($_POST['max_response_days']));
            update_option('marketking_rfq_enable_attachments', isset($_POST['enable_attachments']) ? 'yes' : 'no');
            update_option('marketking_rfq_enable_buyers', isset($_POST['enable_buyers']) ? 'yes' : 'no');
            update_option('marketking_rfq_restrict_sellers', isset($_POST['restrict_sellers']) ? 'yes' : 'no');
            update_option('marketking_rfq_enable_notifications', isset($_POST['enable_notifications']) ? 'yes' : 'no');

            echo '<div class="notice notice-success"><p>Configuración guardada con éxito.</p></div>';
        }
        // Añadir opción para habilitar/deshabilitar la integración con WooCommerce
        $enable_woocommerce = get_option('marketking_rfq_enable_woocommerce', 'yes');
   
        echo '<label>';
        echo '<input type="checkbox" name="enable_woocommerce" value="yes"' . checked($enable_woocommerce, 'yes', false) . '> Habilitar integración con WooCommerce';
        echo '</label><br>';

        // Obtener valores actuales de las opciones
        $email_subject = get_option('marketking_rfq_email_subject', 'Nueva solicitud de cotización disponible');
        $email_message = get_option('marketking_rfq_email_message', 'Hola {supplier_name},\n\nSe ha publicado una nueva solicitud de cotización.');
        $max_response_days = get_option('marketking_rfq_max_response_days', 7);
        $enable_attachments = get_option('marketking_rfq_enable_attachments', 'yes');
        $enable_buyers = get_option('marketking_rfq_enable_buyers', 'yes');
        $restrict_sellers = get_option('marketking_rfq_restrict_sellers', 'no');
        $notifications_enabled = get_option('marketking_rfq_enable_notifications', 'yes');

        echo '<h1>Configuración de RFQ</h1>';
        echo '<div class="nav-tab-wrapper">';
        echo '<a href="#tab-emails" class="nav-tab">Notificaciones por correo</a>';
        echo '<a href="#tab-permissions" class="nav-tab">Permisos</a>';
        echo '<a href="#tab-deadlines" class="nav-tab">Plazos</a>';
        echo '</div>';

        echo '<form method="post" action="">';

        // Pestaña: Notificaciones por correo
        echo '<div id="tab-emails" class="tab-content">';
        $this->render_email_settings($email_subject, $email_message);
        echo '</div>';

        // Pestaña: Permisos
        echo '<div id="tab-permissions" class="tab-content" style="display:none;">';
        $this->render_permission_settings($enable_buyers, $restrict_sellers);
        echo '</div>';

        // Pestaña: Plazos
        echo '<div id="tab-deadlines" class="tab-content" style="display:none;">';
        $this->render_deadline_settings($max_response_days, $enable_attachments);
        echo '</div>';

        echo '<button type="submit" name="save_settings" class="button button-primary">Guardar configuración</button>';
        echo '</form>';

        // Script para manejar las pestañas
        echo '<script>
            document.querySelectorAll(".nav-tab").forEach(tab => {
                tab.addEventListener("click", function(e) {
                    e.preventDefault();
                    document.querySelectorAll(".tab-content").forEach(content => content.style.display = "none");
                    document.querySelector(this.getAttribute("href")).style.display = "block";
                    document.querySelectorAll(".nav-tab").forEach(t => t.classList.remove("nav-tab-active"));
                    this.classList.add("nav-tab-active");
                });
            });
        </script>';
    }

    private function render_email_settings($email_subject, $email_message) {
        echo '<h2>Notificaciones por correo electrónico</h2>';
        echo '<label for="email_subject">Asunto del correo:</label>';
        echo '<input type="text" id="email_subject" name="email_subject" value="' . esc_attr($email_subject) . '" required><br>';
        echo '<label for="email_message">Contenido del correo:</label>';
        echo '<textarea id="email_message" name="email_message" rows="5" required>' . esc_textarea($email_message) . '</textarea><br>';
    }

    private function render_permission_settings($enable_buyers, $restrict_sellers) {
        echo '<h2>Permisos</h2>';
        echo '<label>';
        echo '<input type="checkbox" name="enable_buyers" value="yes"' . checked($enable_buyers, 'yes', false) . '> Permitir a compradores crear RFQ';
        echo '</label><br>';
        echo '<label>';
        echo '<input type="checkbox" name="restrict_sellers" value="yes"' . checked($restrict_sellers, 'yes', false) . '> Restringir respuesta a proveedores verificados';
        echo '</label><br>';
    }

    private function render_deadline_settings($max_response_days, $enable_attachments) {
        echo '<h2>Plazos</h2>';
        echo '<label for="max_response_days">Plazo máximo para respuestas (en días):</label>';
        echo '<input type="number" id="max_response_days" name="max_response_days" value="' . esc_attr($max_response_days) . '" min="1" required><br>';
        echo '<label>';
        echo '<input type="checkbox" name="enable_attachments" value="yes"' . checked($enable_attachments, 'yes', false) . '> Permitir archivos adjuntos en RFQ';
        echo '</label><br>';
    }
}