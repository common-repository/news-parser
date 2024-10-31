<?php
namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;
use NewsParserPlugin\Interfaces\ModelInterface;

/**
 * Class saves received options.
 *
 *
 *
 *
 * @package Controller
 * @author  Evgeny S.Zalevskiy <2600@ukr.net>
 * @license MIT <https://opensource.org/licenses/MIT>
 *
 */

class AIOptionsController 
{
    protected $aiOptionsModel;
    protected $aiServiceProviders;
    public function __construct(ModelInterface $ai_options_model, $ai_service_providers=[])
    {
        $this->aiOptionsModel=$ai_options_model;
        $this->aiServiceProviders=$ai_service_providers;
    }
    public function get($ai_provider_name=null)
    {   
        if(count($this->aiServiceProviders)==0) return false;
        $result=[];
        foreach ($this->aiServiceProviders as $ai_service_provider) {
            if($ai_service_provider->isAPIKeyDefined()){
                $ai_options=$this->aiOptionsModel->findByID($ai_service_provider->getName());
                if(!$ai_options){
                    $ai_options=$ai_service_provider->getOptions();
                    $this->aiOptionsModel->create($ai_service_provider->getName(),$ai_options);
                }
                $result[$ai_service_provider->getName()]=$ai_options;
            }
        }
        
        if($ai_provider_name){
            if(array_key_exists($ai_name,$result)){
                return $result[$ai_name];
            } 
            return false;
        } 
        return count($result)>0?$result:false;
    }
}