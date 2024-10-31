<?php
namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;
use NewsParserPlugin\Models\TemplateModel;
use NewsParserPlugin\Utils\ResponseFormatter;
use NewsParserPlugin\Interfaces\ModelInterface;

/**
 *
 * Class saves received options.
 *
 * @package  Controller
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */
class TemplateController
{
    protected const TEMPLATE_TABLE_NAME = NEWS_PURSER_PLUGIN_TEMPLATE_OPTIONS_NAME;
     /**
     * @var TemplateModel Template model
     */
    protected $templateModel;
    /**
     * Init function.
     *
     * @param ModelInterface $template_model
     */
    public function __construct(ModelInterface $template_model)
    {
        $this->templateModel=$template_model;
    }
    /**
     * Save received options.
     *
     * @param array $options The received options.
     * @return TemplateModel  created template model.
     */
    public function create($options)
    {
         $current_user_id=$this->getUserId($options);
         $options['userID']=$current_user_id;
         $template = $this->templateModel->create($options['url'],$options); 
         return $template;
    }

    /**
     * Get template by URL.
     *
     * @param string $url The URL of the template.
     * @return array|false The array of  template model attributes if found, false otherwise.
     */
    public function get($url)
    {
        $template_data=$this->templateModel->findByID($url);
        if(is_array($template_data)){
            unset($template_data['userID']);
        }
        return $template_data;
    }

    /**
     * Get all template keys.
     *
     * @return array The array of template keys.
     */
    public function templateKeys()
    {
        return array_keys($this->templateModel->getAll());
    }
    /**
     * Delete template by URL.
     *
     * @param string $url The URL of the template to delete.
     * @return void
     */
    public function delete($url)
    {
        $template = $this->templateModel->delete($url);
        return $this->templateKeys();
    }
    
    /**
     * Get the user ID.
     *
     * This method retrieves the ID of the current user. 
     *
     * @return int|false|null The user ID if the current user has the capability to publish posts and the user ID is an integer,
     *                        false if the current user does not have the capability, or null if the user ID could not be determined.
     * @throws \MyException An exception with an error message and code if the current user does not have the capability to publish posts.
     */

    protected function getUserId()
    {
        $current_user_id=get_current_user_id();
        if (!current_user_can( 'publish_posts' )) {
            return new \MyException(Errors::text('NO_RIGHTS_TO_PUBLISH'),Error::code('BAD_REQUEST'));
            }

        if(is_int($current_user_id)){
            return $current_user_id;
        }
        return false;
    }
    
    
}
