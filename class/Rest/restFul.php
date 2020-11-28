<?php

//include jwt class
include __DIR__ .  "/jwt.php";
     
class SmartClass_RestFull extends JWT {
     
    public $_allow = array();
    public $_content_type = "application/json";
    public $_request = array();
    public $_method = "";      
    
    private $_code = 200;
     
    public function __construct()
    {
        $this->inputs();
    }
     
    public function get_referer()
    {
        return $_SERVER['HTTP_REFERER'];
    }
     
    public function response($data, $status)
    {
        $this->_code = ($status) ? $status : 200;
        $this->set_headers();
        echo $data;
        exit;
    }
     
    private function get_status_message()
    {
        $status = array(
                    100 => 'Continue',  
                    101 => 'Switching Protocols',  
                    200 => 'OK',
                    201 => 'Created',  
                    202 => 'Accepted',  
                    203 => 'Non-Authoritative Information',  
                    204 => 'No Content',  
                    205 => 'Reset Content',  
                    206 => 'Partial Content',  
                    300 => 'Multiple Choices',  
                    301 => 'Moved Permanently',  
                    302 => 'Found',  
                    303 => 'See Other',  
                    304 => 'Not Modified',  
                    305 => 'Use Proxy',  
                    306 => '(Unused)',  
                    307 => 'Temporary Redirect',  
                    400 => 'Bad Request',  
                    401 => 'Unauthorized',  
                    402 => 'Payment Required',  
                    403 => 'Forbidden',  
                    404 => 'Not Found',  
                    405 => 'Method Not Allowed',  
                    406 => 'Not Acceptable',  
                    407 => 'Proxy Authentication Required',  
                    408 => 'Request Timeout',  
                    409 => 'Conflict',  
                    410 => 'Gone',  
                    411 => 'Length Required',  
                    412 => 'Precondition Failed',  
                    413 => 'Request Entity Too Large',  
                    414 => 'Request-URI Too Long',  
                    415 => 'Unsupported Media Type',  
                    416 => 'Requested Range Not Satisfiable',  
                    417 => 'Expectation Failed',  
                    500 => 'Internal Server Error',  
                    501 => 'Not Implemented',  
                    502 => 'Bad Gateway',  
                    503 => 'Service Unavailable',  
                    504 => 'Gateway Timeout',  
                    505 => 'HTTP Version Not Supported');
        return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
    }
     
    public function get_request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
     
    private function inputs()
    {
        //$method = ['GET'=>'read', 'POST'=>'create', 'PUT'=>'update', 'DELETE'=>'delete'];
        
        $requestMethod = $this->get_request_method();
        
        switch($requestMethod)
        {
            case "POST":
                if(!$this->authorize()) $this->response('', 401);
                $this->_request = $this->cleanInputs($_POST);
                $this->_method = "create";
                break;
                
            case "GET":
                if(!$this->authorize()) $this->response('', 401);
                $this->_request = $this->cleanInputs($_GET);
                $this->_method = "read";
                break;

            case "DELETE":
                if(!$this->authorize()) $this->response('', 401);
                $this->_request = $this->cleanInputs($_GET);
                $this->_method = "delete";
                break;
            
            case "PUT":
                if(!$this->authorize()) $this->response('', 401);
                parse_str(file_get_contents("php://input"), $this->_request);
                $this->_request = $this->cleanInputs($this->_request);
                $this->_method = "update";
                break;
            
            case "OPTIONS":
                $this->response('', 200);
                break;
            
            default:
                $this->response('', 406);
                break;
        }
    }       
     
    private function cleanInputs($data)
    {
        $clean_input = array();
        
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                $clean_input[$k] = $this->cleanInputs($v);
            }
            
            //add school id to data
            $clean_input["hSchoolId"] = trim($_SERVER["HTTP_SCHOOLID"]);
        }
        else
        {
            if(get_magic_quotes_gpc())
            {
                $data = trim(stripslashes($data));
            }
            
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        
        return $clean_input;
    }       
     
    private function authorize()
    {
        global $dbi;
        
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        $originReferer = $_SERVER["HTTP_REFERER"];
        $xtoken = trim(substr($_SERVER["HTTP_AUTHORIZATION"], 7)); //get rid of Bearer and empty char
        $schoolId = trim($_SERVER["HTTP_SCHOOLID"]);
        
        //static token for SmartClass
        $staticToken1 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MzE2MjU3NjUyfQ.3FbP75G1kCPDWSQGAe7kOEU_lNUaSdlJl6nbPLV9Ndg";
        $staticToken2 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NDQ2ODg2ODg2fQ.UEw0PxG4bl2BOV3FOkBRe6EoLb5aX6DLNOYDuubfS1k";
        
        $mobileApiToken = SMARTCLASS_MOBILE_API_TOKEN;
        $mobileApiTokenDev = SMARTCLASS_MOBILE_API_TOKEN_DEV;
        
        $exceptionPages = ["customers", "menus", "mobile"];

        if(in_array($_GET["page"], $exceptionPages)) return true;
        
        if(empty($xtoken))
        {
            if (preg_match("/accounts.google.com/i", $originReferer) || preg_match("/schoost.com/i", $originReferer) || preg_match("/smartclass.school/i", $originReferer) || preg_match("/smartclass.biz/i", $originReferer) || preg_match("/smartclass.tech/i", $originReferer)  || preg_match("/smartclass.technology/i", $originReferer) || preg_match("/smartclass.uaa.k12.tr/i", $originReferer) || preg_match("/smartclass.tac.k12.tr/i", $originReferer) || preg_match("/smartclass.aci.k12.tr/i", $originReferer) || preg_match("/smartclass.sevizmir.k12.tr/i", $originReferer) || preg_match("/smartclass.sevkoleji.k12.tr/i", $originReferer) || preg_match("/smartclass.sev.org.tr/i", $originReferer) || preg_match("/smartclass.sevtarsus.k12.tr/i", $originReferer) || preg_match("/smartclass.sevuskudar.k12.tr/i", $originReferer) || preg_match("/smartclass.ielev.k12.tr/i", $originReferer) || preg_match("/e-irfan.irfanokullari.com/i", $originReferer) || preg_match("/epalet.paletokullari.k12.tr/i", $originReferer) || preg_match("/smartclass.bilisim.k12.tr/i", $originReferer)) return true;
            else if(!empty($_GET["surl"]) && !empty($_GET["url"])) return true;
            else if(preg_match("/Bitbucket-Webhooks/", $_SERVER["HTTP_USER_AGENT"])) return true;
            else $this->response('Token Not Sent', 401);
        }
        else if ($xtoken == $staticToken1 || $xtoken == $staticToken2)
        {
            return true;
        }
        else if ($xtoken == $mobileApiTokenDev || $xtoken == $mobileApiToken)
        {
            return true;
        }
        else
        {
            //check the user by token
            $dbi->where("accessToken", $xtoken);
            $dbi->where("active", "1");
            $dbUserId = $dbi->getValue(_USERS_, "id");
            
            if(!empty($dbUserId))
            {
                return true;
            }
            else
            {
                //if the request is coming from out of domain then check school id
                if($schoolId == "" || $schoolId == null) $this->response('School Id Not Sent', 401);
                
                //check if the token still exits
                $dbi->where("token", $xtoken);
                $dbi->where("active", "1");
                if($schoolId == 0) $dbi->where("schoolId", $schoolId);
                else $dbi->where("schoolId", array($schoolId, 0), "IN");
                $dbTokenId = $dbi->getValue(_API_TOKENS_, "id");
                
                if(empty($dbTokenId)) $this->response('Token Not Found', 401);
            
                try {
                    //decode the token
                    $apiToken = $this->decode($xtoken, SMARTCLASS_SECRET_PHRASE);
                }
                catch (UnexpectedValueException $e) {
                    $error = $e->getMessage();
                    $this->response($error, 203);
                }
    
                //return false if the token is not valid
                if(empty($apiToken->id)) $this->response('Invalid Token', 401);
                
                //return true if the token is valid
                else return true;
            }
            
            return false;
        }
    }

    private function set_headers()
    {
        header("HTTP/1.1 " . $this->_code . " " . $this->get_status_message());
        header("Content-Type:". $this->_content_type);
    }
}   
