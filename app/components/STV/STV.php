<?php

class STV {

    var $APP_URL = 'https://sledovanitv.cz';
    var $api_debug = true;
    var $api_debug_url = true;

    function __construct() {
         $this->CONFIG = array(
            'apiUrl' => $this->APP_URL."/partner/api",
            'errorUrl' => $this->APP_URL."/partner-login",
             'login' => 'twotowers',
            'password' => '7jfCV6NpbUa0'
        );
    }

    function set_api_debug($value_main, $value_url = false) {
        $this->api_debug = $value_main;
        $this->api_debug_url = $value_url;
    }

    function do_query($function, $input = array()) {
        if($this->api_debug) {
            echo "[STV API DEBUG-CALL]: function: ". $function. " values: ". print_r($input, true);
        }
        $input['partner'] = $this->CONFIG['login'];
        $input['password'] = $this->CONFIG['password'];
        $url = $this->CONFIG['apiUrl']. '/'.$function.'?'. http_build_query($input);

        if($this->api_debug_url) {
            echo "[STV API DEBUG-URL]:". $url. "\n";
        }

        //$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Volání API sledovanitv: '.$url);

        $response = file_get_contents($url);
        $response = json_decode($response, true);

        if($this->api_debug) {
            echo "[STV API DEBUG-RESPONSE]:". print_r($response, true);
        }

        //$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Odpověď API sledovanitv: '.$response);

        return $response;
    }
    
    function activateUser ($id, $service = NULL)
    {
        if ($service == NULL) {
            $answer = $this->do_query('activate-user',array('partnerid' => $id));
        } else {
            $answer = $this->do_query('activate-user',array('partnerid' => $id, 'services' => $service));
        }
        return ($answer);
    }

    function deactivateUser ($id)
    {
        return ($this->do_query('deactivate-user',array('partnerid' => $id)));
    }
    
    function existUser ($id)
    {
        return ($this->do_query('get-user',array('partnerid' => $id)));
    }

}

?>