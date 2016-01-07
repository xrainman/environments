<?php

namespace PavolEichler\Environments;

class Environments {
    
    protected $default;
    protected $requireEnvironment;
    protected $environments;
    
    const ANY = '@@*';
    
    const HOSTNAME = 'hn';
    const HTTP_HOST = 'hh';
    const PATH = 'hp';
    const QUERY = 'hq';

    const ENVIRONMENT_IDENTITY = 'i';
    const ENVIRONMENT_PROPERTIES = 'p';
    
    /**
     * Creates the environment manager.
     * 
     * @param array $default Default values.
     */
    public function __construct($default, $requireEnvironment = true) {
        
        $this->default = $default;
        $this->requireEnvironment = $requireEnvironment;
        
    }
    
    /**
     * Adds a new environment or rewrites the existing one.
     * 
     * @param array $identity An array of values defining this environment. Use one or more of the following:
     *                           self::HOSTNAME Hostname as returned by \gethostname().
     *                           self::HTTP_HOST HTTP host value as defined in $_SERVER['HTTP_HOST'].
     *                           self::PATH File parent directory path as defined in dirname($_SERVER['PHP_SELF']).
     *                           self::QUERY An array of query paramters and their corresponding values to look for in $_GET.
     * @param array $properties An array to values to use for this environment. This array will be merged with default values provided on object construction.
     */
    public function setEnvironment($identity, $properties) {
        
        $this->environments[] = array(
            self::ENVIRONMENT_IDENTITY => ($identity === self::ANY) ? array() : $identity,
            self::ENVIRONMENT_PROPERTIES => (object) array_merge($this->default, $properties)
        );
        
    }
    
    /**
     * Returns all values for the current environment.
     * 
     * @return object
     * @throws \Exception If no matching environment is found.
     */
    public function getCurrentEnvironment() {
        
        // pick the current environment settings
        foreach ($this->environments as $environment){
            // get identity definition
            $identity = $environment[self::ENVIRONMENT_IDENTITY];
            
            // match hostnames
            if (key_exists(self::HOSTNAME, $identity) AND $identity[self::HOSTNAME] !== \gethostname())
                continue;
            
            // match http hosts
            if (key_exists(self::HTTP_HOST, $identity) AND $identity[self::HTTP_HOST] !== $_SERVER['HTTP_HOST'])
                continue;
            
            // match paths
            if (key_exists(self::PATH, $identity) AND $identity[self::PATH] !== dirname($_SERVER['PHP_SELF']))
                continue;
            
            // match query parameters
            if (key_exists(self::QUERY, $identity)){
                $match = true;
                foreach ($identity[self::QUERY] as $key => $value){
                    if (!key_exists($key, $_GET) OR $_GET[$key] !== $value){
                        $match = false;
                        break;
                    }
                }
                if (!$match)
                    continue;
            }
            
            // we have found a matching environment
            return $environment[self::ENVIRONMENT_PROPERTIES];
        }
        
        // oh, no matching environment found
        if ($this->requireEnvironment){
            throw new \Exception('No matching environment found.');
        }else{
            return (object) $this->default;
        }
        
    }
    
}