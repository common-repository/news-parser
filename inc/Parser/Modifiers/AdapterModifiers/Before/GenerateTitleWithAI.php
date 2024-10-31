<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;
use NewsParserPlugin\Service\AI\Custom1AIServiceProvider;
use NewsParserPlugin\Service\AI\OpenAIServiceProvider;



class GenerateTitleWithAI implements MiddlewareInterface
{
    public $aiServeceProviders;
    public $aiServeceProvider;
    public function __construct($ai_service_providers)
    {   
        $this->aiServeceProviders=$ai_service_providers;
    }   
    function __invoke ($parsed_data,$options)
    {
        if(!$options['aiOptions']) return $parsed_data;
        if(!$options['aiOptions']['postTitle']['generateWithAI']||
           !$options['aiOptions']['postTitle']['model']||
           !$options['aiOptions']['postTitle']['prompt']) return $parsed_data;
        $provider=$options['aiOptions']['aiProviders']['textGenerator'];
        $model=$options['aiOptions']['postTitle']['model'];
        $promt=$options['aiOptions']['postTitle']['prompt'];
        $this->aiServeceProvider = reset(
            array_filter($this->aiServeceProviders,function($ai_provider) use ($provider) {
                return $ai_provider->getName() == $provider;
            })
        );
        $post_title=$parsed_data['title'];
        $full_prompt = str_replace('${title}', $post_title, $promt);
        $ai_request_options=[
           'model'=>$model,
           'messages'=>[
                [
                'role'=>'user',
                'content'=>$full_prompt
            ]
           ]
          
        ];
        $responce=$this->aiServeceProvider->chat($ai_request_options);
        if(!$responce) return $parsed_data;
        $parsed_data['title']=$responce;
        return $parsed_data;
    }
}