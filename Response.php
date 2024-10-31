<?php
class Response
{
    public $session_id;
    public $endpoint;
    public $request_response;

    public $status;
    public $msg;
    public $action;

    public $request_timestamp;
    public $received_timestamp;
    public $total_time;
    public $responseSize;

    function __construct($endpoint, $response, $request_time, $received_time, $execution_time, $isFile = false)
    {
        if($isFile)
        {
            $this->endpoint = $endpoint;
            $this->request_response = $response;
            $this->request_timestamp = $request_time;
            $this->received_timestamp = $received_time;
            $this->total_time = $execution_time;
            $this->status = "status ok";
            $this->msg = $response;
            $this->action = "Continue.";
        }
        else
        {
            $this->endpoint = $endpoint;
            $this->request_response = $response;
            $this->request_timestamp = $request_time;
            $this->received_timestamp = $received_time;
            $this->total_time = $execution_time;
            $this->status = $response[0];
            $this->msg = $response[1];
            $this->action = $response[2];
        }
    }

    function logResponseError()
    {

    }

    function isStatusOk()
    {
        echo currTime()." Checking response status...\n";
        if(preg_match("/\bok\b/mi", $this->status))
        {
            echo $this->status."\n";
            return true;
        }
        else
        {
            echo currTime()." Error: Bad response\n";
            echo "$this->status\n";
            return false;
        }
    }

}
?>