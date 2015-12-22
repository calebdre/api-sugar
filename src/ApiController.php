<?php namespace calebdre\ApiSugar;


use Flight;

abstract class ApiController{
    public $mappings = [];

    protected function checkAgainstRequestParams($params){
        $data = Flight::request()->data->getData();
        $diff = array_diff(array_values($params), array_keys($data));

        if(count($diff) == 0){
            return true;
        }else{
            return $diff;
        }
    }

    protected function error($message){
        Flight::json(['error' => $message]);
    }

    protected function fail($message = ""){
        Flight::json(["success" => false, "message" => $message]);
    }
    protected function success($message = ""){
        Flight::json(["success" => true, "message" => $message]);
    }
}