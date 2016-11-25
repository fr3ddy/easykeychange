<?php

namespace Fr3ddy\Easykeychange;

use Illuminate\Console\Command;
use Excel;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easykeychange:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Excel and overwrite existing data';

    private $paths;
    private $bar;
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
        //Search all trans('') in routes / resources / public / app
        if(is_dir(base_path().'/routes')){
            $routes  = \File::allFiles(base_path().'/routes');
        }else{
            $routes = array();
        }
        $resources = \File::allFiles(base_path().'/resources');
        $public = \File::allFiles(base_path().'/public');
        $app = \File::allFiles(base_path().'/app');
        $this->paths = array_merge($routes , $resources , $public , $app);

        Excel::load(storage_path('easykeychange').'/keys.xls' , function($reader){
            $sheet = $reader->first();

            $this->info('Started replacing in files...');
            $this->bar = $this->output->createProgressBar(sizeof($this->paths));
            
            $sheet->each(function($row){
                $keys = $row->toArray();

                foreach($this->paths as $path){
                    $file = file_get_contents($path);
                    if($file){
                        $count1 = 0;
                        $count2 = 0;
                        $file = str_replace("trans('".$keys['old_key']."')" , "trans('".$keys['new_key']."')" , $file , $count1);
                        $file = str_replace('trans("'.$keys["old_key"].'")' , 'trans("'.$keys["new_key"].'")' , $file , $count2);
                        if($count1 + $count2 > 0){
                            $handle = fopen($path,'w');
                            fwrite($handle , $file);
                            fclose($handle);
                        }
                    }
                }
                $this->bar->advance();
            });
        });

        $this->info('Everything replaced!');
    }
}
