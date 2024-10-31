<?php

namespace NewsParserPlugin\CLI;
use \NewsParserPlugin\Controller\PostController;
use NewsParserPlugin\Exception\MyException;

class InvokeParse {
    protected $postController;
    protected $urlErrorMessage='The --posts option must be a valid URL.';
    protected $templateErrorMessage='The --template option must be a valid URL.';
    protected $optionsErrorMessage='The --options option must be a valid URL.';
    public function __construct(PostController $post_controller) {
        $this->postController = $post_controller;
    }

    public function commandCallback( $args,$assoc_args ) {
        if(!array_key_exists('posts',$assoc_args)){
            \WP_CLI::log( $this->urlErrorMessage );
             return ;
        }
        if(!array_key_exists('template',$assoc_args)){
            \WP_CLI::log( $this->templateErrorMessage );
             return ;
        }
        if(!array_key_exists('options',$assoc_args)){
            \WP_CLI::log( $this->optionsErrorMessage );
             return ;
        }
        
        $url=$assoc_args['posts'];
        if (!$url){
            \WP_CLI::log( $this->urlErrorMessage );
             return ;
        }
        $template=$assoc_args['template'];
        if (!$template){
            \WP_CLI::log( $this->templateErrorMessage );
             return ;
        }
        $options=$assoc_args['options'];
        if (!$options){
            \WP_CLI::log( $this->optionsErrorMessage );
             return ;
        }
        try{
            $template=$this->getFileContent($template);
            $options=$this->getFileContent($options);
            $parsed_data=$this->postController->parser->get($url,$template);
            echo json_encode($parsed_data).PHP_EOL; 
        }catch(MyException $e){
            \WP_CLI::log( sprintf( 'Error: %s', $e->getMessage()) );
        }
        
    }
    protected function getFileContent($path)
    {
        $file_content='';
        if(file_exists(NEWS_PARSER_PLUGIN_DIR.$path)){
            $file_content=json_decode(file_get_contents(NEWS_PARSER_PLUGIN_DIR.$path),true);
        }else{
            throw new MyException('File not found '.NEWS_PARSER_PLUGIN_DIR.$path,500);
        }
        return $file_content;
    }
}

