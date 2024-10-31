<?php
namespace NewsParserPlugin\Entities\Factory;
use NewsParserPlugin\Entities\Post;


/**
 * Class PostFactory
 *
 * This class is responsible for creating instances of the Post class.
 *
 * @package  Entities\Factory
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */

class PostFactory
{
    /**
     * Create a new instance of the Post class.
     *
     * @param array $post_array An array containing the post data.
     * @return Post The created Post instance.
     */
    public function create($wp_post,$post_meta)
    {
        /*
        $data = [
            'ID' => $post_object->ID,
            'post_title' => $post_object->post_title,
            'post_content' => $post_object->post_content,
            'post_author' => $post_object->post_author,
            'post_date' => $post_object->post_date,
            'post_status' => $post_object->post_status,
            'post_category' => $post_object->post_status,
            'tags_input' => $post_object->tags_input,
        ];
        */
        return new Post(array_merge($post_meta,get_object_vars($wp_post)));
    }
}
