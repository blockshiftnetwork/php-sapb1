<?php

namespace SAPb1;

/**
 * SAPClient manages access to SAP B1 Service Layer and provides methods to 
 * perform CRUD operations.
 */
class SAPClient{
    
    private Config $config;

    /**
     * Initializes SAPClient with configuration and auth basic data.
     */
    public function __construct(array $configOptions){
        $this->config = new Config($configOptions);
    }
    
    /**
     * Returns a new instance of SAPb1\Service.
     */
    public function getService(string $serviceName) : Service{
        return new Service($this->config, $serviceName);
    }

    /**
     * Returns a new instance of SAPb1\Query, which allows for cross joins.
     */
    public function query($join, $headers = []) : Query{
        return new Query($this->config,'$crossjoin('. str_replace(' ', '', $join) . ')', $headers);
    }
}
