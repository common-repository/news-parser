<?php

namespace NewsParserPlugin\Controller;

use NewsParserPlugin\Entities\Post;
use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Interfaces\AdapterInterface;
use NewsParserPlugin\Interfaces\ModelInterface;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Models\PostCacheModel;
use NewsParserPlugin\Models\PostModel;
use NewsParserPlugin\Models\TemplateModel;
use NewsParserPlugin\Parser\Abstracts\AbstractParseContent;

/**
 * Class controller for post parsing.
 *
 *
 * @package  Controller
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */
class PostController
{
    protected const MAX_TIME_TO_PROCESS = 10 * 10;
    public const PARSING_STATUS_PARSED = 'parsed';
    public const PARSING_STATUS_PROCESSING = 'processing';

    /**
     * @var AbstractParseContent Parser object
     */
    public $parser;
    /**
     * @var AdapterInterface Adapter object
     */
    protected $templateModel;
    /**
     * @var PostModel Post model
     */
    protected $postModel;


    public function __construct(AbstractParseContent $parser, AdapterInterface $adapter, ModelInterface $template_model, PostModel $post_model)
    {
        $this->adapter = $adapter;
        $this->parser = $parser;
        $this->templateModel = $template_model;
        $this->postModel = $post_model;
        
    }
    /**
     *  Method that handels post parsing request.
     * 
     * @param string $url The url of the post.
     * @param string $_id Frontend id of the post.
     * @param string $template_url The url of the template.
     * @return array|false|Post
     */
    public function parsePost($url, $_id, $template_url)
    {

        list($post_options, $post_template_options) = $this->getPostTemplateOptions($template_url);
        $post_draft = $this->createPostDraft($url, $template_url);
        try {
            $parsed_data = $this->parser->get($url, $post_template_options);
            $processed_data = $this->processPostParsedData($parsed_data, $post_template_options, $url);
            $parsed_post = array_merge(
                $this->update($post_draft, $processed_data, $post_options, $post_template_options), [
                    '_id' => $_id,
                ]);
            return $parsed_post;
        } catch (MyException $e) {
            $this->postModel->delete($post_draft->ID);
            throw new MyException($e->getMessage(), $e->getCode());
        }
    }
    /**
     * Method that handels post parsing request from CronController.
     * 
     * @param string $url The url of the post.
     * @param string $template_url The url of the template.
     * @return array|false|Post
     */

    public function autopilotParsePost($url, $template_url = false)
    {
        $parsed_url = parse_url($url);
        if (!is_array($parsed_url)) {
            throw new MyException(Errors::text('WRONG_OPTIONS_URL'), Errors::code('BAD_REQUEST'));
        }
        list($post_options, $post_template_options) = $this->getPostTemplateOptions($template_url);
        $parsed_data = $this->parser->get($url, $post_template_options);
        $processed_data = $this->processPostParsedData($parsed_data, $post_template_options, $url);
        $post_draft = $this->createPostDraft($url, $template_url, $post_template_options['userID']);
        return $this->update($post_draft, $processed_data, $post_options, $post_template_options);
    }
    /**
     * Method create post form given parsed data.
     * 
     * @param string $url The url of the post.
     * @param string $_id Frontend id of the post.
     * @param array $parsed_data The parsed data.
     * @param array $options The post options.
     * @param array $template_options The template options.
     * @return array|false|Post
     */
    public function createPostFromParsedData($url, $_id, $parsed_data, $options, $template_url)
    {
        $post_draft = $this->createPostDraft($url, $template_url);
        try {
            $processed_data = $this->processPostParsedData($parsed_data, $options, $url);
            $parsed_post = array_merge($this->update($post_draft, $processed_data, $options['postOptions'], $options), [
                '_id' => $_id,
            ]);
            return $parsed_post;
        } catch (MyException $e) {
            $this->postModel->delete($post_draft->ID);
            throw new MyException($e->getMessage(), $e->getCode());
        }

    }
    /**
     * Handle posts in progerss requests.
     * 
     * @param string $template_url The url of the template.
     * @param string $post_url The url of the post.
     * @return array|false|Post
     */
    public function getPostsInProgress($template_url, $post_url = null)
    {
        $desired_format = "Y-m-d H:i:s";
        // $expiration_date=strtotime(end($list_data)->pubDate);
        $current_timestamp = time();
        $expiration_date = date($desired_format, $current_timestamp - 24 * 60 * 60);
        $posts = $this->postModel->findByMeta('_parsing_status', self::PARSING_STATUS_PROCESSING);
        $filtered_posts = array_filter($posts,
            function ($post) use ($current_timestamp) {
                $post_timestamp = strtotime($post->post_date);
                if ($post->_parsing_status == self::PARSING_STATUS_PROCESSING) {
                    if ($current_timestamp - $post_timestamp > self::MAX_TIME_TO_PROCESS) {
                        $this->postModel->delete($post->ID);
                        return false;
                    }
                    return true;
                }
            }
        );

        return array_map(function ($post) {

            return $post->getFormatedAttributes([
                'sourceUrl' => '_source_url',
                'post_id' => 'ID',
            ]);
        }, $filtered_posts);
    }
    public function getPostsData($template_url, $post_id)
    {
        $post = $this->postModel->findById($post_id);
        if (!$post) {
            return [
                'error' => Errors::text('POST_WAS_NOT_CREATED'),
            ];
        }
        if ($post->_parsing_status == self::PARSING_STATUS_PROCESSING) {
            return false;
        } else if ($post->_parsing_status == 'parsed') {
            return $post->getFormatedAttributes([
                'sourceUrl' => '_source_url',
                'post_id' => 'ID',
            ]);
        }
    }

    protected function update(Post $post, $post_update, $post_options = [], $post_template_options = [])
    {
        $post->update(array_merge(
            $this->formatPostData($post_update),
            $this->formatPostOptions($post_options),
            $this->formatPostMeta([
                'status' => self::PARSING_STATUS_PARSED,
                'image' => $post_update['image'],
            ])
        ));
        $post_id = $this->postModel->update($post);
        // Apply modifiers to post according to template post options
        if ($post_id !== false) {
            \apply_filters('NewsParserPlugin\Controller\PostController:post', $post, $post_template_options);
        }
        return $post->getFormatedAttributes([
            'post_author' => 'post_author',
            'link' => '_source_url',
            'title' => 'post_title',
            'links' => 'links',
            'status' => '_parsing_status',
            'post_id' => 'ID',
        ]);
    }

    /**
     * Apply body adapter to parsed data
     */
    protected function applyBodyAdapter($parsed_data, $options)
    {
        $parsed_data = \apply_filters('NewsParserPlugin\Controller\PostController\parsedData:adapterBefor', $parsed_data, $options);
        $parsed_data['body'] = $this->adapter->convert($parsed_data['body']);
        $parsed_data['body'] = \apply_filters('NewsParserPlugin\Controller\PostControllerp\arsedData\body:adapterAfter', $parsed_data['body'], $options);
        return $parsed_data;
    }
    protected function formatPostData($parsed_data)
    {
        $formated_post_data = [];
        if (array_key_exists('title', $parsed_data)) {
            $formated_post_data['post_title'] = \wp_strip_all_tags($parsed_data['title']);
        }

        if (array_key_exists('body', $parsed_data)) {
            $formated_post_data['post_content'] = $parsed_data['body'];
        }

        if (array_key_exists('authorId', $parsed_data)) {
            $formated_post_data['post_author'] = $parsed_data['authorId'];
        }

        return $formated_post_data;
    }
    protected function formatPostMeta($options)
    {
        $formated_post_meta = [];
        if (array_key_exists('status', $options)) {
            $formated_post_meta['_parsing_status'] = $options['status'];
        }

        if (array_key_exists('image', $options)) {
            $formated_post_meta['_image'] = $options['image'];
        }

        if (array_key_exists('sourceUrl', $options)) {
            $formated_post_meta['_source_url'] = $options['sourceUrl'];
        }

        if (array_key_exists('templateID', $options)) {
            $formated_post_meta['_template_id'] = $options['templateID'];
        }

        return $formated_post_meta;
    }
    /**
     * Get the post options model based on the given options ID
     *
     * @param string $options_id ID of the template options.
     * @return TemplateModel
     * @throws MyException If no extra options or post options are available.
     */
    protected function getPostTemplateOptions($options_id)
    {
        $template_options = $this->templateModel->findByID($options_id);
        return [$template_options['postOptions'], $template_options];
    }

    protected function processPostParsedData($parsed_data, $options, $url)
    {
        $parsed_data['sourceUrl'] = $url;
        $this->validateParsedData($parsed_data);

        // Apply adapter to adapt parsed body of the post to editor or make changes according to options
        return $this->applyBodyAdapter($parsed_data, $options);
    }
    /**
     * Assign the current user's ID as the author ID
     */
    protected function currentUserID()
    {
        return apply_filters('news_parser_filter_author_id', \get_current_user_id());
    }

    protected function validateParsedData($parsed_data)
    {
        if (!array_key_exists('title', $parsed_data) || empty($parsed_data['title'])) {
            throw new MyException(Errors::text('NO_TITLE'), Errors::code('INNER_ERROR'));
        }
        if (!array_key_exists('body', $parsed_data) || empty($parsed_data['body'])) {
            throw new MyException(Errors::text('NO_BODY'), Errors::code('INNER_ERROR'));
        }
        if (!array_key_exists('authorId', $parsed_data) || $parsed_data['authorId'] == '') {
            // throw new MyException(Errors::text('NO_AUTHOR'),Errors::code('INNER_ERROR'));
        }
        if (!array_key_exists('sourceUrl', $parsed_data) || empty($parsed_data['sourceUrl'])) {
            throw new MyException(Errors::text('NO_POST_URL'), Errors::code('INNER_ERROR'));
        }

    }

    protected function createPostDraft($url, $template_url, $post_author = null)
    {
        return $this->postModel->create($this->formatPostData([
            'authorId' => $post_author == null ? $this->currentUserID() : $post_author,
            'body' => 'Processing...',
            'title' => 'Processing...',
        ]),
            [],
            $this->formatPostMeta([
                'sourceUrl' => $url,
                'status' => self::PARSING_STATUS_PROCESSING,
                'templateID' => $template_url,
            ]));
    }

    protected function formatPostOptions($post_options)
    {
        $formated_post_options = [];
        if (isset($post_options['status'])) {
            $formated_post_options['post_status'] = $post_options['status'];
        }
        if (isset($post_options['categories'])) {
            $formated_post_options['post_category'] = $post_options['categories'];
        }
        if (isset($post_options['tags'])) {
            $formated_post_options['tags_input'] = $post_options['tags'];
        }
        return $formated_post_options;
    }
}
