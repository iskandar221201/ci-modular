<?php

declare(strict_types=1);

namespace App\Libraries;

use Mpdf\Mpdf;

/**
 * BasePdfExporter — abstract base class for generating and streaming PDFs via mPDF.
 *
 * Usage:
 *   1. Create one subclass per module (one exporter per resource):
 *        class UserPdfExporter extends BasePdfExporter { ... }
 *   2. Implement buildHtml(array $data): string
 *   3. Call export($data, 'filename.pdf') from the controller
 *
 * Override $orientation and $paperSize in the subclass for a different layout.
 * Override __construct() in the subclass for advanced mPDF configuration
 * (watermark, letterhead, custom fonts, etc.) — always call parent::__construct() first.
 *
 * Template convention:
 *   - Store templates in app/Views/exports/
 *   - Templates must never extend any layout — plain HTML only for mPDF
 *   - Always use esc() for all user-controlled data in templates
 *
 * @throws \RuntimeException if PDF generation or streaming fails.
 *
 * @warning Data passed to buildHtml() becomes PDF content.
 *          Callers must not forward sensitive data (credentials, tokens, PII
 *          that should not appear in the document) to the exporter.
 */
abstract class BasePdfExporter
{
    protected Mpdf $pdf;

    /**
     * Page orientation. 'P' = Portrait, 'L' = Landscape.
     * Override in subclass when landscape is needed.
     */
    protected string $orientation = 'P';

    /**
     * Paper size. Default A4.
     * Override in subclass for a different size (e.g. 'Letter', 'A3').
     */
    protected string $paperSize = 'A4';

    /**
     * Initialise the mPDF instance with default configuration.
     * Override in subclass for advanced configuration — call parent::__construct() first.
     *
     * @throws \RuntimeException if mPDF fails to initialise.
     */
    public function __construct()
    {
        try {
            $this->pdf = new Mpdf([
                'orientation'   => $this->orientation,
                'format'        => $this->paperSize,
                'margin_top'    => 15,
                'margin_bottom' => 15,
                'margin_left'   => 15,
                'margin_right'  => 15,
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to initialise PDF engine: ' . $e->getMessage());
        }
    }

    /**
     * Build the HTML content for the PDF.
     * Implemented in the per-module subclass.
     *
     * @param array<int|string, mixed> $data Data to render into the PDF.
     * @return string Complete HTML — must not extend any layout.
     *
     * @warning Always use esc() for all user-controlled strings in the output HTML.
     */
    abstract protected function buildHtml(array $data): string;

    /**
     * Generate the PDF from data and stream it directly to the browser as a download.
     *
     * The caller must invoke exit after this method to prevent CI4 from sending
     * additional output after the PDF stream completes.
     *
     * Example in a controller:
     *   (new UserPdfExporter())->export($users, 'users-2026.pdf');
     *   exit;
     *
     * @param array<int|string, mixed> $data     Data forwarded to buildHtml().
     * @param string                   $filename Download filename (should end with .pdf).
     *
     * @throws \RuntimeException if PDF generation or streaming fails.
     */
    public function export(array $data, string $filename = 'export.pdf'): void
    {
        try {
            $this->pdf->WriteHTML($this->buildHtml($data));
            $this->pdf->Output($filename, 'D');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
