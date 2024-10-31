<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;

/**
 * Modifier that iterates over an array of elements and generates the sizes attribute for the img tag.
 *
 * It modifies the 'sizes' attribute of image elements in the parsed body array.
 * The sizes attribute is generated based on a set of breakpoints and the image widths obtained from the 'srcSet' attribute of each image element.
 *
 * @param array $data ['body'=>array,'options'=>array] 
 * @return array 
 */
class AddImageSizesModifier implements MiddlewareInterface
{
function __invoke ($parsed_data,$options)
{
    if(!$options['extraOptions']['addSrcSetAndSizes']) return $parsed_data;
    
    $break_points_array=[480, 768, 1024, 1280, 1440, 1900];
    foreach($parsed_data['body'] as &$element){
        if($element['tagName']=='img'){
            if(array_key_exists('srcSet',$element['content'])&&$element['content']['srcSet']!==''){
                $src_set_array=explode(',',trim($element['content']['srcSet']));
                $sizes_array=array_map(function($src)use($break_points_array)
                    {
                        $image_width=(int)explode(' ',trim($src))[1];
                        foreach($break_points_array as $break_point){
                            if($image_width<$break_point){
                                return "(max-width: ".$break_point."px) ".$image_width."w";
                            }
                        }
                        return $image_width."w";
                    },$src_set_array); 
                    $sizes=implode(',',$sizes_array);
                    $element['content']['sizes']=$sizes;
                    
                }
            }
        }
    return $parsed_data;    
}
}