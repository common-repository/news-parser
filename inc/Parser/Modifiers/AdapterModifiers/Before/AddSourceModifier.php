<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\PostControllerInterface;
use NewsParserPlugin\Interfaces\MiddlewareInterface;

/**
 * Modifier  that adds a source link to the post.
 *
 * It takes a PostController object and adds a source link to the associated PostModel object.
 * The source link is generated using the 'sourceUrl' property from the parsed data retrieved using the `getParsedData()` method of the PostController class.
 * The generated source link is then added as a new paragraph ('p') element to the body array using the `updateParsedDataBody()` method of the PostController class.
 *
 * @param array $data ['body'=>array,'options'=>array] 
 * @return array
 */

class AddSourceModifier implements MiddlewareInterface
{
function __invoke ($parsed_data,$options)
{
    if(!$options['extraOptions']['addSource']) return $parsed_data;
    $extra_options=$options['extraOptions'];
    $source_link_caption=(array_key_exists('sourceLinkCaption',$extra_options)&&$extra_options['sourceLinkCaption']!=='')?$extra_options['sourceLinkCaption']:'Source';
    $anchor_rel_attr=(array_key_exists('sourceLinkIsNoFollow',$extra_options)&&$extra_options['sourceLinkIsNoFollow']==true)?'nofollow':'dofollow';
    $source_link_element=[
        'tagName'=>'source',
        'content'=>[
            'href'=>$parsed_data['sourceUrl'],
            'text'=>$source_link_caption,
            'rel'=>$anchor_rel_attr
        ]
    ];
    array_push($parsed_data['body'],$source_link_element);
    return $parsed_data; 
}
}