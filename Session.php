<?php
//require_once('Loan.php');
require_once('Response.php');

class Session 
{
    private $creds = "username=".USER."&password=".PASSWORD;
    public $curl_ch;
    public $request_endpoint;
    public $request_response;
    public $all_session_requests = [];
    public $session_id;
    public $executionTime;


    function __construct()
    {
        $this->curl_ch = curl_init();
    }

    function createSessionRequest()
    {
        try
        {
            $this->query(CREATESESSION, $this->getCreds());
            $this->setSessionId();
        }
        catch(Exception $e)
        {
            $e->getMessage();
        }

    }

    function endSession()
    {
        $this->query(CLOSESESSION, "sid=".$this->session_id);
        curl_close($this->curl_ch);
    }

    function clearSession()
    {
        $this->query(CLEARSESSION, $this->getCreds());
    }
    
    function query($endpoint, $query_data)
    {
        curl_reset($this->curl_ch);
        curl_setopt($this->curl_ch, CURLOPT_URL, $endpoint);
        $this->setPostData($this->curl_ch, $query_data);
        $this->request_endpoint = $endpoint;
        $start_time = $this->startExecutionTime();
        $response = curl_exec($this->curl_ch);
        $end_time = $this->stopExecutionTime();
        $this->executionTime = $this->calculateExecutionTime($start_time, $end_time);
        $this->setRequestResponse($endpoint, $response, $start_time, $end_time, $this->executionTime);
        if($this->request_response->isStatusOk() === false)
        {
            return throw new Exception("Error: ".$this->request_response->msg);
        }
        return $this->getRequestResponse();
       
        $this->all_session_requests[] = $this->request_response;
    }

    function requestFileQuery($file_id)
    {
        try
        {
            $this->query(REQUESTFILE, "sid=$this->session_id&uid=".USER."&fid=$file_id");
            return $this->getRequestResponse();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        return $this->request_response;
    }

    function queryFiles()
    {
        $this->query(QUERYFILES, "uid=".USER."&sid=$this->session_id");
        return $this->request_response;
    }

    function requestAllDocuments()
    {
        $this->query(REQUESTALLDOCS,"sid=$this->session_id&uid=".USER);
        return $this->request_response;
    }

    function requestAllLoanIds()
    {
        return $this->query(REQUESTALLLOANS,"sid=$this->session_id&uid=".USER);
    }

    function requestFileByLoanNumber($loan_id)
    {
        $this->query(REQUESTFILEBYLOAN,"sid=$this->session_id&uid=".USER."&lid=$loan_id");
        return $this->request_response;
    }

    function setPostData($curl_ch, $post_data)
    {
        curl_setopt($curl_ch, CURLOPT_POST,1);
        curl_setopt($curl_ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_ch, CURLOPT_HTTPHEADER, array(
            'content-type: application/x-www-form-urlencoded',
            'content-length: '. strlen($post_data)));
    }

    function displayEndpointResponse()
    {
        echo "<pre>";
        print_r($this->request_response);
        echo "</pre>";
    }

    function startExecutionTime()
    {
        $time_start=microtime(true);

        return $time_start;
    }

    function stopExecutionTime()
    {
        $time_end=microtime(true); 

        return $time_end;
    }

    function calculateExecutionTime($time_start, $time_end)
    {
        ($time_end-$time_start) / 60;
    }
    //create a new response obj and assign it to request_response property
    function setRequestResponse($endpoint, $response, $start_time, $end_time, $execution_time)
    {
        if($endpoint == REQUESTFILE && !strstr($response, "Status"))
        {
            echo "\nfile request true and status: ok";
            $this->request_response = new Response($endpoint, $response, $start_time, $end_time, $execution_time, true);
            
        }
        else
        {
            $response_arr = json_decode($response, true);
            $this->request_response = new Response($endpoint, $response_arr, $start_time, $end_time, $execution_time, false);
        }
    }

    function getRequestResponse()
    {
        return $this->request_response;
    }

    function setSessionId()
    {
        $this->session_id = $this->request_response->action;
    }

    function getCreds()
    {
        return $this->creds;
    }
}

?>