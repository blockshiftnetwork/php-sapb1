<?php

namespace SAPb1;

class SAPException extends \Exception{
    
    protected $statusCode;
    protected $context;
    /**
     * Initializes a new instance of SAPException.
     */
    public function __construct(Response $response, array $context = []){
        $this->statusCode = $response->getStatusCode();
        $message = '';
        $erroCode = $this->code;

        if($response->getHeaders('Content-Type') == 'text/html'){
            $message = $response->getBody();
        }

        if($response->getHeaders('Content-Type') == 'application/json'){
            $message = $response->getJson()->error->message->value ?? $response->getJson()->error->message;
            $erroCode = $response->getJson()->error->code;
        }

        $this->context = $context;
        
        parent::__construct($message, $erroCode);
    }
    
    public function getStatusCode() : int{
        return $this->statusCode;
    }

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
    
}
