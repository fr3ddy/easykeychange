<?php

namespace Fr3ddy\Easykeychange;

use Illuminate\Console\Command;
use Excel;
use Lang;
use App;

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
    private $new_files;

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
        $old_locale = App::getLocale();

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

        $language_files = \File::allFiles(base_path().'/resources/lang');
        $this->new_files = array();

        $this->info("Starting with Language loading");

        foreach($language_files as $path){
            $split = explode('\\' , $path);
            $file = $split[sizeof($split)-1];
            $lang = $split[sizeof($split)-2];
            $split2 = explode('.' , $file);
            $file_name = $split2[0];
            
            $split3 = explode('-',$file_name);
            if(!isset($split3[1])){
                $this->info("Starting with ".$file_name);
                App::setLocale($lang);
                foreach(Lang::get($file_name) as $key1 => $value1){
                    if(is_array($value1)){
                        foreach($value1 as $key2 => $value2){
                            if(is_array($value2)){
                                foreach($value2 as $key3 => $value3){
                                    if(is_array($value3)){
                                        foreach($value3 as $key4 => $value4){
                                            if(is_array($value4)){
                                                foreach($value4 as $key5 => $value5){
                                                    if(is_array($value5)){
                                                        $this->info("To deep at ".$lang."/".$file_name);
                                                    }else{
                                                        $this->new_files[$file_name][$key1][$key2][$key3][$key4][$key5][$lang] = $value2;
                                                    }
                                                }
                                            }else{
                                                $this->new_files[$file_name][$key1][$key2][$key3][$key4][$lang] = $value2;
                                            }
                                        }
                                    }else{
                                        $this->new_files[$file_name][$key1][$key2][$key3][$lang] = $value3;
                                    }
                                }
                            }else{
                                $this->new_files[$file_name][$key1][$key2][$lang] = $value2;
                            }
                        }
                    }else{
                        $this->new_files[$file_name][$key1][$lang] = $value1;
                    }
                }
                $this->info("Finished with ".$file_name);
            }
        }

        $old_files = $this->new_files;

        $this->info("Old Language finished loading");

        Excel::load(storage_path('easykeychange').'/keys.xls' , function($reader){
            $reader->each(function($sheet){

                $this->info('Started replacing in files...');
                $this->bar = $this->output->createProgressBar(sizeof($this->paths)*count($this->new_files , COUNT_RECURSIVE));
                
                $sheet->each(function($row){
                    $keys = $row->toArray();
                    
                    //replace in files
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

                    //replace in lang array
                    $old_key = explode('.',$keys['old_key']);
                    $new_key = explode('.',$keys['new_key']);
                    $this->replaceInOutput($old_key[0] , $old_key[1] , isset($old_key[2]) ? $old_key[2] : null ,
                                                                       isset($old_key[3]) ? $old_key[3] : null ,
                                                                       isset($old_key[4]) ? $old_key[4] : null ,
                                                                       isset($old_key[5]) ? $old_key[5] : null ,
                                           $new_key[0] , $new_key[1] , isset($new_key[2]) ? $new_key[2] : null ,
                                                                       isset($new_key[3]) ? $new_key[3] : null ,
                                                                       isset($new_key[4]) ? $new_key[4] : null ,
                                                                       isset($new_key[5]) ? $new_key[5] : null );
                    $this->bar->advance();
                });
            });
        });

        //write array for new language files
        $new_files = array();
        foreach($this->new_files as $file => $rest){
            $this->info($file);
            if($old_files[$file] != $this->new_files[$file]){
                foreach($rest as $key1 => $value1){
                    foreach($value1 as $key2 => $value2){
                        if(is_array($value2)){
                            foreach($value2 as $key3 => $value3){
                                if(is_array($value3)){
                                    foreach($value3 as $key4 => $value4){
                                        if(is_array($value4)){
                                            foreach($value4 as $key5 => $value5){
                                                if(is_array($value5)){
                                                    $this->info("TOOOO DEEEEEP");
                                                }else{
                                                    //value5 = text , key5 = lang , key4 = key4 , key3 = key3 , key2 = key2 , key1 = key1
                                                    $new_files[$key5][$file][$key1][$key2][$key3][$key4] = $value5;
                                                }
                                            }
                                        }else{
                                            //value4 = text , key4 = lang , key3 = key3 , key2 = key2 , key1 = key1
                                            $new_files[$key4][$file][$key1][$key2][$key3] = $value4;
                                        }
                                    }
                                }else{
                                    //value3 = text , key3 = lang , key2 = key2 , key1 = key1
                                    $new_files[$key3][$file][$key1][$key2] = $value3;
                                }
                            }
                        }else{
                            //value2 = text , key2 = lang , key1 = key
                            $new_files[$key2][$file][$key1] = $value2;
                        }
                    }
                }
            }
        }

        //create new lang files now
        var_dump($new_files);
        foreach($new_files as $lang => $rest){
            foreach($rest as $filename => $array){
                $file = base_path().'/resources/lang/'.$lang.'/'.$filename.'.php';
                if(file_exists($file)){
                    copy($file , base_path().'/resources/lang/'.$lang.'/'.$filename.'-'.date("YmdHis").'.php');
                }
                $language_array = var_export($array,true);
                $file_content = '<?php

return '.$language_array.';';

                file_put_contents($file,$file_content);
            }
        }

        $this->info('Everything replaced!');
    }

    public function replaceInOutput($old_filename, $old_key_1, $old_key_2, $old_key_3, $old_key_4, $old_key_5, $new_filename, $new_key_1, $new_key_2, $new_key_3, $new_key_4, $new_key_5){
        $stop = false;
        if(isset($this->new_files[$old_filename]) && $old_key_1 != null && $old_key_2 != null && $old_key_3 != null && $old_key_4 != null && $old_key_5 != null){
            $old_value = $this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3][$old_key_4][$old_key_5];
            unset($this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3][$old_key_4][$old_key_5]);
        }elseif(isset($this->new_files[$old_filename]) && $old_key_1 != null && $old_key_2 != null && $old_key_3 != null && $old_key_4 != null){
            $old_value = $this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3][$old_key_4];
            unset($this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3][$old_key_4]);
        }elseif(isset($this->new_files[$old_filename]) && $old_key_1 != null && $old_key_2 != null && $old_key_3 != null ){
            $old_value = $this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3];
            unset($this->new_files[$old_filename][$old_key_1][$old_key_2][$old_key_3]);
        }elseif(isset($this->new_files[$old_filename]) && $old_key_1 != null && $old_key_2 != null){
            $old_value = $this->new_files[$old_filename][$old_key_1][$old_key_2];
            unset($this->new_files[$old_filename][$old_key_1][$old_key_2]);
        }else{
            if(isset($this->new_files[$old_filename]) && isset($this->new_files[$old_filename][$old_key_1])){
                $old_value = $this->new_files[$old_filename][$old_key_1];
                unset($this->new_files[$old_filename][$old_key_1]);
            }else{
                $stop = true;
            }
        }
        if(!$stop){
            
            if($new_key_2 != null && $new_key_3 != null && $new_key_4 != null && $new_key_5 != null){
                $this->new_files[$new_filename][$new_key_1][$new_key_2][$new_key_3][$new_key_4][$new_key_5] = $old_value;
            }elseif($new_key_2 != null && $new_key_3 != null && $new_key_4 != null){
                $this->new_files[$new_filename][$new_key_1][$new_key_2][$new_key_3][$new_key_4] = $old_value;
            }elseif($new_key_2 != null && $new_key_3 != null ){
                $this->new_files[$new_filename][$new_key_1][$new_key_2][$new_key_3] = $old_value;
            }elseif($new_key_2 != null){
                $this->new_files[$new_filename][$new_key_1][$new_key_2] = $old_value;
            }else{
                $this->new_files[$new_filename][$new_key_1] = $old_value;
            }
        }else{
            $info = "No translation found for: ";
            if($old_filename != null) $info .= $old_filename;
            if($old_key_1 != null) $info .= '.'.$old_key_1;
            if($old_key_2 != null) $info .= '.'.$old_key_2;
            if($old_key_3 != null) $info .= '.'.$old_key_3;
            if($old_key_4 != null) $info .= '.'.$old_key_4;
            if($old_key_5 != null) $info .= '.'.$old_key_5;
            $this->info($info);
        }
    }
}
