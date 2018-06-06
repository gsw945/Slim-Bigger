<?php
namespace App\Controllers;

use Illuminate\Database\Eloquent\Builder;

/**
 * ControllerBase
 * @property Builder $db
 */
class ControllerBase
{
    protected $ci;
    /** @var Builder $db */
    protected $db;
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $file_store = null;

    public function __construct(\Interop\Container\ContainerInterface $ci)
    {
        $this->ci = $ci;
        // 加载DB容器
        $this->db = $ci->get('db');
        $this->pdo = $ci->get('pdo'); // 从容器中获取　pdo 之前，必须先取　db
        // 加载Local FileSystem容器
        $this->file_store = $ci->get('file_store');
    }

    /**
     * 获取正确的值.
     * @param $key
     * @param $param
     * @param array $args
     * @return mixed|null
     */
    public function filter_param($key,$param,$args = []){
        if(array_key_exists($key,$param)){
            return $param[$key];
        }
        if($args){
            if(array_key_exists($key,$args)){
                return array_get($args,$key,null);
            }
        }
        return null;
    }
}