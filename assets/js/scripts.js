document.addEventListener('DOMContentLoaded', function () {
    const rfqForm = document.querySelector('.rfq-form');
    if (rfqForm) {
        rfqForm.addEventListener('submit', function (e) {
            let isValid = true;

            const title = document.querySelector('#rfq_title');
            const quantity = document.querySelector('#rfq_quantity');

            // Limpiar mensajes de error previos
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            // Validar título
            if (!title.value.trim()) {
                isValid = false;
                showError(title, 'Por favor, ingresa un título para el producto.');
            }

            // Validar cantidad
            if (!quantity.value || quantity.value <= 0) {
                isValid = false;
                showError(quantity, 'Por favor, ingresa una cantidad válida.');
            }

            // Prevenir envío si hay errores
            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Función para mostrar mensajes de error
    function showError(element, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = 'red';
        errorDiv.textContent = message;
        element.parentNode.insertBefore(errorDiv, element.nextSibling);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;

            // Alternar clase 'active' en el encabezado
            header.classList.toggle('active');

            // Alternar visibilidad del contenido
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                header.setAttribute('aria-expanded', 'false');
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                header.setAttribute('aria-expanded', 'true');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('convert-to-order');
    if (button) {
        button.addEventListener('click', function () {
            const rfqId = this.getAttribute('data-rfq-id');

            // Confirmación antes de crear el pedido
            if (!confirm('¿Estás seguro de que deseas convertir esta cotización en un pedido?')) {
                return;
            }

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
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al procesar la solicitud.');
            });
        });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('rfq_attachments');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const files = Array.from(this.files);
            if (files.length > 5) {
                alert('No puedes subir más de 5 archivos.');
                this.value = ''; // Limpiar el campo
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Confirmación antes de exportar
    const exportButtons = document.querySelectorAll('.button');
    exportButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            const format = this.textContent.trim(); // 'PDF' o 'Excel'
            if (!confirm(`¿Estás seguro de que deseas exportar este RFQ como ${format}?`)) {
                event.preventDefault(); // Cancelar la acción si el usuario cancela
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.rfq-response-form');
    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            const response = this.querySelector('textarea').value.trim();
            if (!response) {
                alert('Por favor, escribe una respuesta antes de enviar.');
                event.preventDefault(); // Cancelar el envío
            }
        });
    });
});