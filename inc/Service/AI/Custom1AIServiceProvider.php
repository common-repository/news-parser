<?php

namespace NewsParserPlugin\Service\AI;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;
use NewsParserPlugin\Utils\ResponseFormatter;


class Custom1AIServiceProvider 
{
    public function __construct()
    {

    }
    public function getName()
    {
        return 'Custom1';
    }
    public function getOptions()
    {
       return [
        'featuredImage' => false,
        'postTitle'=>[
            'models'=>['custom1'],
        ],
        'postBody'=>[
            'models'=>['custom1'],
        ],
       ];
    }
    public function isAPIKeyDefined()
    {
        if(defined('NEWS_PARSER_CUSTOM1_API_KEY') && NEWS_PARSER_CUSTOM1_API_KEY!=''){
            return true;
        }
        return false;
    }
}