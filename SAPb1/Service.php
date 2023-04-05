<?php

namespace SAPb1;

/**
 * Service class contains methods to perform CRUD actions on a service.
 */
class Service{
    
    private $config;
    private $session;
    private $serviceName;
    private $headers = [];
    public  $serviceViewName;
    
    /**
     * Initializes a new instance of Service.
     */
    public function __construct(Config $configOptions, array $session, string $serviceName){
        $this->config = $configOptions;
        $this->session = $session;
        $this->serviceName = $serviceName;
    }
    
    /**
     * Creates an entity.
     * Throws SAPb1\SAPException if an error occurred.
     */
    public function create(array $data){
        
        $response = $this->doRequest('POST', $data);

        if($response->getStatusCode() === 200){
            return $response->getJson();
        }
        
        if($response->getStatusCode() === 201){
            return $response->getJson();
        }

        if($response->getStatusCode() === 204){
            return true;
        }
        
        throw new SAPException($response, [
            'action' => 'create',
            'data' => $data,
            'status_code' => $response->getStatusCode(),
        ]);
    }
    
    /**
     * Updates an entity using $id. Returns true on success.
     * Throws SAPb1\SAPException if an error occurred.
     */
    public function update($id, array $data) : bool{
        
        if(is_string($id)){
            $id = "'" . str_replace("'", "''", $id) . "'";
        }

        $response = $this->doRequest('PATCH', $data, '(' . $id . ')');

        if($response->getStatusCode() === 204){
            return true;
        }
        
        throw new SAPException($response, [
            'action' => 'update',
            'id' => $id,
            'data' => $data,
            'status_code' => $response->getStatusCode(),
        ]);
    }
    
    /**
     * Deletes an entity using $id. Returns true on success.
     * Throws SAPb1\SAPException if an error occurred.
     */
    public function delete($id) : bool{
        
        if(is_string($id)){
            $id = "'" . str_replace("'", "''", $id) . "'";
        }

        $response = $this->doRequest('DELETE', '(' . $id . ')');

        if($response->getStatusCode() === 204){
            return true;
        }
        
        throw new SAPException($response, [
            'action' => 'delete',
            'id' => $id,
            'status_code' => $response->getStatusCode(),
        ]);
    }
    
    /**
     * Performs an action on an entity using $id. Returns true on success.
     * Throws SAPb1\SAPException if an error occurred.
     */
    public function action($id, string $action) : bool{
        
        if(is_string($id)){
            $id = "'" . str_replace("'", "''", $id) . "'";
        }

        $response = $this->doRequest('POST', null, '(' . $id . ')/' . $action);

        if($response->getStatusCode() === 204){
            return true;
        }
        
        throw new SAPException($response, [
            'action' => $action,
            'id' => $id,
            'status_code' => $response->getStatusCode(),
        ]);
    }
    
    /**
     * Returns a new instance of SAPb1\Query.
     */
    public function queryBuilder() : Query{
        return new Query($this->config, $this->session, $this->serviceName, $this->headers);
    }

    /**
     * Specifies request headers.
     */
    public function setHeaders($headers) : Service{
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Returns metadata for the service.
     */
    public function getMetaData() : array{
        $request = new Request($this->config->getServiceUrl('$metadata'), $this->config->getSSLOptions());
        $request->setMethod('GET');
        $request->setCookies($this->session);
        $response = $request->getResponse(); 

        $dom = new \DOMDocument();
        $dom->loadXML($response->getBody());
        
        $entitySetList = $dom->getElementsByTagName('EntityContainer')[0]->getElementsByTagName('EntitySet');
        
        $meta = [];
        
        foreach($entitySetList as $entitySet){
            if($entitySet->getAttribute('Name') == $this->serviceName){
                $entityType = $entitySet->getAttribute('EntityType');
                
                $array = explode('.', $entityType);
                
                $entityTypeList = $dom->getElementsByTagName('EntityType');

                foreach($entityTypeList as $entityType){
                    if($entityType->getAttribute('Name') == $array[1]){
                        $key = $entityType->getElementsByTagName('PropertyRef');
                        
                        if($key->length > 0){
                            $meta['key'] = $key[0]->getAttribute('Name');
                        }
                        
                        $properties = $entityType->getElementsByTagName('Property');
                        
                        foreach($properties as $property){
                            $name = $property->getAttribute('Name');
                            $meta['properties'][] = $name;
                        }
                        
                        $navProperties = $entityType->getElementsByTagName('NavigationProperty');
                        
                        foreach($navProperties as $property){
                            $name = $property->getAttribute('Name');
                            $meta['navigation'][] = $name;
                        }
                    }
                }
                break;
            }
        }
        
        return $meta;
    }
    
    private function doRequest($method, $postData, $action = '') : Response{
        $request = new Request($this->config->getServiceUrl($this->serviceName) . $action, $this->config->getSSLOptions());
        $request->setMethod($method);
        $request->setCookies($this->session);
        $request->setHeaders($this->headers);
        $request->setPost($postData);

        return $request->getResponse();
    }
}