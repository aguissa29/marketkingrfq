<?php
require_once MARKETKING_RFQ_PATH . 'vendor/autoload.php'; // Cargar PhpSpreadsheet

class MarketKing_RFQ_Export {

    public function __construct() {
        add_action('wp_ajax_export_rfq', [$this, 'export_rfq']);
    }

    /**
     * Exporta un RFQ a PDF o Excel.
     */
    public function export_rfq() {
        if (!isset($_GET['rfq_id']) || !isset($_GET['format'])) {
            wp_send_json_error('Parámetros incorrectos.');
        }

        $rfq_id = intval($_GET['rfq_id']);
        $format = sanitize_text_field($_GET['format']); // 'pdf' o 'excel'

        global $wpdb;
        $table_name = $wpdb->prefix . 'rfq_requests';
        $rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rfq_id));

        if (!$rfq) {
            wp_send_json_error('RFQ no encontrado.');
        }

        if ($format === 'pdf') {
            $this->generate_pdf($rfq);
        } elseif ($format === 'excel') {
            $this->generate_excel($rfq);
        } else {
            wp_send_json_error('Formato no válido.');
        }
    }

    /**
     * Genera un archivo PDF con los detalles del RFQ.
     */
    private function generate_pdf($rfq) {
        require_once MARKETKING_RFQ_PATH . 'vendor/tcpdf/tcpdf.php';

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $html = "<h1>Detalles de la solicitud de cotización</h1>";
        $html .= "<p><strong>Título:</strong> {$rfq->title}</p>";
        $html .= "<p><strong>Descripción:</strong> {$rfq->description}</p>";
        $html .= "<p><strong>Cantidad:</strong> {$rfq->quantity}</p>";
        $html .= "<p><strong>Plazo de entrega:</strong> {$rfq->delivery_date}</p>";
        $html .= "<p><strong>Estado:</strong> {$rfq->status}</p>";

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('rfq_' . $rfq->id . '.pdf', 'D'); // Descargar el archivo
    }

    /**
     * Genera un archivo Excel con los detalles del RFQ.
     */
    private function generate_excel($rfq) {
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Título');
        $sheet->setCellValue('B1', 'Descripción');
        $sheet->setCellValue('C1', 'Cantidad');
        $sheet->setCellValue('D1', 'Plazo de entrega');
        $sheet->setCellValue('E1', 'Estado');

        $sheet->setCellValue('A2', $rfq->title);
        $sheet->setCellValue('B2', $rfq->description);
        $sheet->setCellValue('C2', $rfq->quantity);
        $sheet->setCellValue('D2', $rfq->delivery_date);
        $sheet->setCellValue('E2', $rfq->status);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="rfq_' . $rfq->id . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}