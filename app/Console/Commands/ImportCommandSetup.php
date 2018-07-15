<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use League\Csv\Statement;
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
    protected $columns = [
        'client'            => [
            'Клиент',
        ],
        'wares_count'       => [
            'Кол-во',
        ],
        'ware_name'         => [
            'Марка',
        ],
        'date_created_at'   => [
            'БЕЗ НДС' => [
                'typo' => 'without_nds',
            ],
            'С НДС'   => [
                'typo' => 'with_nds',
            ],
        ],
        'ware_gruntovka'    => [
            'Грунтовка л',
        ],
        'ware_propitka'     => [
            'Пропитка л',
        ],
        'ware_elastik'      => [
            'Эластификатор',
        ],
        'ware_skotch'       => [
            'Скотч',
        ],
        'ware_skotch_width' => [
            'Толщина',
        ],
        'self_cost'         => [
            'Сумма за г.п.э.с.',
        ],
        'sum'               => [
            'Сумма',
        ],
        'partner'           => [
            'откат',
        ],
        'clean_sum'         => [
            'Сумма очищенная',
        ],
    ];

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

            Log::info('Show columns ...');
            Log::info($columns);

            $this->line('Columns...');
            foreach ($columns as $key => $value) {
                $this->line("[$key] => $value");
            }

            $this->line('Parsing document $file...');
            $this->parse_items($file, $columns);
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
            'Сумма',
            'откат',
            'бонус',
            'Сумма очищенная',
            'БЕЗ НДС',
        ];
        $stmt    = (new Statement())->offset(0)->limit(1);
        $records = $stmt->process($csv);

        // Записываем в каком ключе в файлке для импорта находится нужный массив
        foreach ($records as $record) {
            foreach ($record as $k => $value) {
                $this->line("[$k] => $value");
                if (in_array(trim($value), $expected_columns_names)) {
                    $columns[$value] = array_search($value, $expected_columns_names);
                }
            }
        }
        return $columns;
    }

    private function parse_items($file_path, $columns)
    {
        $results = [];
        $csv     = Reader::createFromPath($file_path, 'r');
        $stmt    = (new Statement())->offset(0);
        $records = $stmt->process($csv);

        $this->line("Records clients index: " . $columns[$this->columns['client'][0]]);

        foreach ($records as $record) {
            // $this->line($record);
            $proposal = [];
            // $this->line('Proposal client ' . $record[]);
            // Log::info($proposal);
        }

    }
}
