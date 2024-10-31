<?php
namespace NewsParserPlugin\Models;

use NewsParserPlugin\Entities\Factory\PostFactory;
use NewsParserPlugin\Entities\Post;
use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use WP_Query;


/**
 * Class creates post model as facade to wordpress functions.
 *
 *
 *
 * @package  Models
 * @author   Evgeniy S.Zalevskiy <2600@urk.net>
 * @license  MIT
 */

class PostModel
{
    
    protected $wpID;
    /**
     * The post factory instance.
     *
     * @var PostFactory
     */

    protected $postFactory;
    /**
     * Constructor.
     *
     * @param PostFactory $post_factory The post factory instance.
     */
    public function __construct(PostFactory $post_factory)
    {
        $this->postFactory = $post_factory;
    }
    /**
     * Create post instance from existed post data.
     *
     * @param string $id
     * @return false|Post
     */
    public function findByID($post_id)
    {
        if (is_null($wp_post = get_post($post_id))) {
            return false;
        }
        $post_meta=array_map(function($meta){
            return count($meta)>0?$meta[0]:false;
        },get_post_meta($post_id));
        $post_array = array_merge(array(
            'post_title' => $wp_post->post_title,
            'post_content' => $wp_post->post_content,
            'post_author' => $wp_post->post_author,
            '_image' => get_the_post_thumbnail_url($wp_post, 'full'),
            'ID' => absint($post_id),
        ),$post_meta);
        return $this->postFactory->create($wp_post,$post_meta);
    }
    /**
     * Finds WordPress posts with the specified meta key and value.
     *
     * @param string $meta_key The meta key to query.
     * @param string $meta_value The meta value to match.
     *
     * @return array An array of WordPress post objects or an empty array if none found.
     */
    public function findByMeta($meta_key, $meta_value,$exp_date=false)
    {
        global $post;
        $args = [
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'posts_per_page' => 9999,
            'post_status'=>'all'
            
        ];
        if($exp_date){
            $args['date_query']=array(
                array(
                    'after' => $exp_date
                )
            );
        }
        $query = new WP_Query($args);

        // Return an array of Post objects from the query results
        $posts = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_meta=array_map(function($meta){
                   return $meta[0];
                }, get_post_meta($post->ID));
                $posts[] = $this->postFactory->create($post, $post_meta);
            }
        }
        wp_reset_postdata();

        return $posts;
    }
    

    /**
     * Creates a WordPress post and returns details as an array.
     *
     * @param {object} post_data WordPress post attributes (see {@link https://developer.wordpress.org/reference/functions/wp_insert_post/}).
     * @param {object} [post_options_array={}] Additional post options to pass to `wp_insert_post`.
     * @param {string} [mode=''] Optional validation mode:
     *   - `'strict'`: Performs additional data validation (implementation dependent).
     *   - '': Skips additional validation.
     *
     * @returns {object|false}
     *   - On success, returns an instance of Post object with the following properties:
     *     - `link`: String, post permalink.
     *     - `authorId`: ID of the post author.
     *     - 'image' : String, post featured image URL.
     *     - `ID`: Number, WordPress post ID.
     *     - 'sourceUrl': String, post source URL.
     *     - `title`: String, post title.
     *     - `links`: Object with preview, edit, and delete links.
     *     - `status`: String, post status ('processing' or 'parsed').
     *   - On failure, returns `false`.
     *
     * @throws {MyException} If WP post creation fails or post data is invalid in strict mode.
     */

    public function create($post_array, $post_options_array, $post_meta = [], $mode = '')
    {
        if ($mode == 'strict') {
            $this->isDataValid($post_array);
        }

        $post_id = \wp_insert_post(array_merge($post_array, $post_options_array));

        if (\is_wp_error($post_id)) {
            throw new MyException($post_id->get_error_message(), Errors::code('BAD_REQUEST'));
        }
        $post_array['ID'] = $post_id;
        $this->updatePostMeta($post_id, $post_meta);
        $wp_post=get_post($post_id);
        return $this->postFactory->create($wp_post, $post_meta);
    }
    /**
     * Update wordpress post
     *
     * @param string $update_item name of updated field
     * @param mixed $data new data that will be add to the field
     * @return void
     */
    public function update(Post $post)
    {
        $post_array = [
            'ID' => $post->ID,
        ];
        $post_id = \wp_update_post($post->getPostAttributes());
        $post_meta = $post->getPostMetaAttributes();
        $this->updatePostMeta($post_id, $post_meta);
        return $post_id;
    }
    
    /**
     * Deletes a WordPress post.
     *
     * @param integer $post_id The ID of the post to delete.
     *
     * @return bool True if the post was deleted successfully, false otherwise.
     * @throws MyException If the post deletion fails.
     */
    public function delete($post_id)
    {
        if (!wp_delete_post($post_id, true)) {
           
            $error = wp_get_error_reason();
            throw new MyException($error, Errors::code('DELETE_FAILED'));
        }

        return true;
    }
    protected function isDataValid($post_data_array)
    {
        if (!isset($post_data_array['title']) || empty($post_data_array['title'])) {
            throw new MyException(Errors::text('NO_TITLE'), Errors::code('BAD_REQUEST'));
        }
        if (!isset($post_data_array['body']) || empty($post_data_array['body'])) {
            throw new MyException(Errors::text('NO_BODY'), Errors::code('BAD_REQUEST'));
        }
        if (!isset($post_data_array['authorId'])) {
            throw new MyException(Errors::text('NO_AUTHOR'), Errors::code('BAD_REQUEST'));
        }
    }
    protected function updatePostMeta($post_id, $post_meta)
    {
        if(!is_array($post_meta)) return false;
        foreach ($post_meta as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        return true;
    }
}
