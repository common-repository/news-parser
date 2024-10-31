<?php

namespace NewsParserPlugin\Models;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Interfaces\ModelInterface;
use NewsParserPlugin\Message\Errors;

class AIOptionsModel implements ModelInterface
{
    /**
     * The table name for storing cron options.
     */
    protected const AI_OPTIONS_TABLE_NAME = NEWS_PURSER_PLUGIN_AI_OPTIONS_TABLE_NAME;
    
    public function __construct()
    {
       
    }
   
    public function create($ai_provider_name, $ai_provider_options_array)
    {
        if(!$this->isOptionsValid($ai_provider_options_array)){
            throw new MyException(Errors::text('WRONG_AI_OPTIONS_FORMAT'),Errors::code('INNER_ERROR'));
        }
        return $this->update($ai_provider_name, $ai_provider_options_array);
    }
    
   
    public function update($ai_provider_name, $ai_provider_options_array)
    {
        if(!$this->isOptionsValid($ai_provider_options_array)){
            throw new MyException(Errors::text('WRONG_AI_OPTIONS_FORMAT'),Errors::code('INNER_ERROR'));
        }
        $ai_providers_options=$this->getAll();
        $ai_providers_options[$ai_provider_name]=$ai_provider_options_array;    
        return update_option(self::AI_OPTIONS_TABLE_NAME, $ai_providers_options);
    }
    
    
    public function delete($ai_provider_name)
    {
        $ai_providers_options = $this->getAll();
        if (array_key_exists($ai_provider_name, $ai_providers_options)) {
            unset($ai_providers_options[$ai_provider_name]);
        }
        return update_option(self::AI_OPTIONS_TABLE_NAME, $ai_providers_options);
    }
    
    /**
     * Delete all cron options using the WordPress function delete_option.
     *
     * @return bool True if all cron options were deleted successfully, false otherwise.
     */
    public function deleteAll()
    {
        return delete_option(self::AI_OPTIONS_TABLE_NAME);
    }
    
    /**
     * Get all saved cron options using the WordPress function get_option.
     *
     * @return false|array An array containing all saved cron options, or false if no options are found.
     */
    public function getAll()
    {
        $result=get_option(self::AI_OPTIONS_TABLE_NAME);
        return is_array($result)?$result:[];
    }
    
    
    public function findByID($ai_provider_name)
    {
        $ai_providers_options = $this->getAll();
        if (array_key_exists($ai_provider_name, $ai_providers_options)) {
            return $ai_providers_options[$ai_provider_name];
        }
        return false;
    }
    
    protected function isOptionsValid($options)
    {
        if (empty($options)||!is_array($options)) {
            return false;
        }
        // Check if all required keys exist
        $required_keys = ['featuredImage','postTitle','postBody'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $options)) {
                return false;
            }
        }
        return true;
    }
}
