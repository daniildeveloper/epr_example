<?php

namespace App\Console\Commands;

use App\ParsingHelpers\ProposalHelper as H;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
            'Сумма ',
        ],
        'partner'           => [
            'откат',
        ],
        'clean_sum'         => [
            'Сумма очищенная',
        ],
    ];

    protected $types = ['БЕЗ НДС', 'С НДС'];

    protected $files = [
        './storage/app/public/2.csv',
        // './storage/app/public/3.csv',
        './storage/app/public/4.csv',
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
        $this->info('Parser begin ...');

        foreach ($this->files as $file) {
            $columns = $this->columns_setup($file);

            Log::info($columns);

            $this->line('Columns...');
            foreach ($columns as $key => $value) {
                $this->line("[$key] => $value");
            }

            $this->line('Parsing document $file...');
            $proposals = $this->parse_items($file, $columns);

            foreach ($proposals as $proposal) {
                $this->line('Allowing proposal #' . $proposal);
                H::allowProposal($proposal);
                H::closeProposalSuccessFly($proposal);
            }

            $this->info('Closing accounting period for file ' . $file);
            H::endAccountingPeriod($proposals);

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
            'Кол-во',
            'Клиент',
            'Марка',
            'Грунтовка л',
            'Эластификатор',
            'Толщина',
            'Скотч',
            'Сумма за г.п.э.с.',
            'Пропитка л',
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
                    $columns[$value] = $k;
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

        $first_line_index = '';
        // $this->line("First line index is " . $first_line_index);
        //
        $last_date = '';

        foreach ($records as $record) {
            $first_element = 0;

            while ($record[$first_element] === '') {
                $this->line('$first_element' . $first_element);
                $first_element++;

                // TODO: check for overrides and typos
                if ($record[$first_element] === $this->types[0] || $record[$first_element] === $this->types[1]) {
                    $first_line_index = $record[$first_element];
                }
            }

            if ($first_line_index != '') {
                $this->line('Proposal client ' . $record[$columns[$this->columns['client'][0]]]);

                if ($record[$columns[$this->columns['client'][0]]] != $this->columns['client'][0] && $record[$columns[$this->columns['client'][0]]] != '') {

                    // replace date strings t parse correctly
                    if (strpos($record[1], ',') !== false) {
                        $date = str_replace(',', '-', $record[1]);
                    }

                    if (strpos($record[1], '/') !== false) {
                        $date = str_replace('/', '-', $record[1]);
                    }
                    //

                    if ($date != '') {
                        $date = date('Y-m-d H:m:s', Carbon::parse($date)->timestamp);
                        $this->line($date);
                        $last_date = $date;
                    }

                    $this->line("Date created at $date");
                    $this->line('Тип налога ' . $first_line_index);
                    $proposal = [
                        'client'           => $record[$columns[$this->columns['client'][0]]],
                        'phone'            => '',
                        'created_at'       => $last_date,
                        'object'           => '',
                        'is_with_docs'     => $first_line_index === 'БЕЗ НДС' ? false : true,
                        'tax'              => $first_line_index === 'БЕЗ НДС' ? 0 : 12,
                        'client_deadline'  => $last_date,
                        'workers_deadline' => $last_date,
                        'partner_payment'  => $record[$columns[$this->columns['partner'][0]]],
                        'partner_notes'    => '',
                        'wares'            => [],
                    ];

                    // add base ware to proposal
                    if ($record[$columns[$this->columns['ware_name'][0]]] != '') {
                        $ware_count = (int) $record[$columns[$this->columns['wares_count'][0]]];
                        $clean_sum  = (int) $record[$columns[$this->columns['clean_sum'][0]]];
                        $new_ware   = [
                            'id'              => H::parse_ware_name($record[$columns[$this->columns['ware_name'][0]]]),
                            'price_per_count' => $clean_sum / $ware_count,
                            'count'           => $ware_count,
                            'created-at'      => $last_date,
                        ];
                        $proposal['wares'][] = $new_ware;
                    }
                    // add additional wares
                    Log::info("Create new proposal");
                    Log::info($proposal);

                    $results[] = H::store($proposal);
                }

            }

        }

        return $results;

    }
}
