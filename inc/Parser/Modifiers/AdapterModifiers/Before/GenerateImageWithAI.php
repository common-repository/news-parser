<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;
use NewsParserPlugin\Service\AI\Custom1AIServiceProvider;
use NewsParserPlugin\Service\AI\OpenAIServiceProvider;



class GenerateImageWithAI implements MiddlewareInterface
{
    public $aiServeceProvider;
    public function __construct($ai_service_provider)
    {   
        $this->aiServeceProvider=$ai_service_provider;
    }   
    function __invoke ($parsed_data,$options)
    {
        if(!$options['aiOptions']) return $parsed_data;
        if(!$options['aiOptions']['featuredImage']['generateWithAI']||
           !$options['aiOptions']['featuredImage']['model']||
           !$options['aiOptions']['featuredImage']['prompt']) return $parsed_data;
        $model=$options['aiOptions']['featuredImage']['model'];
        $promt=$options['aiOptions']['featuredImage']['prompt'];
        $size=$options['aiOptions']['featuredImage']['size'];
        $post_title=$parsed_data['title'];
        $full_prompt = str_replace('${title}', $post_title, $promt);
        $ai_request_options=[
           'model'=>$model,
           'prompt'=>$full_prompt,
           'n'=>1,
           'size'=>$size?$size:"1024x1024"
        ];
        $responce=$this->aiServeceProvider->image($ai_request_options);
        if(!$responce) return $parsed_data;
        $parsed_data['image']=$responce;
        return $parsed_data;
    }
}