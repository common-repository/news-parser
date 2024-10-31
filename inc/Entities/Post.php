<?php
namespace NewsParserPlugin\Entities;

use NewsParserPlugin\Exception\MyException;
/**
 * Class Post
 *
 * This class represents a post entity, containing information related to a post.
 *
 * @package  Entities
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */

class Post
{
 /**
     * Title of the post.
     *
     * @var string
     */
    public $post_title;
    /**
     * Post content.
     *
     * @var string
     */
    public $post_content;
    /**
     * Featured image url.
     *
     * @var string|false
     */
    public $_image;
    /**
     * Status of the post could be parsed|processing
     *
     * @var string
     */
    public $_parsing_status;
    public $post_status;
    public $post_category=[];
    public $tags_input=[];
    /**
     * Url of source post.
     *
     * @var string|false
     */
    public $_source_url;
    /**
     * Id of wordpress post.
     *
     * @var int
     */
    public $ID;
    /**
     * Array of links.
     * Structure: [previewLink,editLink,deleteLink]
     *
     * @var array
     */
    public $links = array();
    /**
     * Wordpress authoe id.
     *
     * @var string
     */
    public $authorId;
    /**
     * Init function
     *
     * @throws MyException if post have no title
     *
     * @param array $data structure['title','body','image','sourceUrl','authorId']
     */
    public function __construct($post_data_array)
    {
        
        $this->post_title = $post_data_array['post_title'];
        $this->post_date= $post_data_array['post_date'];
        $this->post_content = $post_data_array['post_content'];
        $this->post_author = $post_data_array['post_author'];
        $this->_image = isset($post_data_array['_image'])?$post_data_array['_image']:false;
        $this->_source_url = isset($post_data_array['_source_url'])?$post_data_array['_source_url']:false;
        $this->ID=$post_data_array['ID'];
        $this->_parsing_status = array_key_exists('_parsing_status',$post_data_array)?$post_data_array['_parsing_status']:null;
        $this->_template_id = array_key_exists('_template_id',$post_data_array)?$post_data_array['_template_id']:null;
        $this->links=$this->getPostLinksWordpress($this->ID);
        $this->post_status = array_key_exists('post_status',$post_data_array)?$post_data_array['post_status']:'draft';
        $this->post_category = array_key_exists('post_category',$post_data_array)?$post_data_array['post_category']:[];
        $this->tags_input = array_key_exists('tags_input',$post_data_array)?$post_data_array['tags_input']:[];
        
    }
    /**
     * Attach main image to wordpress post
     *
     * @param null|string $image_url
     * @param string $alt
     * @return string|int Id of saved post featured media.
     */
    public function addPostThumbnail($image_url = null, $alt = '')
    {
        $url=is_null($image_url)?$this->_image:$image_url;
        $featured_image_title=!empty($alt)?$alt:$this->post_title;
        return $this->attachImageToPostWordpress($url, $this->ID, true, $featured_image_title);
    }

  
    /**
     * Return Post data in array|json|object format
     *
     * @param string $format
     * @return array|string|object
     */
    public function getFormatedAttributes($map=['post_author'],$format = 'array')
    {
        $data_array = [];
        foreach ($map as $map_key=>$local_key) {
            $data_array[$map_key] = $this->$local_key;
        }
        $data_json = json_encode($data_array);
        switch ($format) {
            case 'json':
                return $data_json;
            case 'object':
                return json_decode($data_json);
            default:
                return $data_array;
        }
    }
    /**
     * Return Post data in array|json|object format
     *
     * @param string $format
     * @return array|string|object
     */
    public function getPostAttributes($format = 'array')
    {
        $data_array = array(
            'post_author'=>$this->post_author,
            'post_title'=>$this->post_title,
            'post_content' => $this->post_content,
            'post_status' => $this->post_status,
            'post_category' => $this->post_category,
            'tags_input' => $this->tags_input,
            'ID' => $this->ID,
        );
        $data_json = json_encode($data_array);
        switch ($format) {
            case 'json':
                return $data_json;
            case 'object':
                return json_decode($data_json);
            default:
                return $data_array;
        }
    }
    public function getPostMetaAttributes($format = 'array')
    {
        $data_array = array(
            '_parsing_status'=>$this->_parsing_status,
            '_source_url'=>$this->_source_url,
            '_template_id'=>$this->_template_id,
        );
        $data_json = json_encode($data_array);
        switch ($format) {
            case 'json':
                return $data_json;
            case 'object':
                return json_decode($data_json);
            default:
                return $data_array;
        }
    }
    /**
     * Facade function for WP media_sideload_image
     *
     * @param string $file
     * @param integer|string $post_id
     * @param string $desc
     * @param string $return
     * @return string|\WP_Error
     */
    public function mediaSideloadImage($file, $post_id = 0, $desc = null, $return = 'html')
    {
        if(function_exists('media_sideload_image')){
            return \media_sideload_image($file, $post_id, $desc, $return);
        } 
        /** WordPress Administration File API */
        require_once ABSPATH . 'wp-admin/includes/file.php';

        /** WordPress Image Administration API */
        require_once ABSPATH . 'wp-admin/includes/image.php';

        /** WordPress Media Administration API */
        require_once ABSPATH . 'wp-admin/includes/media.php';
        return \media_sideload_image($file, $post_id, $desc, $return);
    }
    public function update($post_data_array)
    {
        foreach ($post_data_array as $prop => $value) {
            if(property_exists($this, $prop)){
                $this->$prop = $value;
            }
        }
    }
    /**
     * Download and attach image to WP post
     *
     * @param string $image url of image
     * @param int $id post ID in WP
     * @param boolean $post_thumb if image will use NewsParserPlugin\as main image of the post
     * @param int $max_attempt counts of downloads attempts
     * @return WP_Error|string  image ID
     */
    protected function attachImageToPostWordpress($image, $id, $post_thumb = false, $alt = '',$max_attempt=3)
    {
        $url = $image;
        $post_id = $id;
        $desc = $alt?:"image";
        for ($attempt = 0;$attempt<$max_attempt;$attempt++){
            $img_id = $this->mediaSideloadImage($url, $post_id, $desc, 'id');
            if(!\is_wp_error($img_id)) break;
        }
        if (\is_wp_error($img_id)) {
            throw new MyException($img_id->get_error_message().' Image url:'.esc_url_raw($url), Errors::code('BAD_REQUEST'));
        } else {
            if ($post_thumb) {
                \set_post_thumbnail($post_id, $img_id);
            }

            return $img_id;
        }
    }
   /**
     * Get links to the saved WP post
     *
     * @return void
     */
    protected function getPostLinksWordpress($post_id)
    {
        return [
            'previewLink'=> \get_post_permalink($post_id),
            'editLink' => \get_edit_post_link($post_id, ''),
            'deleteLink' => \get_delete_post_link($post_id)
        ];
    }
}