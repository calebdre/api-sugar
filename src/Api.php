<?php namespace calebdre\ApiSugar;

use \Flight;
use \ReflectionClass;
use ReflectionException;
use \Illuminate\Database\Capsule\Manager as Capsule;
use \Illuminate\Events\Dispatcher;
use \Illuminate\Container\Container;

class Api{
	private $endpoints = [];
    private $crud = false;
    private $dbConnected = false;
	
	private function addEndpoint($method, $name, Callable $func){
        if(!$this->dbConnected) throw new \Exception("Hook up the database first. Call configureDB");

		$this->endpoints[] = ['name' => $name, 'method' => $method, 'func' => $func];
		return $this;
	}
	
	public function addClass(ApiController $class){
        $reflection = new ReflectionClass($class);

        try{
            $mappings = $reflection->getStaticPropertyValue('mappings');
        }catch(ReflectionException $e){
            $mappings = $reflection->getProperty('mappings')->getValue($class);
        }

        if(isset($mappings['crud'])){
            $this->crud = true;
            $this->addClass(new CrudTemplate(
                $mappings['crud']['model'],
                (isset($mappings['crud']['resource_name'])) ? $mappings['crud']['resource_name'] : null,
                (isset($mappings['crud']['eager_relations'])) ? $mappings['crud']['eager_relations']: []
            ));
        }

		foreach($reflection->getMethods() as $method){
            if(!isset($mappings[$method->name])){
                continue;
            }

            $mapping = $mappings[$method->name];

            if(isset($mapping['method']) && isset($mapping['route'])){
                $apiMethod = $mapping['method'];
                $route = $mapping['route'];

                $this->addEndpoint($apiMethod, $route, $method->getClosure($class));
            }else{
                throw new \Exception('Mappings of methods should include both a method and route.');
            }
		}

		return $this;
	}

	private function fillRoutes(){
		foreach($this->endpoints as $endpoint){
			$route = implode(' ', [strtoupper($endpoint['method']), $endpoint['name']]);
			Flight::route($route, $endpoint['func']);
		}
	}

    public function configureDB(Array $options){
        $capsule = new Capsule;
        $capsule->addConnection($options);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->dbConnected = true;
    }
	
	public function execute(){
		$this->fillRoutes();
		Flight::start();
	}
}