<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Modules\ModuleArtisanMaker;

class ArtisanMaker extends Command
{
    public const TYPE_MIGRATION = 'migration';
    public const TYPE_SEEDER = 'seeder';
    public const TYPE_MODEL = 'model';
    public const TYPE_LIVEWIRE = 'livewire';
    public const TYPE_CONTROLLER = 'controller';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make
                            {module_name}
                            {type : Type to make. Now available - migration}
                            {params_string}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make artisan commands for modules';

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
     * @return int
     */
    public function handle()
    {
        $module_name = $this->argument('module_name');
        $type = $this->argument('type');
        $params_string = $this->argument('params_string');

        $method_name = null;

        switch ($type) {
            case static::TYPE_MIGRATION:
                $method_name = 'makeMigration';
                break;
            case static::TYPE_SEEDER:
                $method_name = 'makeSeeder';
                break;
            case static::TYPE_MODEL:
                $method_name = 'makeModel';
                break;
            case static::TYPE_LIVEWIRE:
                $method_name = 'makeLivewire';
                break;
            case static::TYPE_CONTROLLER:
                $method_name = 'makeController';
                break;
        }

        if (empty($method_name)) {
            $this->error('Wrong type');
            return 0;
        }

        $response = ModuleArtisanMaker::$method_name($module_name, $params_string);

        if (empty($response['success']) || !empty($response['error'])) {
            $error_msg = $response['error'] ?? 'Something went wrong!';
            $this->error($error_msg);
        } else {
            $this->info('Make succesfully.');
        }

        return 0;
    }
}
