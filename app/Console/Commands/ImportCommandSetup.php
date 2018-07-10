<?php

namespace App\Console\Commands;

use App\Models\Action;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportCommandSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integro:import';
    // protected $signature = 'integro:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer for integro from excel';

    /**
     * Columns
     * @var array
     */
    protected $columns = [];

    protected $files = [
        './storage/app/public/2.csv'
    ];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 1. Распознование колонок
        $this->line('Show columns...');
        $this->line('Reading file ' . $this->file);

        // 2. Вызов сущности модели
        // 3. Генерация поставок
        // 4. калькуляция остатков
        // 6. Ввод товаров
    }

    private function columns_setup() {}
}
