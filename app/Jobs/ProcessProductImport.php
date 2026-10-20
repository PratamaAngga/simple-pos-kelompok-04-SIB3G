<?php
namespace App\Jobs;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ImportError;
class ProcessProductImport implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public array $backoff = [10, 60];
    public function __construct(private string $csvPath) {}
    public function handle(): void
    {
        $lines = file(Storage::path($this->csvPath), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = array_map('str_getcsv', $lines);
        $header = array_shift($rows);
        $rowNumber = 1;
        foreach ($rows as $row) {
            $rowNumber++;
            if (count($row) !== count($header)) {
                ImportError::create([
                    'source_file' => basename($this->csvPath),
                    'row_number' => $rowNumber,
                    'message' => 'Jumlah kolom tidak sesuai dengan header CSV.',
                ]);

                continue;
            }
            $validator = Validator::make(array_combine($header, $row), [
                'name' => ['required', 'string'],
                'category_id' => ['required', 'exists:categories,id'],
                'price' => ['required', 'integer', 'min:0'],
            ]);
            if ($validator->fails()) {
                ImportError::create([
                    'source_file' => basename($this->csvPath),
                    'row_number' => $rowNumber,
                    'message' => implode(' ', $validator->errors()->all()),
                ]);

                continue;
            }
            $data = $validator->validated();
            Product::updateOrCreate(
                ['name' => $data['name'], 'category_id' => $data['category_id']],
                ['price' => $data['price']],
            );
        }
        Storage::delete($this->csvPath);
    }
}