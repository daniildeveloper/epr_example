<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use League\Csv\Statement;

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
        './storage/app/public/2.csv',
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

        foreach ($this->files as $file) {
            $columns = $this->columns_setup($file);

            $this->line('Columns...');
            foreach ($columns as $key => $value) {
                $this->line("[$key] => $value");
            }
        }

        // 2. Вызов сущности модели
        // 3. Генерация поставок
        // 4. калькуляция остатков
        // 6. Ввод товаров
    }

    private function columns_setup($file_path)
    {
        $csv     = Reader::createFromPath($file_path, 'r');
        $columns = [];

        $expected_columns_names = [
            'Клиент',
            'Кол-во',
            'Марка',
            'Грунтовка л',
            'Пропитка л',
            'Эластификатор',
            'Скотч',
            'Толщина',
            'Сумма за г.п.э.с.',
            'Сумма ',
            'откат',
            'бонус',
            'Сумма очищенная',

        ];

        $stmt = (new Statement())->offset(0)->limit(1);

        $records = $stmt->process($csv);

        foreach ($records as $record) {
            foreach ($record as $k => $value) {
                $this->line("[$k] => $value");
                if (in_array($value, $expected_columns_names)) {
                    $columns[$value] = array_search($value, $expected_columns_names);
                }
            }
        }

        return $columns;
    }
}
