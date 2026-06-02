<?php

class DocsController
{
    private string $docs_dir;

    /**
     * Ordered list of all docs included in the full PDF download.
     * When adding a new module, insert its entry here in the correct position.
     * Format: 'file_slug' => 'Chapter title shown in the PDF and TOC'
     */
    private const PRINT_ORDER = [
        'index'                 => 'Introducción',
        'dashboard'             => 'Dashboard',
        'admin_usuarios'        => 'Gestión de Usuarios',
        'admin_sucursales'      => 'Sucursales',
        'inventario_productos'  => 'Productos',
        'inventario_movimientos'=> 'Movimientos de Inventario',
        'inventario_categorias' => 'Categorías de Inventario',
        'inventario_unidades'   => 'Unidades de Medida',
        'inventario_motivos'    => 'Motivos de Movimiento',
        'compras_proveedores'   => 'Proveedores',
        'compras_compras'       => 'Compras',
        'egresos_categorias'    => 'Categorías de Egresos',
        'egresos_egresos'       => 'Egresos',
        'ventas_tipo_cambio'      => 'Tipo de cambio',
        'ventas_mi_caja'          => 'Mi caja',
        'ventas_historial_turnos' => 'Historial de turnos',
        'ventas_historial_ventas' => 'Historial de ventas',
        'ventas_pos'            => 'Punto de venta (POS)',
        'cfg_roles'             => 'Roles y Permisos',
        'cfg_perfil'            => 'Mi Perfil',
    ];

    public function __construct()
    {
        $this->docs_dir = __DIR__ . '/../_docs/';
    }

    public function get(string $name): void
    {
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower($name));
        if (!$name) $name = 'index';

        $file = $this->docs_dir . $name . '.md';

        if (!file_exists($file)) {
            echo json_encode(['status' => 'error', 'message' => 'Documento no encontrado.']);
            return;
        }

        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false);

        echo json_encode(['status' => 'ok', 'html' => $parsedown->text(file_get_contents($file))]);
    }

    public function downloadPdf(): void
    {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false);

        // ── Render sections ────────────────────────────────────────────────
        $sections = [];
        foreach (self::PRINT_ORDER as $slug => $title) {
            $file = $this->docs_dir . $slug . '.md';
            if (!file_exists($file)) continue;
            $sections[] = [
                'title' => $title,
                'html'  => $parsedown->text(file_get_contents($file)),
            ];
        }

        // ── Table of contents ──────────────────────────────────────────────
        $toc_rows = '';
        foreach ($sections as $i => $s) {
            $num       = $i + 1;
            $title     = htmlspecialchars($s['title']);
            $toc_rows .= "<tr><td width=\"30\" style=\"color:#1b8ea3;font-weight:bold;\">{$num}.</td><td>{$title}</td></tr>";
        }

        // ── Chapter bodies ─────────────────────────────────────────────────
        $body = '';
        foreach ($sections as $i => $s) {
            $break  = $i > 0 ? '<pagebreak />' : '';
            $body  .= $break . '<div class="section">' . $s['html'] . '</div>';
        }

        $date = date('d/m/Y');

        $css = '
            body        { font-family: dejavusans, sans-serif; font-size: 11pt; color: #222; line-height: 1.55; }
            h1          { font-size: 18pt; font-weight: bold; color: #0c1f28; border-bottom: 2pt solid #1b8ea3; padding-bottom: 4pt; margin: 0 0 12pt 0; }
            h2          { font-size: 13pt; font-weight: bold; color: #0c1f28; margin: 14pt 0 5pt 0; }
            h3          { font-size: 11pt; font-weight: bold; margin: 10pt 0 4pt 0; }
            p           { margin: 0 0 7pt 0; }
            ul, ol      { margin: 0 0 8pt 0; padding-left: 18pt; }
            li          { margin-bottom: 3pt; }
            strong      { font-weight: bold; }
            code        { font-family: dejavusansmono; font-size: 9pt; color: #c0392b; background: #f5f5f5; padding: 1pt 3pt; }
            table       { width: 100%; border-collapse: collapse; margin-bottom: 10pt; font-size: 10pt; }
            th          { background-color: #1b8ea3; color: #ffffff; padding: 5pt 7pt; font-weight: bold; text-align: left; }
            td          { padding: 4pt 7pt; border-bottom: 0.5pt solid #ddd; vertical-align: top; }
            .section    { margin-bottom: 0; }
            .cover-label{ font-size: 9pt; font-weight: bold; color: #1b8ea3; letter-spacing: 2pt; }
            .cover-title{ font-size: 30pt; font-weight: bold; color: #0c1f28; line-height: 1.15; }
            .cover-sub  { font-size: 13pt; color: #555; }
            .cover-meta { font-size: 9pt; color: #999; }
            .toc-title  { font-size: 16pt; font-weight: bold; color: #0c1f28; border-bottom: 2pt solid #1b8ea3; padding-bottom: 4pt; margin-bottom: 12pt; }
        ';

        $html = '
            <!-- Cover -->
            <div style="padding-top: 100mm; padding-left: 10mm; border-left: 6pt solid #1b8ea3; margin-left: 5mm;">
                <p class="cover-label">DOCUMENTACIÓN DEL SISTEMA</p>
                <br/>
                <p class="cover-title">Manual del<br/>Sistema</p>
                <br/>
                <p class="cover-sub">e-Sol &mdash; Guía de uso y referencia</p>
                <br/><br/>
                <p class="cover-meta">Generado el ' . $date . '</p>
            </div>

            <pagebreak />

            <!-- Table of contents -->
            <p class="toc-title">Contenido</p>
            <table>
                <tbody>' . $toc_rows . '</tbody>
            </table>

            <pagebreak />

            ' . $body;

        // ── mPDF output ────────────────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 20,
            'margin_right'  => 18,
            'margin_bottom' => 20,
            'margin_left'   => 18,
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->SetTitle('Manual del Sistema — e-Sol');
        $mpdf->SetAuthor('e-Sol');

        $mpdf->SetHTMLFooter('
            <table width="100%"><tr>
                <td style="font-size:8pt;color:#999;">Manual del Sistema — e-Sol</td>
                <td style="font-size:8pt;color:#999;text-align:right;">Página {PAGENO} de {nb}</td>
            </tr></table>
        ');

        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $filename = 'manual-esol-' . date('Y-m-d') . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }
}
