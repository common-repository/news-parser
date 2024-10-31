<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;
/**
 * Modifier  that removes duplicated parsed image elements.
 *
 * It takes an instance of the PostController class and removes any duplicated image elements from the parsed body array based on their 'src' attribute.
 * It returns a new array of body elements with duplicated images removed.
 *
 * @param array $data ['body'=>array,'options'=>array] 
 * @return array 
 */

class RemoveDublicatedPicturesModifier implements MiddlewareInterface
{
function __invoke ($parsed_data,$options=null)
{
    $new_body=[];
    $image_src_map=[];
    foreach($parsed_data['body'] as $element){
        if($element['tagName']=='img'){
            if(!in_array($element['content']['src'],$image_src_map)){
                array_push($image_src_map,$element['content']['src']);
                array_push($new_body,$element);
            }
        }else{
            array_push($new_body,$element);
        } 
    }
    $parsed_data['body']=$new_body;
    return $parsed_data;  
}
}