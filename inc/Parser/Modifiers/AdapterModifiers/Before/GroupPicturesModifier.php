<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;
/**
 * Modifier  that iterates over an array of elements and groups consecutive 'img' elements into an 'imgRow' element.
 *
 * It takes an instance of the PostController class and modifies the parsed body array by grouping consecutive 'img' elements into an 'imgRow' element.
 * It returns a new array of body elements with consecutive 'img' elements grouped.
 *
 * @param array $data ['body'=>array,'options'=>array] 
 * @return array 
 */

class GroupPicturesModifier implements MiddlewareInterface
{
function __invoke ($parsed_data,$options)
{
    if(!$options['extraOptions']['groupImagesRow']) return $parsed_data;
    
    $new_body=[];
    $prev_element=null;
    foreach($parsed_data['body'] as $element){
        if($element['tagName']=='img'&&$prev_element['tagName']=='img'){
            $prev_image_element=array_pop($new_body);
            array_push($new_body,array(
                'tagName'=>'imgRow',
                'content'=>array($prev_image_element,$element)
            ));
        } else {
            array_push($new_body,$element);
        }
        $prev_element=end($new_body);
    }
    $parsed_data['body']=$new_body;
    return $parsed_data;  
}
}
