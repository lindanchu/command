<?php

namespace Linhdanchu\Artisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {composer} {suffix?} {base_repository?} {construct?}';
    protected $files;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->files = new Filesystem;
        parent::__construct();
    }

    protected $namespace = 'App/Repositories/';
    protected $viewComposer = '';
    protected $suffixName = 'Repository';
    protected $baseRepository = false;
    protected $construct = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($this->argument('base_repository') == 'true') $this->baseRepository = true;
        if($this->argument('construct') == 'true') $this->construct = true;
        $viewComposer = $this->argument('composer');
        if ($viewComposer === '' || is_null($viewComposer) || empty($viewComposer)) {
            return $this->error('Vui lòng nhập tên class!');
        }
        if(!$this->convertNameFile()){
            return;
        }
        $this->checkFile();
    }

    /**
     * Check name của file và namespace file
     */
    public function convertNameFile(){
        // lấy tên theo command
        $viewComposer = $this->argument('composer');

        // tách tên với namespace file
        $viewComposer = explode('/', $viewComposer);
        foreach ($viewComposer as $key => $value) {
            // check để lấy tên file
            if($key == count($viewComposer) - 1){
                $name = $value;
            }else{
                // gán thêm vào namespace
                $this->namespace .= ucfirst($value) .'/';
            }
        }

        // tách những kí tự đặc biệt và in hoa từ sau những kí tứ tự đặc biết (có check khoảng trắng)
        $arrName = preg_split('/[^A-Za-z0-9]+/', $name);
        foreach ($arrName as $value){
            $this->viewComposer .= ucfirst($value);
        }

        // check xem tên file của đã có hậu tố hay chưa
        if($this->argument('suffix') != 'true'){
            $this->viewComposer .= $this->suffixName;
        }
        if($this->viewComposer){
            return true;
        }
        $this->error('Tên class không hợp lệ!');
        return false;
    }

    /**
     * Check, tạo file và folder đã tồn tại hay chưa,
     */
    public function checkFile(){
        // check xem đã tồn tại folder hay chưa
        if(!$this->files->isDirectory($this->namespace)){
            // chưa tồn tại folder thì tạo mới folder
            $this->files->makeDirectory($this->namespace, 0777, true, true);
        }
        $pathFile = $this->namespace . $this->viewComposer . '.php';
        if($this->files->isFile($pathFile)){
            $this->error('File đã tồn tại');
        }else{
            if(!$this->files->put($pathFile, $this->content())){
                $this->error('Tạo file thất bại, Vui lòng thử lại');
            }else{
                $this->info('Tạo thành công repository ' . $pathFile);
            }
        }
    }

    /**
     * content file
     */
    public function content(){
        if($this->baseRepository){
            $baseRepository = '

use App\Repositories\BaseRepository;';
            $extends = ' extends BaseRepository';
            $model = '
    /**
     *  Namespace model
     * 
     *  @return $this->model
     */ 
    protected $MODEL = \'\';           
';
            if($this->construct){
                $construct = '
    public function __construct(){
        parent::__construct();
    }
';
            }else{
                $construct = '';
            }
        }else{
            $baseRepository = '';
            $extends = '';
            $model = '';
            if($this->construct){
                $construct = '
    public function __construct(){
        
    }
';
            }else{
                $construct = '';
            }
        }
        $content =
"<?php

namespace " .str_replace('/', '\\', substr($this->namespace, 0, -1)). ";".$baseRepository."

class ". $this->viewComposer . $extends ."
{".$model . $construct ."

}
";
        return $content;
    }
}
