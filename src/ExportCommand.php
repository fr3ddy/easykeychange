<?php

namespace Fr3ddy\Easykeychange;

use Illuminate\Console\Command;
use Excel;
use Lang;
use App;

class ExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easykeychange:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translations to Excel';

    private $keys;

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
        $this->keys = array();
        
        //Search all trans('') in routes / resources / public / app
        if(is_dir(base_path().'/routes')){
            $routes  = \File::allFiles(base_path().'/routes');
        }else{
            $routes = array();
        }
        $resources = \File::allFiles(base_path().'/resources');
        $public = \File::allFiles(base_path().'/public');
        $app = \File::allFiles(base_path().'/app');
        $paths = array_merge($routes , $resources , $public , $app);
        
        $this->info('Started searching in files...');
        $bar = $this->output->createProgressBar(sizeof($paths));
        
        $trans = array();
        foreach($paths as $path){
            $file = file_get_contents($path);
            preg_match("/trans\('.+\..+?'\)/" , $file , $keys);
            $trans = array_merge($trans , $keys);
            preg_match('/trans\(".+\..+?"\)/' , $file , $keys);
            $trans = array_merge($trans , $keys);
            $bar->advance();
        }
        $this->info("");
        $this->info("Starting with file creation");
        foreach($trans as $t){
            $t2 = str_replace("trans('", "" , $t);
            $t3 = str_replace("')" , "", $t2);
            $t4 = str_replace('trans("', "" , $t3);
            $this->keys[] = str_replace('")' , "", $t4);
        }

        Excel::create('keys', function($excel) {

            // Set the title
            $excel->setTitle('Easykeychange Export File');

            // Chain the setters
            $excel->setCreator('Easykeychange')
                ->setCompany('Fr3ddyF');

            // Call them separately
            $excel->setDescription('This is the Easykeychange Export File.');

            $excel->sheet('All Keys', function($sheet) {
                $data = array();
                $data[] = array('Old Key' , 'New Key');
                foreach($this->keys as $key){
                    $data[] = array($key , $key);
                }

                $sheet->rows($data);

                // Freeze first row
                $sheet->freezeFirstRow();
                // Set auto size for sheet
                $sheet->setAutoSize(true);

                //Format first row
                $sheet->cells('A1:B1', function($cells) {

                    $cells->setFontSize(14);
                    $cells->setFontWeight('bold');
                
                });

            });

        })->store('xls', storage_path('easykeychange'));

        $this->info('Exported');
    }
}
