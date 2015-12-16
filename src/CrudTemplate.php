<?php namespace calebdre\ApiSugar;

use \Flight;

class CrudTemplate extends ApiController{
    private $resource;
    public $mappings;
    private $eagerRelations;
    private $paginate;

    public function __construct($model_name, $resource_name = null, $eagerRelations = [], $paginate = false)
    {
        if(class_exists($model_name)){
            $this->resource = new $model_name();
        }else{
            throw new \Exception("Class with name '" .$model_name . "' does not exist.");
        }


        if($resource_name == null || empty($resource_name)){
            $resource_name = substr(strtolower(strrchr($model_name, '\\')), 1);
        }

        $this->eagerRelations = $eagerRelations;

        if($paginate && !isset($paginate['per_page'])){
            throw new \Exception("Paginate should have a per_page attribute.");
        }

        $this->paginate = $paginate;

        $this->mappings = [
            'getAll' => ['method' => 'get', 'route' => '/'.$resource_name],
            'getOne' => ['method' => 'get', 'route' => '/'.$resource_name.'/@id'],
            'getRelation' => ['method' => 'get', 'route' => '/'.$resource_name.'/@id/@relation'],
            'create' => ['method' => 'post', 'route' => '/'.$resource_name],
            'update' => ['method' => 'put', 'route' => '/'.$resource_name],
            'delete' => ['method' => 'delete', 'route' => '/'.$resource_name.'/@id'],
        ];
    }

    public function getAll(){
        $offset = Flight::request()->query["offset"];
        if($offset == null){
            $offset = 0;
        }

        if($this->paginate){
            $all = $this->resource->with($this->eagerRelations)->limit($this->paginate['per_page'])->offset($offset)->get();
        }else{
            $all = $this->resource->with($this->eagerRelations)->get();
        }
        Flight::json($all);
    }

    public function getOne($id){
        $one = $this->resource->find($id);
        if($one == null){
            Flight::json(['error' => 'resource could not be found.']);
        }else{
            Flight::json($one->toArray());
        }

    }

    public function create(){
        $data = Flight::request()->data->getData();
        $resource= $this->resource->create($data);

        Flight::json($resource->all()->getDictionary());
    }

    public function update(){
        $string = Flight::request()->getBody();
        parse_str($string, $data);

        $resource= $this->resource->find($data['id']);

        if($resource != null){
            $resource->fill($data);
            $resource->save();

            Flight::json($resource->toArray());
        }else{
            Flight::json(['error' => "resource could not be found."]);
        }
    }

    public function delete($id){
        Flight::json($this->resource->find($id)->delete());
    }

    private function getRelation($id, $relation)
    {
        $one = $this->resource->find($id);
        if($one == null){
            Flight::json(['error' => 'resource could not be found.']);
        }else{
            Flight::json($one->$relation);
        }
    }
}