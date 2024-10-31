<?php
namespace NewsParserPlugin;

use NewsParserPlugin\Core\ScriptLoadingManager;
use NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before\RemoveDublicatedPicturesModifier;
use NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before\AddImageSizesModifier;
use NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before\RemoveSrcSetAndSizesModifier;
use NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before\GroupPicturesModifier;
use NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before\AddSourceModifier;
use NewsParserPlugin\Parser\Modifiers\PostModifiers\AddPostThumbnailModifier;

function news_parser_init(){
    
    // Dependency Injection container initialization

    $container=new \ContainerBuilder\DI();
    $container->addDefinitions(NEWS_PARSER_PLUGIN_DIR.'inc/Config/di-config.php');
    
   
    // Load script, style, and global variable configurations
   if(NEWS_PARSER_PLUGIN_MODE&&NEWS_PARSER_PLUGIN_MODE=='development'){
      $scripts_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/scripts-config-dev.php';
      $styles_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/styles-config-dev.php';
      $global_variables_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/global-variables-config-dev.php';
   }else if(NEWS_PARSER_PLUGIN_MODE=='production'){
      $scripts_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/scripts-config.php';
      $styles_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/styles-config.php';
      $global_variables_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/global-variables-config.php'; 
   }
   $scripts_translation_config= include NEWS_PARSER_PLUGIN_DIR.'inc/Config/scripts-translation-config.php';
    $app=Core\App::start($container);

    // Initialize the ScriptLoadingManager

    $loading_manager=ScriptLoadingManager::getInstance($container->get(\NewsParserPlugin\Menu\Admin\MenuPage::class),$container->get(\NewsParserPlugin\Utils\MenuConfig::class));
    $loading_manager->setScriptsConfig($scripts_config);
    $loading_manager->setStylesConfig($styles_config);
    $loading_manager->setGlobalVariablesConfig($global_variables_config);
    $loading_manager->setScriptsTranslationConfig($scripts_translation_config);
    $loading_manager->init();

    // Set up modifiers middleware for html parser
   

    $app->middleware->add('NewsParserPlugin\Parser\HTMLRaw:parse:parse',array(
      $app->DI_container->get(Modifiers\RemoveLineBreaks::class),
      $app->DI_container->get(Modifiers\ReplaceRelativePathWithAbsolute::class),
      $app->DI_container->get(Modifiers\ImagePrepare::class)
   ));

    
    $app->middleware->add('NewsParserPlugin\Controller\PostController\parsedData:adapterBefor',array(
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\RemoveDublicatedPicturesModifier::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\GenerateImageWithAI::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\GenerateTitleWithAI::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\GeneratePostBodyWithAI::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\AddImageSizesModifier::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\RemoveSrcSetAndSizesModifier::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\GroupPicturesModifier::class),
      $app->DI_container->get(Parser\Modifiers\AdapterModifiers\Before\AddSourceModifier::class)
   ));
   
   $app->middleware->add('NewsParserPlugin\Controller\PostController:post',array(
      $app->DI_container->get(Parser\Modifiers\PostModifiers\AddPostThumbnailModifier::class)
   ));
    // Event listeners
 
    $app->event->on('media:create',array(Controller\MediaController::class,'create'));
    $app->event->on('template:create',array(Controller\TemplateController::class,'create'));
    $app->event->on('template:get',array(Controller\TemplateController::class,'get'));
    $app->event->on('template:delete',array(Controller\TemplateController::class,'delete'));
    $app->event->on('template.keys:get',array(Controller\TemplateController::class,'templateKeys'));
    $app->event->on('cron:create',array(Controller\CronController::class,'create'));
    $app->event->on('cron:delete',array(Controller\CronController::class,'delete'));
    $app->event->on('cron:get',array(Controller\CronController::class,'get'));
    $app->event->on('ai-options:get',array(Controller\AIOptionsController::class,'get'));
    $app->event->on('ai:chat',array(Controller\AIController::class,'chat'));
    $app->event->on('list:get',array(Controller\ListController::class,'get'));
    $app->event->on('html:get',array(Controller\VisualConstructorController::class,'get'));
    $app->event->on('posts:parse',array(Controller\PostController::class,'parsePost'));
    $app->event->on('posts:autopilot-parse',array(Controller\PostController::class,'autopilotParsePost'));
    $app->event->on('posts:create',array(Controller\PostController::class,'createPostFromParsedData'));
    $app->event->on('posts:in-progress',array(Controller\PostController::class,'getPostsInProgress'));
    $app->event->on('posts:data',array(Controller\PostController::class,'getPostsData'));
    

    
   // Add cron action

   add_action(NEWS_PARSER_CRON_ACTION_PREFIX.'hourly',array($app->DI_container->get(Controller\CronTaskController::class),'cronTaskCallback'));
   add_action(NEWS_PARSER_CRON_ACTION_PREFIX.'twicedaily',array($app->DI_container->get(Controller\CronTaskController::class),'cronTaskCallback'));
   add_action(NEWS_PARSER_CRON_ACTION_PREFIX.'daily',array($app->DI_container->get(Controller\CronTaskController::class),'cronTaskCallback'));
   add_action(NEWS_PARSER_CRON_ACTION_PREFIX.'weekly',array($app->DI_container->get(Controller\CronTaskController::class),'cronTaskCallback'));

   // Add WP_CLI commands

      if ( defined( 'WP_CLI' ) && WP_CLI ) {
         $invoke_autopilot=new \NewsParserPlugin\CLI\InvokeAutopilot($app->DI_container->get(Controller\CronTaskController::class));
         \WP_CLI::add_command( 'autopilot', array($invoke_autopilot,'commandCallback'));
         $invoke_parse=new \NewsParserPlugin\CLI\InvokeParse($app->DI_container->get(Controller\PostController::class));
         \WP_CLI::add_command( 'parse', array($invoke_parse,'commandCallback'));
      }   
   
 }