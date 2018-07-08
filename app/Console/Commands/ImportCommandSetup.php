<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportCommandSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer for integro from excel';

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
        // 2. Вызов сущности модели
        // 3. Генерация поставок
        // 4. калькуляция остатков
        // 6. Ввод товаров
    }
}
