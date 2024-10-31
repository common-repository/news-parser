<?php
namespace NewsParserPlugin\Models;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Interfaces\ModelInterface;
use NewsParserPlugin\Message\Errors;

/**
 * Class TemplateModel
 *
 * This class represents a model that operates with parsing options.
 *
 * @package  Models
 * @author   Evgeniy S.Zalevskiy <2600@urk.net>
 * @license  MIT
 */


class TemplateModel implements ModelInterface
{
    /**
     * The name of the template table.
     */

    protected const TEMPLATE_TABLE_NAME = NEWS_PURSER_PLUGIN_TEMPLATE_OPTIONS_NAME;
    
    /**
     * Save options using wp function update_option.
     * [url]
     * [template]
     *  Template patterns need to automatic parse data from the page.
     *   - [className]- HTML Class name of the tag that contain post content.
     *   - [tagName]- tag name of the tag that contain post content.
     *   - [searchTemplate] - search pattern to get node from DOM using HtmlDomParser.
     *   - [children] - array of child node elements that contain elements of the post data.
     *      Structure:
     *          - [tagName]-name of the tag? helps determine type of content e.g image, title etc.
     *          - [searchTemplate] -search pattern to get node from DOM using HtmlDomParser.
     *          - [position] - current value 'all'.
     * [extraOptions]
     *   - [addSrcSetAndSizes] - bool - add sizes attribute with image sizes breakpoints
     *   - [groupImagesRow] - bool - Groups images in Guttenberg group by two and arrange them in a row
     *   - [addFeaturedMedia]- bool- Add featured media to the post.
     *   - [addSource] -bool - Add link to the source page to th end of the post.
     *     [sourceLinkCaption]: string - caption of the link to the source page.
     *     [sourceLinkIsNoFollow]: bool - add rel="nofollow" to the link.
     * [postOptions]
     * [userID]
     * 
     * @throws MyException if options have wrong format.
     * @param string $resource_url
     * @param array $options
     * @return boolean
     */
    public function create($resource_url,$options)
    {
       return $this->update($resource_url,$options);
    }
    /**
     * Update options using wp function update_option.
     *
     * @throws MyException if options have wrong format.
     * @param string $resource_url
     * @param array $options
     * @return boolean
     *
     * @return bool Returns true if the options were successfully updated, false otherwise.
     */

    public function update($resource_url, $options)
    {
        if (!$this->isOptionsValid($options)) {
            throw new MyException(Errors::text('OPTIONS_WRONG_FORMAT'), Errors::code('BAD_REQUEST'));
        }
        $templates=$this->getAll();
        if(!is_array($templates)) $templates=[];
        $templates[$resource_url]=$options;
        return $this->updateOptions(self::TEMPLATE_TABLE_NAME, $templates, 'no');
    }
    /**
     * Delete options using wp function update_option.
     *
     * @param string $resource_url
     * @return bool Returns true if the options were successfully deleted, false otherwise.
     */

    public function delete ($resource_url){
        $templates=$this->getAll();
        if(array_key_exists($resource_url,$templates)){
            unset($templates[$resource_url]);
            return $this->updateOptions(self::TEMPLATE_TABLE_NAME, $templates, 'no');
        }
        return false;
    }
     /**
     * Find template by URL.
     *
     * @param string $url The URL of the template.
     * @return TemplateModel|false The template model if found, false otherwise.
     */
    public function findByID($url)
    {
        $templates = $this->getAll();
        if (array_key_exists($url, $templates)) {
            return $templates[$url];
        }
        return false;
    }
    protected function updateOptions($key,$data,$autoload=null)
    {
        return update_option($key, $data, $autoload);
    }
    /**
     * Delete function using wp delete_option.
     *
     * @return boolean
     */
    public static function deleteAll()
    {
        return delete_option(self::TEMPLATE_TABLE_NAME);
    }
    /**
     * Get saved options using wp get_option()
     *
     * @return false|array
     */
    public function getAll()
    {
        $result=get_option(self::TEMPLATE_TABLE_NAME);
        return is_array($result)?$result:[];
    }
    
    /**
     * Check if the options have a valid format.
     *
     * @param array $options The options to validate.
     *
     * @return bool Returns true if the options have a valid format, false otherwise.
     */
    protected function isOptionsValid($options)
    {

        if (empty($options)||!is_array($options)) {
            return false;
        }
        // Check if all required keys exist
        $required_keys = ['extraOptions', 'template', 'postOptions', 'userID', 'aiOptions' ];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $options)) {
                return false;
            }
        }
        return true;
    }
   
}
