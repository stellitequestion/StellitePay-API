<?php
session_start();

/**
 * Reference implementation for StellitePay's REST API.
 *
 * See https://www.github.com/stellitecoin/stellitepay-api for more info.
 *
 *
*/

class StellitePay
{
    protected $access_token;    // API key
    protected $url;             // API base URL
    protected $version;         // API version
    protected $curl;            // curl handle
    protected $key;             // Your Integrator Key 

    /**
     * Constructor for StelliteAPI
     *
     * @param string $version API version
    */
    function __construct($access_token = '0',$version='1')
    {
        $this->url = 'https://api.stellitepay.com';
        $this->access_token = $access_token;
        $this->version = '/v'.$version;
        $this->key = "";
        $this->curl = curl_init();

        curl_setopt_array($this->curl, array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'StellitePayBETA',
            CURLOPT_RETURNTRANSFER => true)
        );
    }

    function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * Query login
     *
     * @param string $email method path
     * @param array $password request parameters
     * 
     * @return array access token
    */
    function login($email, $password){
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/login" ,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => json_encode(array("email" => $email, "password" => $password, "key" => $this->key)
        )));
        
        $r = json_decode(curl_exec($this->curl));
        if($r->status == 'success') $this->access_token = $r->message->access_token;
        return $r;
    }

    /**
     * Query create a new user
     *
     * @param string $email user's email
     * @param string $name user's name
     * @param string $password user's password
     * 
     * @return array created user
    */
    function register($name, $email, $password, $beta, $ref=''){
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/register" ,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => json_encode(array("user" => array("name" => $name, "email" => $email, "password" => $password, "key" => $this->key, "betakey" => $beta))
        )));
            
        $r = json_decode(curl_exec($this->curl));
        if(isset($r->message->access_token)) $this->access_token = $r->message->access_token;
        return $r;
    }

    /**
     * Query logout
    */
    function logout()
    {   
        $this->lofi();
        curl_setopt_array($this->curl, array(
                CURLOPT_URL => $this->url . $this->version.'/logout',
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                            'Authorization: Bearer ' . $this->access_token)
                ));
        return json_decode(curl_exec($this->curl));
    }

    /**
     * Query activate email
     *
     * @param string $activate activation string
     * 
    */
    function activate($activate){
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/activate" ,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => json_encode(array("activate" => $activate)
        )));
        return json_decode(curl_exec($this->curl));
    }

    /**
     * Query balance
     * 
     * @return array balance
    */
    public function balance()
    {
        $this->lofi();
        curl_setopt_array($this->curl, array(
                CURLOPT_URL => $this->url . $this->version.'/balance',
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                            'Authorization: Bearer ' . $this->access_token)
                ));
        return json_decode(curl_exec($this->curl));
    }

    /**
     * Query userdata
     * 
     * @return array userdata
    */
    public function user()
    {
        $this->lofi();
        curl_setopt_array($this->curl, array(
                CURLOPT_URL => $this->url . $this->version.'/user',
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                            'Authorization: Bearer ' . $this->access_token)
                ));
        return json_decode(curl_exec($this->curl));
    }

    /**
     * Query transfer
     *
     * @param string $address Integrated-Address, Normal-Address, E-Mail
     * @return int $amount Stellite 1 Unit = 0.01 XTL
    */
    function transfer($address, $amount)
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/transfer" ,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token),
            CURLOPT_POSTFIELDS => json_encode(array("address" => $address,"amount" => $amount)
        )));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /**
     * Query transactoins
     * 
     * @return array $r of all transactions
    */
    function transactions()
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/transactions" ,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token)
        ));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /**
     * Query addressbook
     * 
     * @return array $r all addresses
    */
    function getaddressbook()
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/addressbook" ,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token)
        ));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /**
     * Query add addressbook entry
     * 
     * @return array $r 
    */
    function addaddressbook($address, $description)
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/addressbook" ,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token),
            CURLOPT_POSTFIELDS => json_encode(array("address" => $address,"description" => $description))
        ));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /**
     * Query delete addressbook entry
     * 
     * @return array $r 
    */
    function deladdressbook($address)
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/addressbook" ,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token),
            CURLOPT_POSTFIELDS => json_encode(array("address" => $address))
        ));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /**
     * Query all transactions
     * 
     * @return array $r of all recent transactions
    */
    function alltransactions()
    {   
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->url . $this->version . "/alltransactions" ,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                                        'Authorization: Bearer ' . $this->access_token)
        ));
            
        $r = json_decode(curl_exec($this->curl));
        return $r;
    }

    /** 
     *  @return Object "Login First" Error 
    */
    private function lofi()
    {
        if($this->access_token == ''){
            $o = new \stdClass;
            $o->success = false;
            $o->message = "login first"; 
            return $o;
        }
    }
}