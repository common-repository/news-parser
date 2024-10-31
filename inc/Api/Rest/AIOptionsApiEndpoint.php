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

class AIOptionsApiEndpoint extends RestApiController

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
     * @var AIOptionsApiController
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
        $base = 'ai-options';

        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'getAIOptions'),
                'permission_callback' => array($this, 'checkPermission'),
            )
            ));
    }
/**
 * Get AI options options.
 *
 * @param WP_REST_Request $request Full data about the request.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
 */
public function getAIOptions($request){

    try{
        $crons_data=$this->event->trigger('ai-options:get', array());
        $response_data=$this->formatResponse()->message('success', null)->options($crons_data)->get('array');
        return $this->sendResponse($response_data);
    }catch(MyException $e){
        $error_data=$this->formatResponse()->error($e->getCode())->message('error', $e->getMessage())->get('array');
        $error_code=$e->getCode();
        $error_message=$e->getMessage();
        return $this->sendError($error_code,$error_message,$error_data);
    }
}

}