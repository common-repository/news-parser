<?php

namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;

/**
 * Class that handles chat with AI. 
 *
 *
 * @package Controller
 * @author  Evgeny S.Zalevskiy <2600@ukr.net>
 * @license MIT <https://opensource.org/licenses/MIT>
 *
 */

class AIController 
{
    
    protected $aiServiceProviders;
    public function __construct( $ai_service_providers=[])
    {
        $this->aiServiceProviders=$ai_service_providers;
    }
    public function chat($iaProvider,$request)
    {
        if(count($this->aiServiceProviders)==0||!array_key_exists($iaProvider,$this->aiServiceProviders)){
            throw new MyException(Errors::text('WORNG_AI_API_PROVIDER'),Errors::code('BAD_REQUEST'));
        }
        $ai_service_provider=$this->aiServiceProviders[$iaProvider];
        if(!$ai_service_provider->isAPIKeyDefined()){
            throw new MyException(Errors::text('NO_AI_API_KEY'),Errors::code('INNER_ERROR'));
        }
        return $result=$ai_service_provider->chat($request);
    }
}