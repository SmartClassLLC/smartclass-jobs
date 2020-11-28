<?php
     
require_once __DIR__ . "/RestFul.php";
     
class SmartClass_Api extends SmartClass_RestFull {
     
    public $data = "";
 
    public function __construct()
    {
        parent::__construct();
    }
         
    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
    public function processApi()
    {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
        
        if((int)method_exists($this, $func) > 0) $this->$func();
        else $this->response('Error code 404, Page not found', 404);   // If the method not exist with in this class, response would be "Page not found".
    }
    
    private function hello()
    {
        echo str_replace("this", "that", "HELLO WORLD!!");
    }
         
    private function test(){    
        // Cross validation if the request method is GET else it will return "Not Acceptable" status
        if($this->get_request_method() != "GET") $this->response('', 406);
        
        $param = $this->_request['var'];
        
        // If success everythig is good send header as "OK" return param
        $this->response($param, 200);    
    }
     
    /*
     *  Encode array into JSON
    */
    private function json($data){
        if(is_array($data))
        {
            return json_encode($data);
        }
    }
}
 
?>