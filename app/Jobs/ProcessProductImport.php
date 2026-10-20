<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];

    public function __construct(private string $csvPath) {}

    public function handle(): void
    {
        $import = new ProductsImport;

        Excel::import($import, $this->csvPath, 'local');

        Log::info("Import produk selesai: {$import->created} baru, {$import->updated} diperbarui, {$import->failures()->count()} row dilewati.");

        Storage::delete($this->csvPath);
    }
}