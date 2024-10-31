<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;

/**
 * Modifier  that iterates over an array of elements and removes 'srcSet' and 'sizes' attributes from 'img' tags.
 *
 * It takes a PostController instance as input and modifies the 'srcSet' and 'sizes' attributes of image elements in the parsed body array.
 * It sets the 'srcSet' and 'sizes' attributes of all 'img' tags to empty strings.
 *
 * @param array[] $data ['body'=>array,'options'=>array] 
 * @return void
 */
class RemoveSrcSetAndSizesModifier implements MiddlewareInterface
{
function __invoke ($parsed_data,$options)
{
    if($options['extraOptions']['addSrcSetAndSizes']) return $parsed_data;
    $new_body=[];
    foreach($parsed_data['body'] as $element){
        if($element['tagName']=='img'){
            $element['content']['srcSet']='';
            $element['content']['sizes']='';
        }
        array_push($new_body,$element);
    }
    $parsed_data['body']=$new_body;
    return $parsed_data;  
}
}