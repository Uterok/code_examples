<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Modules\NewModulesHandler;

class CreateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:create 
                            {name : Name of the new module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new module';

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
        $module_name = $this->argument('name');

        $response = NewModulesHandler::createNewModule($module_name);

        if (empty($response['success']) || !empty($response['error'])) {
            $error_msg = $response['error'] ?? 'Something went wrong!';
            $this->error($error_msg);
        } else {
            $this->info('Module ' . $module_name . ' generated succesfully.');
        }

        return 0;
    }
}
