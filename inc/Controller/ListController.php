<?php
namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Parser\Abstracts\AbstractParseContent;
use NewsParserPlugin\Models\PostModel;


/**
 * Class creates and formats list from RSS feed
 *
 *
 *
 * @package  Controller
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */

class ListController 
{
    /**
     * Instance of list parser.
     *
     * @var AbstractParseContent
     */
    protected $parser;
    protected $postModel;
    /**
     * Init function.
     *
     * @param AbstractParseContent $parser
     */
    public function __construct(AbstractParseContent $parser, PostModel $post_model)
    {
        $this->parser = $parser;
        $this->postModel=$post_model;
    }
    /**
     * Get formated list of posts.
     *
     * @uses NewsParserPlugin\Parser\Abstracts\AbstractParseContent::get()
     * @param string $url Url of the RSS source.
     * @return array
     */
    public function get($url)
    {
        $list_data = $this->parser->get($url);
        if ($this->isListDataValid($list_data)) {
            throw new MyException(Errors::text('WRONG_LIST_FORMAT'), Errors::code('BAD_REQUEST'));
        }
        
        
        $desired_format = "Y-m-d H:i:s";
       // $expiration_date=strtotime(end($list_data)->pubDate);
        $expiration_date = date($desired_format, strtotime(end($list_data)->pubDate));
        $cached_data=$this->postModel->findByMeta('_template_id',$url,$expiration_date);
        //$cached_data=$this->postCache->removeOldPosts($cached_data,$expiration_date,$expiration_date);
        //$cached_data=$this->postCache->removeErrorPosts($cached_data);
        //$this->postCache->set('created',$cached_data,$url);
        $parsed_posts=array_filter($cached_data,function($post){
            return $post->_parsing_status!=PostController::PARSING_STATUS_PROCESSING;
        });
        $cached_posts_links=array_map(function($post){
            return $post->_source_url;
        },$parsed_posts);
        return array_map(function($post) use ($cached_data,$cached_posts_links){
            if(($post_index=array_search($post->link,$cached_posts_links))!==false){
              return array_merge($this->toArray($post),$this->formatPostDraftData($cached_data[$post_index]));
            }
            return $this->toArray($post);
        },$list_data);
    }
    /**
     * $list_data  Structure:
     * [title] - title of post
     * [pubDate] -date of post publication
     * [description] -post brief description
     * [link] - link to the original post
     * [image] - main post image url
     * [status] - status of post parsed - if post was not saved as draft and draft -when post saved as draft
     * 
     * @param array $list_data 
     * @return bool
     */
    public function isListDataValid($list_data)
    {
        if (empty($list_data)||!is_array($list_data)) {
            return false;
        }
        // Check if all required keys exist
        $required_keys = ['title', 'pubDate', 'description', 'link', 'image', 'status'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $list_data)) {
                return false;
            }
        }
        return true;
    }
    protected function toArray($post){
        return json_decode(json_encode($post),true);
    }
    protected function formatPostDraftData($post){
        if(!$post) return [];
        return [
            'post_id'=>$post->ID,
            'draft'=>[
                'editLink'=>$post->links['editLink'],
            ]
        ];
    }
    
}
