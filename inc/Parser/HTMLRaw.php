<?php
namespace NewsParserPlugin\Parser;

/**
 * Get not parsed HTML data
 *
 * PHP version 5.6
 *
 *
 * @package  Parser
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */

class HTMLRaw extends Abstracts\AbstractParseContent
{
    /**
     * Init function.
     *
     * @param int $cache_expiration Cache expiration value in seconds.
     */
    public function __construct($cache_expiration = 600)
    {
        $this->url='http://site.com';
        parent::__construct($cache_expiration);
    }
    /**
     * Get page raw html data.
     *
     * @param string $html Html raw data.
     * @return string
     */
    protected function parse($html)
    {
       
        $html=apply_filters('NewsParserPlugin\Parser\HTMLRaw:parse:parse',$html,$this->url);
       
        if (is_array($this->options)&&array_key_exists('remove_scripts', $this->options)&&$this->options['remove_scripts']) {
            return $this->removeScriptTags($html);
        }
        return $html;
    }
}
