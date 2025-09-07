<?php

namespace PlusinfoLab\Logstation\Services;

use League\Csv\Writer;
use PlusinfoLab\Logstation\Facades\Logstation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogExportService
{
    /**
     * Export logs to specified format.
     */
    public function export(array $filters, string $format = 'json'): StreamedResponse
    {
        $maxEntries = config('logstation.export.max_entries', 10000);
        $entries = Logstation::search($filters, 0)->take($maxEntries);

        return match ($format) {
            'csv' => $this->exportCsv($entries),
            'txt' => $this->exportTxt($entries),
            default => $this->exportJson($entries),
        };
    }

    /**
     * Export as JSON.
     */
    protected function exportJson($entries): StreamedResponse
    {
        return response()->streamDownload(function () use ($entries) {
            echo json_encode($entries->toArray(), JSON_PRETTY_PRINT);
        }, 'logstation-export-' . date('Y-m-d-His') . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Export as CSV.
     */
    protected function exportCsv($entries): StreamedResponse
    {
        return response()->streamDownload(function () use ($entries) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());

            // Header
            $csv->insertOne([
                'ID',
                'Date/Time',
                'Level',
                'Channel',
                'Message',
                'Exception',
                'User ID',
                'Request URL',
                'IP Address',
            ]);

            // Data
            foreach ($entries as $entry) {
                $csv->insertOne([
                    $entry->id,
                    $entry->created_at->toDateTimeString(),
                    $entry->level_name,
                    $entry->channel,
                    $entry->message,
                    $entry->exception_class ?? '',
                    $entry->user_id ?? '',
                    $entry->request_url ?? '',
                    $entry->request_ip ?? '',
                ]);
            }

            echo $csv->toString();
        }, 'logstation-export-' . date('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export as TXT.
     */
    protected function exportTxt($entries): StreamedResponse
    {
        return response()->streamDownload(function () use ($entries) {
            foreach ($entries as $entry) {
                echo str_repeat('=', 80) . PHP_EOL;
                echo "[{$entry->created_at}] {$entry->level_name}: {$entry->message}" . PHP_EOL;
                echo "Channel: {$entry->channel}" . PHP_EOL;

                if ($entry->exception_class) {
                    echo "Exception: {$entry->exception_class}" . PHP_EOL;
                    echo "File: {$entry->exception_file}:{$entry->exception_line}" . PHP_EOL;
                }

                if ($entry->request_url) {
                    echo "URL: {$entry->request_url}" . PHP_EOL;
                }

                if ($entry->user_id) {
                    echo "User: {$entry->user_id}" . PHP_EOL;
                }

                echo PHP_EOL;
            }
        }, 'logstation-export-' . date('Y-m-d-His') . '.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }
}
