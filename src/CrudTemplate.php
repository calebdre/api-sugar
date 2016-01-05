<?php namespace calebdre\ApiSugar;

use \Flight;

class CrudTemplate extends ApiController{
    private $resource;
    public $mappings;
    private $eagerRelations;
    private $paginate;
    private $resourceName;

    public function __construct($model_name, $resource_name = null, $eagerRelations = [], $paginate = false, $not = [])
    {
        if(class_exists($model_name)){
            $this->resource = new $model_name();
        }else{
            throw new \Exception("Class with name '" .$model_name . "' does not exist.");
        }


        if($resource_name == null || empty($resource_name)){
            $resource_name = substr(strtolower(strrchr($model_name, '\\')), 1);
            $this->resourceName = $resource_name;
        }

        $this->eagerRelations = $eagerRelations;

        if($paginate && !isset($paginate['per_page'])){
            throw new \Exception("Paginate should have a per_page attribute.");
        }

        $this->paginate = $paginate;

        $this->mappings = [
            'getAll' => ['method' => 'get', 'route' => '/'.$resource_name],
            'getOne' => ['method' => 'get', 'route' => '/'.$resource_name.'/@id:\d*'],
            'getRelation' => ['method' => 'get', 'route' => '/'.$resource_name.'/@id:\d*/@relation'],
            'create' => ['method' => 'post', 'route' => '/'.$resource_name],
            'update' => ['method' => 'put', 'route' => '/'.$resource_name],
            'delete' => ['method' => 'delete', 'route' => '/'.$resource_name.'/@id:\d*'],
        ];

        $intersections = array_intersect(array_keys($this->mappings), $not);
        foreach($intersections as $intersection){
            unset($this->mappings[$intersection]);
        }
    }

    public function getAll(){
        $offset = Flight::request()->query["offset"];
        if($offset == null){
            $offset = 0;
        }

        if($this->paginate){
            $all = $this->resource->with($this->eagerRelations)->limit($this->paginate['per_page'])->offset($offset)->get();
            $all['offset'] = $offset;
        }else{
            $all = $this->resource->with($this->eagerRelations)->get();
        }
        Flight::json(($all->flatten()));
    }

    public function getOne($id){
        $one = $this->resource->with($this->eagerRelations)->find($id);
        if($one == null){
            Flight::json(['error' => 'resource could not be found.']);
        }else{
            Flight::json($one);
        }
    }

    public function create(){
        $data = Flight::request()->data->getData();

        $resource = $this->resource->create($data);

        $this->success("", [$this->resource->getTable() => $resource->toArray()]);
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
        $r = $this->resource->find($id);
        if($r){
            Flight::json($r->delete());
        }else{
            $this->success("Record alreaddy deleted");
        }
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