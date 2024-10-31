<?php

namespace NewsParserPlugin\Service\AI;

use NewsParserPlugin\Exception\MyException;
use NewsParserPlugin\Message\Errors;
use Orhanerday\OpenAi\OpenAi;

class OpenAIServiceProvider
{
    protected $openAIClient = null;
    protected const LOCK_KEY = 'openai_lock';
    protected const RETRY_COUNT = 10;
    protected $lockDuration = 34;
    public function __construct()
    {
        if (defined('NEWS_PARSER_OPENAI_API_KEY')) {
            $this->openAIClient = $this->getOpenAIClient(NEWS_PARSER_OPENAI_API_KEY);
            if (defined('NEWS_PARSER_OPENAI_FREE_TIER')) {
                $this->lockDuration = 34;
            }

        }
    }
    public function getName()
    {
        return 'OpenAI';
    }
    public function getOptions()
    {
        if (!$this->openAIClient) {
            return false;
        }

        $responce = json_decode($this->openAIClient->listModels());
        $this->checkResponceStatus();
        if (!$responce->data && !is_array($responce->data)) {
            return false;
        }
        $open_ai_models = array_filter($responce->data, function ($model) {
            return strpos($model->id, 'gpt') !== false;
        });
        $models_list = array_values(array_map(function ($model) {
            return $model->id;
        }, $open_ai_models));
        return [
            'featuredImage' => [
                'models' => ['dall-e-2', 'dall-e-3'],
                'sizes' =>['1024x1024', '1024x1792', '1792x1024']
            ],
            'postTitle' => [
                'models' => $models_list,
            ],
            'postBody' => [
                'models' => $models_list,
            ],
        ];
    }
    public function chat($chat_options_array)
    {
        $counter = 0;
        $max_counter = self::RETRY_COUNT;
        while (true) {
            $responce = json_decode($this->openAIClient->chat($chat_options_array));
            if (property_exists($responce, 'error')) {
                if ($responce->error->code == 'rate_limit_exceeded') {
                    if ($counter >= $max_counter) {
                        throw new MyException($responce->error->message, $responce->error->code);
                    }
                    $counter++;
                    usleep($this->lockDuration*1000000+rand(0, 10000));
                } else {
                    throw new MyException($responce->error->message, $responce->error->code);
                }
            } else {
                return $responce->choices[0]->message->content;
            }
        }
    }
    public function image($image_options_array)
    {
        $counter = 0;
        $max_counter = self::RETRY_COUNT;
        while (true) {
            $responce = json_decode($this->openAIClient->image($image_options_array));
            if (property_exists($responce, 'error')) {
                if ($responce->error->code == 'rate_limit_exceeded') {
                    if ($counter >= $max_counter) {
                        throw new MyException($responce->error->message, $responce->error->code);
                    }
                    $counter++;
                    sleep($this->lockDuration);
                } else {
                    throw new MyException($responce->error->message, $responce->error->code);
                }
            } else {
                return $responce->data[0]->url;
            }
        }
    }
    public function isAPIKeyDefined()
    {
        if (defined('NEWS_PARSER_OPENAI_API_KEY') && NEWS_PARSER_OPENAI_API_KEY != '') {
            return true;
        }
        return false;
    }
    protected function getOpenAIClient($api_key)
    {
        return new OpenAI($api_key);
    }
    protected function checkResponceStatus()
    {
        $request_detatils = $this->openAIClient->getCURLInfo();
        if ($request_detatils['http_code'] != 200) {
            throw new MyException(Errors::text('OPENAI_API_ERROR'), $request_detatils['http_code']);
        }

    }
    protected function aquireLock()
    {
        $lock_key = self::LOCK_KEY;
        $lock_duration = $this->lockDuration;
        $lock = get_transient($lock_key);
        return $lock;
    }
    protected function setLock($lock_duration)
    {
        $lock_key = self::LOCK_KEY;
        set_transient($lock_key, true, $lock_duration);
    }
    protected function releaseLock()
    {
        $lock_key = self::LOCK_KEY;
        delete_transient($lock_key);
    }

}
