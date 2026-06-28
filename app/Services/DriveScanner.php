<?php

namespace App\Services;

use App\Models\ExternalDrive;
use App\Models\ArchivedChart;

class DriveScanner
{
    public function scan(ExternalDrive $drive): array
    {
        $drivePath   = rtrim($drive->drive_path, '/\\');
        $archiveDir  = $drivePath . DIRECTORY_SEPARATOR . 'archives';
        $deletedDir  = $drivePath . DIRECTORY_SEPARATOR . 'deleted';

        $result = [
            'drive'           => $drive,
            'archive_dir'     => $archiveDir,
            'drive_accessible'=> is_dir($drivePath),
            'archive_accessible' => is_dir($archiveDir),
            'files_on_drive'  => [],
            'orphaned_files'  => [],   // on drive but no DB record
            'missing_files'   => [],   // in DB but not on drive
            'matched'         => [],   // both exist and match
            'deleted_files'   => [],   // files in /deleted folder
            'summary'         => [],
        ];

        if (!$result['drive_accessible']) {
            $result['summary'] = ['error' => "Drive path '{$drivePath}' is not accessible. Drive may be disconnected."];
            return $result;
        }

        // ── Scan physical files on drive ──────────────────────────────────────
        $filesOnDrive = [];
        if (is_dir($archiveDir)) {
            foreach (scandir($archiveDir) as $file) {
                if ($file === '.' || $file === '..') continue;
                $fullPath = $archiveDir . DIRECTORY_SEPARATOR . $file;
                if (is_file($fullPath)) {
                    $filesOnDrive[$file] = [
                        'filename'  => $file,
                        'full_path' => $fullPath,
                        'size'      => filesize($fullPath),
                        'modified'  => date('Y-m-d H:i:s', filemtime($fullPath)),
                    ];
                }
            }
        }

        // Scan deleted folder
        if (is_dir($deletedDir)) {
            foreach (scandir($deletedDir) as $file) {
                if ($file === '.' || $file === '..') continue;
                $fullPath = $deletedDir . DIRECTORY_SEPARATOR . $file;
                if (is_file($fullPath)) {
                    $result['deleted_files'][] = [
                        'filename'  => $file,
                        'full_path' => $fullPath,
                        'size'      => filesize($fullPath),
                        'modified'  => date('Y-m-d H:i:s', filemtime($fullPath)),
                    ];
                }
            }
        }

        $result['files_on_drive'] = $filesOnDrive;

        // ── Get all DB records with a digital_copy_path ───────────────────────
        $dbRecords = ArchivedChart::whereNotNull('digital_copy_path')
            ->with('patient')
            ->get();

        // Index DB records by their filename
        $dbByFilename = [];
        foreach ($dbRecords as $chart) {
            $filename = basename($chart->digital_copy_path);
            $dbByFilename[$filename] = $chart;
        }

        // ── Compare: files on drive vs DB ─────────────────────────────────────
        foreach ($filesOnDrive as $filename => $fileInfo) {
            if (isset($dbByFilename[$filename])) {
                $chart = $dbByFilename[$filename];
                $result['matched'][] = [
                    'filename'    => $filename,
                    'full_path'   => $fileInfo['full_path'],
                    'size'        => $fileInfo['size'],
                    'modified'    => $fileInfo['modified'],
                    'chart_id'    => $chart->id,
                    'case_number' => $chart->case_number,
                    'patient'     => $chart->patient->full_name,
                    'status'      => $chart->status,
                    'path_match'  => $chart->digital_copy_path === $fileInfo['full_path'],
                ];
            } else {
                // File exists on drive but no DB record
                $result['orphaned_files'][] = [
                    'filename' => $filename,
                    'full_path'=> $fileInfo['full_path'],
                    'size'     => $fileInfo['size'],
                    'modified' => $fileInfo['modified'],
                ];
            }
        }

        // ── Compare: DB records vs files on drive ─────────────────────────────
        foreach ($dbByFilename as $filename => $chart) {
            $expectedPath = $archiveDir . DIRECTORY_SEPARATOR . $filename;
            $actualPath   = $chart->digital_copy_path;

            // Check if file exists at its stored path OR at the expected path
            if (!file_exists($actualPath) && !file_exists($expectedPath)) {
                $result['missing_files'][] = [
                    'chart_id'    => $chart->id,
                    'case_number' => $chart->case_number,
                    'patient'     => $chart->patient->full_name,
                    'status'      => $chart->status,
                    'stored_path' => $actualPath,
                    'filename'    => $filename,
                ];
            }
        }

        // ── Summary ───────────────────────────────────────────────────────────
        $result['summary'] = [
            'total_files_on_drive' => count($filesOnDrive),
            'total_db_records'     => $dbRecords->count(),
            'matched'              => count($result['matched']),
            'orphaned_files'       => count($result['orphaned_files']),
            'missing_files'        => count($result['missing_files']),
            'deleted_files'        => count($result['deleted_files']),
        ];

        return $result;
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)   return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)      return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}