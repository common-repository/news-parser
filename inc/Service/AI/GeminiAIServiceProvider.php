<?php


namespace NewsParserPlugin\Service\AI;

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;
use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use NewsParserPlugin\Message\Success;


class GeminiAIServiceProvider 
{
    protected $geminiClient = null;
    protected const LOCK_KEY = 'gemini_lock';
    protected const RETRY_COUNT = 10;
    protected $lockDuration = 1;
    public function __construct()
    {
        if (defined('NEWS_PARSER_GEMINI_API_KEY')) {
            $this->geminiClient = $this->getGeminiClient(NEWS_PARSER_GEMINI_API_KEY);
        }
    }
    public function getName()
    {
        return 'Gemini';
    }
    protected function getGeminiClient($api_key)
    {
        return new Client($api_key);
    }

    public function getOptions()
    {
        if (!$this->geminiClient) {
            return false;
        }
        return [
            'featuredImage'=>false,
            'postTitle' => [
                'models' => ['gemini-pro'],
            ],
            'postBody' => [
                'models' => ['gemini-pro']
            ]
        ];
    }
    public function chat($chat_options_array)
    {
        $counter = 0;
        $max_counter = self::RETRY_COUNT;
        while (true) {
            try{
                $responce = $result = $this->geminiClient
                    ->geminiPro()
                    ->generateContent(
                        new TextPart($chat_options_array['messages'][0]['content']),
                    );
            } catch (\Exception $e) {
                throw new MyException($e->getMessage(), Errors::GEMINI_API_ERROR);
                if ($counter >= $max_counter) {
                    throw new MyException($e->getMessage(), $e->getCode());
                }
                $counter++;
                usleep($this->lockDuration*1000000+rand(0, 10000));
                continue;
            }
            return $responce->text();
            
        }
    }
    public function isAPIKeyDefined()
    {
        if (defined('NEWS_PARSER_GEMINI_API_KEY') && NEWS_PARSER_GEMINI_API_KEY != '') {
            return true;
        }
        return false;
    }
}