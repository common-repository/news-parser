<?php
namespace NewsParserPlugin\Core;

use NewsParserPlugin\Api\Ajax\AjaxApiEndpoint;
use NewsParserPlugin\Controller\MiddlewareController;
use NewsParserPlugin\Controller\EventController;
use \ContainerBuilder\Interfaces\ContainerInterface as ContainerInterface;
use NewsParserPlugin\Utils\ResponseFormatter;
use NewsParserPlugin\Controller\CronController;
use NewsParserPlugin\Api\Rest\AIOptionsApiEndpoint;
use NewsParserPlugin\Api\Rest\TemplateApiEndpoint;
use NewsParserPlugin\Api\Rest\CronApiEndpoint;
use NewsParserPlugin\Api\Rest\AIApiEndpoint;
use NewsParserPlugin\Api\Rest\PostApiEndpoint;
use NewsParserPlugin\Models\CronDataModel;

class App{
    protected $ajaxApiEndpoint;
    public $middleware;
    public $DI_container;
    public $event;
    public $templateApiEndpoint;
    public $aiOptionsApiEndpoint;
    public $cronApiEndpoint;
    public $cronTaskController;
    static protected $instance=null;
    protected function __construct(ContainerInterface $DI_container){
        $this->DI_container=$DI_container;
        $this->event=$this->DI_container->get(\NewsParserPlugin\Controller\EventController::class);
        $this->ajaxApiEndpoint=AjaxApiEndpoint::create($this->event);
        $this->templateApiEndpoint=TemplateApiEndpoint::create($this->event);
        $this->cronApiEndpoint=CronApiEndpoint::create($this->event);
        $this->aiOptionsApiEndpoint=AIOptionsApiEndpoint::create($this->event);
        $this->aiApiEndpoint=AIApiEndpoint::create($this->event);
        $this->postApiEndpoint=PostApiEndpoint::create($this->event);
        $this->middleware=MiddlewareController::getInstance($this->event);
        
    }
    static public function start(ContainerInterface $DI_container){
        if(self::$instance==null){
            self::$instance=new self( $DI_container);
            return self::$instance;
        }
        return self::$instance;
    }
}