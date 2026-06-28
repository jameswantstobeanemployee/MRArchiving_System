<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class PdfCompressionService
{
    /**
     * Compress a PDF using Ghostscript if the admin has enabled it.
     * Returns the path to use (compressed or original) and updates $size.
     *
     * @param  string  $pdfPath   Absolute path to the uploaded PDF
     * @param  int    &$size      File size reference — updated if compression runs
     * @return string             Path to use for storage (may be a temp file)
     */
    public function compress(string $pdfPath, int &$size): string
    {
        if (!SystemSetting::getValue('pdf_compression_enabled', false)) {
            return $pdfPath;
        }

        if (!$this->ghostscriptAvailable()) {
            Log::warning('PdfCompressionService: pdf_compression_enabled is ON but Ghostscript was not found.');
            return $pdfPath;
        }

        $quality      = SystemSetting::getValue('pdf_compression_quality', 'ebook');
        $allowedPresets = ['screen', 'ebook', 'printer', 'prepress'];
        if (!in_array($quality, $allowedPresets, true)) {
            $quality = 'ebook';
        }

        $outputPath = sys_get_temp_dir() . '/gs_compressed_' . uniqid() . '.pdf';

        $command = sprintf(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/%s '
            . '-dNOPAUSE -dQUIET -dBATCH '
            . '-sOutputFile=%s %s 2>&1',
            escapeshellarg($quality),
            escapeshellarg($outputPath),
            escapeshellarg($pdfPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath)) {
            Log::error('PdfCompressionService: Ghostscript failed.', [
                'exit_code' => $exitCode,
                'output'    => implode("\n", $output),
            ]);
            return $pdfPath; // fall back to original
        }

        $compressedSize = filesize($outputPath);

        // Only use compressed version if it's actually smaller
        if ($compressedSize >= $size) {
            Log::info('PdfCompressionService: Compressed file not smaller, using original.', [
                'original'   => $size,
                'compressed' => $compressedSize,
            ]);
            @unlink($outputPath);
            return $pdfPath;
        }

        Log::info('PdfCompressionService: Compression successful.', [
            'original_bytes'   => $size,
            'compressed_bytes' => $compressedSize,
            'saved_bytes'      => $size - $compressedSize,
        ]);

        $size = $compressedSize;
        return $outputPath;
    }

    public function ghostscriptAvailable(): bool
    {
        exec('which gs 2>/dev/null', $out, $code);
        return $code === 0 && !empty($out);
    }
}
