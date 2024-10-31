<?php
namespace NewsParserPlugin\Api\Rest;

use NewsParserPlugin\Interfaces\EventControllerInterface;
use NewsParserPlugin\Message\Success;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Traits\SanitizeDataTrait;
use NewsParserPlugin\Traits\ValidateDataTrait;
use NewsParserPlugin\Exception\MyException;

/**
 * Class saves received ai options options.
 *
 * PHP version 5.6
 *
 *
 * @package  Controller
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 *
 */

class PostApiEndpoint extends RestApiController

{

    /**
     * Event controller.
     *
     * @var EventControllerInterface
     */
    protected $event;
    /**
     * Instance of this class
     *
     * @var PostApiEndpoint
     */
    protected static $instance;

    /**
     * Methods to validate input data.
     *
     * @method validateUrl()
     * @method validateImageUrl()
     * @method validateMediaOptions()
     * @method validateExtraOptions()
     * @method validateTemplate()
     */
    use ValidateDataTrait;
    /**
     * Methods to sanitize input data.
     *
     * @method sanitizeMediaOptionsArray()
     * @method sanitizeExtraOptions()
     * @method sanitizeTemplate()
     */
    use SanitizeDataTrait;
    /**
     * Init method
     *
     * @param EventControllerInterface $event Controller factory instance.
     */
    protected function __construct(EventControllerInterface $event)
    {
        $this->event = $event;
        $this->init();
    }
    /**
     * Initializes the object by registering its routes as a REST API endpoint.
     *
     * @return void
     */
    protected function init()
    {
        
        add_action('rest_api_init', array($this,'register_routes'));

    }
    /**
     * Singleton static method to get instance of class.
     *
     * @param EventControllerInterface $event Controller factory instance.
     * @return AIOptionsApiController
     */
    public static function create(EventControllerInterface $event)
    {

        if (static::$instance) {
            return static::$instance;
        } else {
            static::$instance = new static($event);
            return static::$instance;
        }
    }

/**
 * Register the routes for the objects of the controller.
 */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'news-parser-plugin/v' . $version;
        $base = 'posts/parse';

        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => array($this, 'parsePost'),
                'permission_callback' => array($this, 'checkPermission'),
                'args' => array(
                    'url'=>array(
                        'description'=>'Parsing page url',
                        'type'=>'string',
                        'validate_callback'=>function ($url) {
                            return wp_http_validate_url($url);
                        },
                        'sanitize_callback'=>function ($input_url) {
                            return esc_url_raw($input_url);
                        }
                    ),
                    '_id'=>array(
                        'description'=>'Front end requested page index',
                        'type'=>'integer',
                        'validate_callback'=>function ($_id) {
                            preg_match('/[^0-9]/i', $_id, $matches);
                            if (empty($matches)) {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        'sanitize_callback'=>function ($_id) {
                            return preg_replace('/[^0-9]/i', '', $_id);
                        }
                    ),
                    'templateUrl'=>array(
                        'description'=>'Url that identifies template',
                        'type'=>'string',
                        'validate_callback'=>function ($url) {
                            return true;
                        },
                        'sanitize_callback'=>function ($input_url) {
                            return esc_url_raw($input_url);
                        }
                    )
                )
            )));

            $base = 'posts/create';

            register_rest_route($namespace, '/' . $base, array(
                array(
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'createPost'),
                    'permission_callback' => array($this, 'checkPermission'),
                    'args' => array(
                        'url'=>array(
                            'description'=>'Parsing page url',
                            'type'=>'string',
                            'validate_callback'=>function ($url) {
                                return wp_http_validate_url($url);
                            },
                            'sanitize_callback'=>function ($input_url) {
                                return esc_url_raw($input_url);
                            }
                        ),
                        '_id'=>array(
                            'description'=>'Front end requested page index',
                            'type'=>'integer',
                            'validate_callback'=>function ($_id) {
                                preg_match('/[^0-9]/i', $_id, $matches);
                                if (empty($matches)) {
                                    return true;
                                } else {
                                    return false;
                                }
                            },
                            'sanitize_callback'=>function ($_id) {
                                return preg_replace('/[^0-9]/i', '', $_id);
                            }
                        ),
                        'options'=>array(
                            'description'=>'Options for post creation',
                            'type'=>'array',
                            'validate_callback'=>function ($options) {
                                return $this->validateOptions($options);
                            },
                            'sanitize_callback'=>function ($options) {
                                return $this->sanitizeOptions($options);
                            }
                        ),
                        'parsedData'=>array(
                            'description'=>'Parsed data from page for post creation',
                            'type'=>'array',
                            'validate_callback'=>function ($parsed_data) {
                                return $this->validateParsedData($parsed_data);
                            },
                            'sanitize_callback'=>function ($parsed_data) {
                                return $this->sanitizeParsedData($parsed_data);
                            }
                        ),
                        'templateUrl'=>array(
                            'description'=>'Url that identifies template',
                            'type'=>'string',
                            'validate_callback'=>function ($url) {
                                return true;
                            },
                            'sanitize_callback'=>function ($input_url) {
                                return esc_url_raw($input_url);
                            }
                        )
                        // 'parsedData'
                        // 'options' - template format 
                    )
                )
                ));
        
            $base = 'posts/in-progress';

            register_rest_route($namespace, '/' . $base, array(
                    array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($this, 'postsInProgress'),
                        'permission_callback' => array($this, 'checkPermission'),
                        'args' => array(
                            'templateUrl'=>array(
                                'description'=>'Parsing page url',
                                'type'=>'string',
                                'validate_callback'=>function ($url) {
                                    return true;
                                },
                                'sanitize_callback'=>function ($input_url) {
                                    return esc_url_raw($input_url);
                                }
                            )
                        )
                    )
                )
            );
            $base = 'posts/data';

            register_rest_route($namespace, '/' . $base, array(
                    array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($this, 'postsData'),
                        'permission_callback' => array($this, 'checkPermission'),
                        'args' => array(
                            'templateUrl'=>array(
                                'description'=>'Parsing page url',
                                'type'=>'string',
                                'validate_callback'=>function ($url) {
                                    return true;
                                },
                                'sanitize_callback'=>function ($input_url) {
                                    return esc_url_raw($input_url);
                                }
                            ),
                            'post_id'=>array(
                                'description'=>'Parsing page url',
                                'type'=>'string',
                                'validate_callback'=>function ($post_id) {
                                    return true;
                                },
                                'sanitize_callback'=>function ($post_id) {
                                    return intval($post_id);
                                }
                            )
                        )
                    )
                )
            );
    }
 /**
     * Callback that handles parsing single page api requests and create WP post draft using saved parsing templates.
     * If there is no template for that domain name returns error.
     *
     * @uses EventController::trigger()
     * @return WP_REST_Response|WP_Error
     */
public function parsePost($request)
{

    try{
        $post_params=$request->get_params();
        $response=$this->event->trigger('posts:parse', array($post_params['url'],$post_params['_id'],$post_params['templateUrl']));
        $response_data=$this->formatResponse()->post($response)->message('success', sprintf(Success::text('POST_SAVED'), $response['title']))->addCustomData('_id', $request['_id'])->get('array');
        return $this->sendResponse($response_data);
    }catch(MyException $e){
        $error_data=$this->formatResponse()->error($e->getCode())->message('error', $e->getMessage())->get('array');
        $error_code=$e->getCode();
        $error_message=$e->getMessage();
        return $this->sendError($error_code,$error_message,$error_data);
    }
}
public function createPost ($request)
{
    try{
        $post_params=$request->get_params();
        $response=$this->event->trigger('posts:create', array($post_params['url'],$post_params['_id'],$post_params['parsedData'],$post_params['options'],$post_params['templateUrl']));
        $response_data=$this->formatResponse()->post($response)->message('success', sprintf(Success::text('POST_SAVED'), $response['title']))->addCustomData('_id', $request['_id'])->get('array');
        return $this->sendResponse($response_data);
    }catch(MyException $e){
        $error_data=$this->formatResponse()->error($e->getCode())->message('error', $e->getMessage())->get('array');
        $error_code=$e->getCode();
        $error_message=$e->getMessage();
        return $this->sendError($error_code,$error_message,$error_data);
    }

}

public function postsInProgress($request){
    try{
        $post_params=$request->get_params();
        $response=$this->event->trigger('posts:in-progress', array($post_params['templateUrl']));
        $response_data=$this->formatResponse()->options($response)->get('array');
        return $this->sendResponse($response_data);
    }catch(MyException $e){
        $error_data=$this->formatResponse()->error($e->getCode())->message('error', $e->getMessage())->get('array');
        $error_code=$e->getCode();
        $error_message=$e->getMessage();
        return $this->sendError($error_code,$error_message,$error_data);
    }
}
public function postsData($request){
    try{
        $post_params=$request->get_params();
        $response=$this->event->trigger('posts:data', array($post_params['templateUrl'],$post_params['post_id']));
        $response_data=$this->formatResponse()->options($response)->get('array');
        return $this->sendResponse($response_data);
    }catch(MyException $e){
        $error_data=$this->formatResponse()->error($e->getCode())->message('error', $e->getMessage())->get('array');
        $error_code=$e->getCode();
        $error_message=$e->getMessage();
        return $this->sendError($error_code,$error_message,$error_data);
    }
}
}