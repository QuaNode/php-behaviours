<?php

/**
 * Created by PhpStorm.
 * User: Fareez
 * Date: 1/25/17
 * Time: 4:36 PM
 */
class Behaviours
{

    public static function instance($baseURL,$defaults,$localStorageFnGet,$localStorageFnSet){ // localStorageFN Last Parameters instead of it we will use 2 fns storage set and storage get
        static $instance = null;
        if($instance === null){
            $instance = new self($baseURL,$defaults,$localStorageFnGet,$localStorageFnSet);
        }
        return $instance;
    }
    private $behavioursJson ;
    private $localStorageGet ;
    private $localStorageSet ;
    private $baseUrl = '';// null instead of an array
    //private $keys = array(); // null instead of an array ,, inside getBehaviour
    //private $behavioursArray = array(); // not needed
    //private $params;
    private function __construct($baseURL, $defaults = null, $storageGet ,$storageSet)
    {
        // construct(bas,storageGet)
        //
        $this->parameters = $storageGet && $storageGet('Behaviours') ? $storageGet('Behaviours') : new \stdClass();
        //
        $this->localStorageGet = $storageGet;
        $this->localStorageSet = $storageSet;
        $this->baseUrl = $baseURL;
        foreach ($defaults as $k => $v) {
            $this->parameters->$k = $v;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $baseURL . '/behaviours');
        $this->behavioursJson = curl_exec($curl); // check for curl error else throw error
        $this->behavioursJson = json_decode($this->behavioursJson);
        // { "key" : "value" } {
        //      $key = "value"
        //  }
        // return['key']
        $keys = array_keys($this->behavioursJson);
        for($i = 0; $i<count($keys); $i++) {
            $this[$keys[$i]] = $this->getBehaviour($keys[$i]);
        }
    }
    private function getBehaviour($behaviourName)
    {
        if($this->behavioursJson) {
            if(isset($this->behavioursJson[$behaviourName])) {
                $behaviour = $this->behavioursJson[$behaviourName];
                return function($behaviourData) use ($behaviour,$behaviourName){
                    if(!$behaviourData)
                        $behaviourData = array();
                    if (!(count($behaviour['parameters']) > 0)) {
                        $behaviour['parameters'] = array();

                    }
                    foreach ($this->parameters as $k => $v) {
                        $behaviour['parameters'][$k] = $v;
                    }
                    $params = $behaviour['parameters'];
                    $keys = array_keys($params);
                    $headers = array();
                    $data = array();
                    $url = $behaviour['path'];
                    foreach ($keys as $key) {
                        if($params && $params[$keys[$key]]) {
                            if (isset($params[$keys[$key]]['unless'])) {
                                $unless = array_search($behaviourName, $params[$keys[$key]]['unless']);
                                if ($unless || ($unless == 0))
                                    continue;
                            }
                            if (isset($params[$keys[$key]]['for'])) {
                                $for = array_search($behaviourName, $params[$keys[$key]]['for']);
                                if ($for == false)
                                    continue;
                            }
                            $type = $params[$keys[$key]]['type'];

                                switch ($type) {
                                    case 'header':
                                        $headers[$params[$keys[$key]]['key']] = $this->getValueForParameter($params[$keys][$key], $behaviourData, $keys[$key], $behaviourName);
                                        break;
                                    case 'body':
                                        $paths = explode('.',$params[$keys[$key]]['key']);
                                        $nestedData = $data;
                                        $lastPath = null;
                                        foreach ($paths as $path){
                                            if($lastPath){
                                                $nestedData = $nestedData[$lastPath];
                                            }
                                            if(!$nestedData[$paths[$path]]){
                                                $nestedData[$paths[$path]] = array();
                                            }
                                            $lastPath = $paths[$path];
                                        }
                                        if($lastPath)
                                            $nestedData[$lastPath]=$this->getValueForParameter($params[$keys[$key]],$behaviourData,$keys[$key],$behaviourName);
                                        break;
                                    case 'path':
                                        str_replace(':'.urlencode($params[$keys[$key]]['key']),urlencode($this->getValueForParameter($params[$keys][$key], $behaviourData, $keys[$key], $behaviourName)), $url);
                                        break;
                                    case 'query':
                                        if(strstr($url,'?') == false)
                                            $url = $url.'?';
                                        $url = $url . '&' . urlencode($params[$keys[$key]]['key']) . '=' . urlencode($this->getValueForParameter($params[$keys][$key], $behaviourData, $keys[$key], $behaviourName));
                                        break;
                                }

                        }
                    }
                    $call = curl_init();
                    curl_setopt($call, CURLOPT_URL, $this->baseUrl.$url);
                    curl_setopt($call, CURLOPT_HEADER, $headers);
                    curl_setopt($call, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($call,CURLOPT_CUSTOMREQUEST,strtoupper($behaviour['method']));
                    curl_setopt($call,CURLOPT_RETURNTRANSFER,1);
                    $response = curl_exec($call);
                    return $response;
                };
            }
            else {
                throw new ErrorException('This behaviour doesn\'t exist');
            }
        }
        else {
            throw new ErrorException('Behaviours is not ready yet');
        }
    }
    function getValueForParameter($parameter,$data,$key,$name)
    {
        if($data[$key])
            return $data[$key];
        else {
            if($parameter['value']){
                if(is_callable($parameter['value']))
                    return $parameter['value']($name,$data);
                else
                    return $parameter['value'];

            }
            else{
                $meta = $this->localStorageGet('Behaviours',$parameter['source']);
                if(isset($meta[$key]) && isset($meta[$key]['value']))
                    return $meta[$key]['value'];
                else
                    return $this->localStorageGet($key,$parameter['source']);
            }
        }
    }
}

