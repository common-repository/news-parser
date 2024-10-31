<?php

namespace NewsParserPlugin\Modifiers;

use NewsParserPlugin\Interfaces\MiddlewareInterface;
/**
 * Modify HTML. Remove line breaks.
 *
 * PHP version 5.6
 *
 *
 * @package  Modifiers
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */
class RemoveLineBreaks implements MiddlewareInterface
{
    /**
     * Remove line breaks using regexp.
     */
    protected function regexpRemoveBreak(string $data){
        return \preg_replace('/\n/i','',$data);
    }
    /**
     * Call methods and path them html data.
     * 
     * @param string $$html
     * @return string 
     */
    public function __invoke($html,$args=null){
        return $this->regexpRemoveBreak($html);
    }
}