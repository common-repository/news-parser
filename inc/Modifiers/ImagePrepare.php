<?php

namespace NewsParserPlugin\Modifiers;

use NewsParserPlugin\Interfaces\MiddlewareInterface;
/**
 * Modify HTML. Take source path to pictures from attributes and set it to src attribute of img tag.
 *
 * PHP version 5.6
 *
 *
 * @package  Modifiers
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */
class ImagePrepare implements MiddlewareInterface
{  
    /**
     * Get path to image from data-src attribute and set it to the src attribute.
     * 
     * @param string $data
     * @return string 
     */
    protected function dataSrc(string $html){
        return \preg_replace_callback('/(<img.*?>)/i',
            function($matches){
                if(preg_match('/data-src=(\'|\")(.*?)(\'|\")/i',$matches[1],$path)===1){
                    return preg_replace('/data-src=(\'|\")(.*?)(\'|\")/i','src="'.$path[2].'"',$matches[1]);
                } 
                return $matches[1];
            },
            $html);
    }
    /**
     * Get data from data-srcset of source tag and set it to srcset attribute.
     * 
     * @param string $data
     * @return string 
     */
    protected function pictureTag(string $html){
        return \preg_replace_callback('/(<picture.*?>.*?<\/picture>)/i',
            function($matches){
                if(preg_match('/data-srcset=(\'|\")(.*?)(\'|\")/i',$matches[1],$path)===1){
                    return preg_replace('/data-srcset=(\'|\")(.*?)(\'|\")/i','srcset="'.$path[2].'"',$matches[1]);
                } 
                return $matches[1];
            },
        $html);       
    }

    /**
     * Call methods and path them html data.
     * 
     * @param string $html 
     * @param string $url
     * @return array 
     */
    public function __invoke($html,$url){
        $html=$this->dataSrc($html);
        return $this->pictureTag(($html));
    }
    
}