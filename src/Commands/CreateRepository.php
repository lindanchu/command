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
     * @param string $composer tên file repository
     * @param boolean $suffix trạng thái tên repository đã có hậu tố Repository hay chưa
     * @param boolean $base_repository trạng thái có extends BaseRepository hay không
     * @param boolean $construct trang thái có thêm hamg construct hay không
     * @param boolean $create_base_repository trạng thái có tạo thêm file BaseRepository hay không
     */
    protected $signature = 'make:repository {composer} {suffix?} {base_repository?} {construct?} {create_base_repository?}';
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
    protected $createBaseRepository = false;
    protected $namespaceBaseRepository = 'App/Repositories';
    protected $nameBaseRepository = 'BaseRepository';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($this->argument('base_repository') == 'true') $this->baseRepository = true;
        if($this->argument('construct') == 'true') $this->construct = true;
        if($this->argument('create_base_repository') == 'true') $this->createBaseRepository = true;
        // if($this->argument('namespace_base_repository')){
        //     // $this->namespaceBaseRepository = $this->argument('namespace_base_repository');
        //     $this->checkNamespaceBase();
        // }
        $viewComposer = $this->argument('composer');
        if ($viewComposer === '' || is_null($viewComposer) || empty($viewComposer)) {
            return $this->error('Vui lòng nhập tên class!');
        }
        if(!$this->convertNameFile()){
            return;
        }
        if($this->createFile() && $this->createBaseRepository){
            $this->createBase();
        }
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
    public function createFile(){
        $return = false;
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
                $return = true;
            }
        }
        return $return;
    }

    /**
     * create basic repository
     */
    public function createBase(){
        if(!$this->createBaseRepository) return;
        // check xem đã tồn tại folder chứa base repository hay chưa
        $return = false;

        if(!$this->files->isDirectory($this->namespaceBaseRepository)){
            // chưa tồn tại folder thì tạo mới folder
            $this->files->makeDirectory($this->namespaceBaseRepository, 0777, true, true);
        }
        $pathFile = $this->namespaceBaseRepository . '/' . $this->nameBaseRepository . '.php';
        if($this->files->isFile($pathFile)){
            $this->error('File BaseRepository đã tồn tại');
        }else{
            if(!$this->files->put($pathFile, $this->contentBaseRepository())){
                $this->error('Tạo file BaseRepository thất bại, Vui lòng thử lại');
            }else{
                $this->info('Tạo thành công BaseRepository');
                $return = true;
            }
        }
        return $return;
    }

    /**
     * content file base repository
     */
    public function contentBaseRepository(){
        $content = 
'<?php

namespace '.str_replace('/', '\\', $this->namespaceBaseRepository).';

class BaseRepository
{
    protected $model;
    public function __construct(){
        if(isset($this->MODEL) && gettype($this->MODEL) == "string"){
            $this->model = new $this->MODEL;
        }
    }
}
        ';
        return $content;
    }

    /**
     * Check namespace base repository
     */
    // public function checkNamespaceBase(){
    //     // lấy namespace repository từ command
    //     $namespace = $this->argument('namespace_base_repository');
    //     // check regex namespace
    //     preg_match_all('/[^A-Za-z0-9|\.|\/|_]/', $namespace, $check, PREG_PATTERN_ORDER);
    //     if(count($check[0]) > 0){
    //         $this->error('namespace của file base repository không chứa kí tự đặc biệt');
    //         return false;
    //     }
    //     $namespaceReturn = '';
    //     $arrNamespace = explode('/', $namespace);
    //     foreach($arrNamespace as $key => $value){
    //         if($value === '') continue;
    //         // check bắt đầu file với foder k bắt đầu bằng số
    //         if(preg_match('/[0-9]/', $value[0]) !== 0){
    //             $this->error('Folder hoặc file không bắt đâu bằng chữ số');
    //             return false;
    //         }
    //         // bỏ đấu _ và chữ cài sau đáu gạch chuyển thành in hoa
    //         $arrNameOne = explode('_', $value);
    //         foreach ($arrNameOne as $one){
    //             $namespaceReturn .= ucfirst($one);
    //         }
    //         // check là phần tử cuối cùng thì k thêm dấu /
    //         if($key != count($arrNamespace) - 1 && $key != 0){
    //             $namespaceReturn .= '/';
    //         }
    //     }
    //     // check xem namespace có trùng với namespace mặc định hay k, nếu khác thì nối 2 chuỗi vào với nhau
    //     if($namespaceReturn != $this->namespaceBaseRepository){
    //         $this->namespaceBaseRepository .= '/'. $namespaceReturn;
    //     }
    //     return true;
    // }

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
