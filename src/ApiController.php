<?php namespace calebdre\ApiSugar;


use Flight;

abstract class ApiController{
    public $mappings = [];

    protected function checkAgainstRequestParams($params){
        $data = $this->getRequestData();
        $diff = array_diff($params, array_keys($data));

        if(count($diff) != 0){
            $this->fail("missing fields: " . implode(", ", $diff));
        }
    }

    protected function error($message){
        Flight::json(['error' => $message]);
    }

    protected function fail($message = ""){
        Flight::json(["success" => false, "message" => $message]);
    }
    protected function success($message = "", $data = []){
        $payload = [
            "success" => true
        ];

        if(!empty($message)) $payload["message"] = $message;
        if(count($data) > 0) {
            $payload = array_merge($payload, $data);
        }

        Flight::json($payload);
    }

    protected function getRequestData(){
        $data = Flight::request()->data->getdata();
        if(count($data) == 0){
            $data = Flight::request()->query->getdata();
        }

        return $data;
    }

    protected function findModelOrFail($instance, $id, $name = "model"){
        $m = $instance->find($id);
        if(is_null($m)){
            $this->fail("Could not find $name.");
        }

        return $m;
    }
}