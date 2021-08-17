<?php

namespace App\Libraries;

use Twilio\Rest\Client;
use Plivo\RestClient;

class SmsService {

    private $_number;
    private $_message;
    private $_error;

    /**
     * Class constructor
     * 
     * @param  string  $number
     * @param  string  $message
     * 
     * @return void
     */
    public function __construct($number, $message)
    {
        $this->_number = $number;
        $this->_message = $message;
        $this->addPlusSign();
        $this->validate();
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function error(){
        return $this->_error;
    }

    /**
     * Send message by specifying a service provider
     * 
     * @param string $provider
     * 
     * @return bool
     */
    public function send($provider){
        $result = false;
        try{
            // checks provider and process accordingly
            switch($provider){
                case 'twilio':
                    $result = $this->sendViaTwilio();
                    break;
                case 'plivo':
                    $result = $this->sendViaPlivo();
                    break;
                default:
                    throw new \Exception('Invalid service provider.');
                    break;
            }

        }catch(\Exception $e){
            // handles exception
            $this->_error = $e->getMessage();
        }
        return $result;
    }

    /**
     * check and add + sign to mobile number in case not supplied for a valid number
     * 
     * @return void
     */
    private function addPlusSign(){
        $isPlusSignExist = mb_substr($this->_number, 0, 1, 'utf-8') === '+' ? true : false;
        if(!$isPlusSignExist){
            $this->_number = '+' . $this->_number;
        }
    }

    /**
     * Validate number and message
     */
    private function validate(){
        // checks number length - should be 13 including + sign.
        if(strlen($this->_number) !== 13){
            throw new \Exception('Invalid mobile number.');
        }
        // message body should not be empty and null.
        if($this->_message === null || strlen(trim($this->_message)) === 0){
            throw new \Exception('Message body is empty.');
        }
    }

    /**
     * Send messages using twilio
     * 
     * @return bool
     */
    public function sendViaTwilio(){
        try{

            // set twilio configs
            $accountSid = env('TWILIO_ACCOUNT_ID', null);
            $authToken = env('TWILIO_AUTH_TOKEN', null);
            $serviceId = env('TWILIO_MESSAGING_SERVICE_ID', null);
            $fromNo = env('TWILIO_FROM_NO', null);

            // checks both token and account id is configured or not
            if($accountSid === null || $authToken === null
            || $accountSid === '' || $authToken === ''){
                throw new \Exception('Twilio - Account ID and Auth Token Required.');
            }

            // checks service id or from number is configured or not
            if(($serviceId === null && $fromNo === null )|| ($serviceId === '' || $fromNo === '')){
                throw new \Exception('Twilio - Service ID or From Number Required.');
            }

            // request body
            $options = array('body' => $this->_message);

            // set messagingServiceSid if supplied or the number given for from number
            if($serviceId !== null && $serviceId !== ''){
                $options['messagingServiceSid'] = $serviceId;
            }else{
                $options['from'] = $fromNo;
            }

            // create client and send message
            $client = new Client($accountSid, $authToken);
            $client->messages->create($this->_number, $options);

            return true;

        }catch(TwilioException $e){
            // handles TwilioException
            $this->_error = $e->getMessage();
        }catch(Twilio\Exceptions\RestException $e){
            // handles RestException
            $this->_error = $e->getMessage();
        }catch(\Exception $e){
            // handles all other exceptions
            $this->_error = $e->getMessage();
        }

        return false;
    }

    /**
     * Send messages using plivo
     */
    public function sendViaPlivo(){
        try{

            // set twilio configs
            $authId = env('PLIVO_AUTH_ID', null);
            $authToken = env('PLIVO_AUTH_TOKEN', null);
            $sourceNo = env('PLIVO_FROM_NO', null);

            // checks both token and account id is configured or not
            if($authId === null || $authToken === null || $sourceNo === null
            || $authId === '' || $authToken === '' || $sourceNo === ''){
                throw new \Exception('Plivo - Auth ID, Auth Token and Source No. Required.');
            }

            // create client and send message
            $client = new RestClient($authId, $authToken);
            $client->messages->create($sourceNo, [$this->_number], $this->_message);
            return true;

        }catch(Plivo\Exceptions\PlivoRestException $e){
            // handles PlivoRestException
            $this->_error = $e->getMessage();
        }catch(\Exception $e){
            // handles all other exceptions
            $this->_error = $e->getMessage();
        }

        return true;
    }
}