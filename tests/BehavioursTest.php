<?php

/**
 * Created by PhpStorm.
 * User: Fareez
 * Date: 2/10/17
 * Time: 5:03 PM
 */

use PHPUnit\Framework\TestCase;

class BehavioursTest extends TestCase
{
    public function testBehaviours(){

        $curlExecution = function($curlObj){
            /**
             *  switch casses on the url in the curl_opt and each case retunrs a different json
             */
            $url = curl_getinfo($curlObj,CURLOPT_URL);
            if(strpos($url,'behaviours') != false)
                return '{
  "behaviours": {
    "method": "GET",
    "path": "/behaviours"
  },
  "register": {
    "version": "1",
    "method": "POST",
    "path": "/register",
    "parameters": {
      "email": {
        "key": "user.email",
        "type": "body"
      },
      "password": {
        "key": "user.password",
        "type": "body"
      },
      "privilege": {
        "key": "user.privilege",
        "type": "body"
      }
    },
    "returns": {
      "email": {
        "type": "body"
      },
      "registered": {
        "type": "body"
      }
    }
  },
  "login": {
    "version": "1",
    "method": "POST",
    "path": "/login",
    "parameters": {
      "username": {
        "key": "user.username",
        "type": "body"
      },
      "email": {
        "key": "user.email",
        "type": "body"
      },
      "password": {
        "key": "user.password",
        "type": "body"
      },
      "ip": {
        "key": "ip",
        "type": "middleware"
      }
    },
    "returns": {
      "username": {
        "type": "body"
      },
      "email": {
        "type": "body"
      },
      "authenticated": {
        "type": "body"
      },
      "X-Access-Token": {
        "key": "token",
        "type": "header",
        "purpose": [
          "constant",
          {
            "as": "parameter",
            "unless": [
              "login",
              "register"
            ]
          }
        ]
      }
    }
  },
  "logout": {
    "version": "1",
    "method": "POST",
    "path": "/logout",
    "parameters": {
      "user": {
        "key": "user",
        "type": "middleware"
      },
      "token": {
        "key": "X-Access-Token",
        "type": "header"
      }
    },
    "returns": {
      "email": {
        "type": "body"
      },
      "unauthenticated": {
        "type": "body"
      }
    }
  }
}';
            else if(strpos($url,'register') != false)
                return '{
  "behaviour": "register",
  "version": "1",
  "response": {
    "email": "a@a.com",
    "registered": true
  }
}';


            else if(strpos($url,'logout') != false)
                return '{
  "behaviour": "logout",
  "version": "1",
  "response": {
    "email": "a@a.com",
    "unauthenticated": true
  }
}';
        };
        $localStorageSet = function($params = null){
            return $params;
        };
        $localStorageGet = function ($firstParam = null , $secondParam = null){
            return false;
        };
        /**
         *  after creating behaviours object we need to try to trigger each function and make the localstorage set a global variable
         */
        $mock = Behaviours::instance('http://www.google.com',$localStorageGet,$localStorageSet,$curlExecution);
        if($mock){
            $this->setResult('Behaviours Initiated Successfully');
        }
        else{
            $this->setResult('Error In Behaviours');
        }
        /*$stub = $this->createMock(Behaviours::class);
        $stub->method('instance')
            ->will($this->returnSelf());
        $this->assertSame($stub,$stub->instance());*/
    }
}
