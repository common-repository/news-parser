<?php
namespace NewsParserPlugin\Parser\Modifiers\AdapterModifiers\Before;

use NewsParserPlugin\Interfaces\MiddlewareInterface;

class GeneratePostBodyWithAI implements MiddlewareInterface
{
    public $aiServeceProviders;
    public $aiServeceProvider;
    const IMG_SYMBOL = '[image]';
    const HEADER_SYMBOL = '***';
    public function __construct($ai_service_providers)
    {
        $this->aiServeceProviders = $ai_service_providers;
    }
    public function __invoke($parsed_data, $options)
    {
        if (!$this->shouldGenerateWithAI($options)) {
            return $parsed_data;
        }
        $provider=$options['aiOptions']['aiProviders']['textGenerator'];
        $model = $options['aiOptions']['postBody']['model'];
        $prompt = $options['aiOptions']['postBody']['prompt'];
        $pipelines = array_merge([$prompt], $options['aiOptions']['postBody']['pipeline']);
        $save_post_structure = $options['aiOptions']['postBody']['savePostStructure'];
        $post_body = $this->preparePostBody($parsed_data['body'], $save_post_structure);
        $this->aiServeceProvider = reset(
            array_filter($this->aiServeceProviders,function($ai_provider) use ($provider) {
                return $ai_provider->getName() === $provider;
            })
        );
        // Separate function for AI interaction
        $response = $this->processPipelines($model, $pipelines, $post_body, $parsed_data['title']);

        if (!$response) {
            return $parsed_data;
        }

        $parsed_data['body'] = $save_post_structure ? $response : $this->replaceOriginalContent($response, $parsed_data['body']);
        return $parsed_data;
    }

    // Extracted function for early return logic
    private function shouldGenerateWithAI($options)
    {
        return $options['aiOptions'] &&
            $options['aiOptions']['postBody']['generateWithAI'] &&
            $options['aiOptions']['postBody']['model'] &&
            $options['aiOptions']['postBody']['prompt'];
    }

    // Extracted function for post body preparation
    private function preparePostBody($body, $savePostStructure)
    {
        return $savePostStructure ? $this->mergeParagraphs($body) : $this->encodeBody($body);
    }

    // Extracted function for pipeline processing
    private function processPipelines($model, $pipelines, $post_body, $title)
    {
        return array_reduce($pipelines, function ($body, $current_prompt) use ($model,$title) {
            $ai_request_options = $this->getAIRequestOptions($model);

            if (is_array($body)) {
                return $this->processBodyArray($ai_request_options, $current_prompt, $body, $title);
            } else {
                return $this->processBodyString($ai_request_options, $current_prompt, $body, $title);
            }
        }, $post_body);
    }
    protected function getAIRequestOptions($model)
    {
        $ai_request_options = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => '',
                ],
            ],
        ];

        return $ai_request_options;
    }
    protected function processBodyString($ai_request_options, $prompt, $body, $title)
    {
        $ai_request_options['messages'][0]['content'] = $this->preparePrompt($prompt, $body, $title);
        $ai_response = $this->aiServeceProvider->chat($ai_request_options);
        return $this->decodeBody($ai_response);
    }
    protected function processBodyArray($ai_request_options, $prompt, $body, $title)
    {
        $result = [];
        foreach ($body as $element) {
            if ($element['tagName'] === 'p') {
                $ai_request_options['messages'][0]['content'] = $this->preparePrompt($prompt, $element['content'], $title);
                $result = array_merge($result, $this->decodeParagraph($this->aiServeceProvider->chat($ai_request_options)));
            } elseif (in_array($element['tagName'], ['h2', 'h3', 'h4'])) {
                $ai_request_options['messages'][0]['content'] = $this->preparePrompt($prompt, $element['content'], $title);
                $element['content']=$this->aiServeceProvider->chat($ai_request_options);
                $result[] = $element;
            } else {
                $result[] = $element;
            }
        }

        return $result;
    }
    protected function removePromptTags($prompt, $post_body, $post_title)
    {
        $full_prompt = str_replace('${post}', '', $prompt);
        $full_prompt = str_replace('${title}', '', $full_prompt);
        //$full_prompt = str_replace('${headers}', $this->extractHeadins($parsed_data['body']), $full_prompt);
        //$full_prompt = str_replace('${paragraphs}', $this->countParagraphs($parsed_data['body']), $full_prompt);
        return $full_prompt;
    }
    protected function preparePrompt($prompt, $post_body, $post_title)
    {
        $full_prompt = str_replace('${post}', $post_body, $prompt);
        $full_prompt = str_replace('${title}', $post_body, $full_prompt);
        //$full_prompt = str_replace('${headers}', $this->extractHeadins($parsed_data['body']), $full_prompt);
        //$full_prompt = str_replace('${paragraphs}', $this->countParagraphs($parsed_data['body']), $full_prompt);
        return $full_prompt;
    }
    protected function decodeBody($body)
    {
        $result_body_array = [];
        $body_array = explode(PHP_EOL, $body);
        foreach ($body_array as $el) {
            if ($el == '') {
                continue;
            }

            if (strpos($el, self::HEADER_SYMBOL) !== false) {
                $result_body_array[] = array(
                    'tagName' => 'h3',
                    'content' => str_replace(self::HEADER_SYMBOL, '', $el),
                );
                continue;
            }
            if (strpos($el, self::IMG_SYMBOL) !== false) {
                $result_body_array[] = array(
                    'tagName' => 'img',
                    'content' => '',
                );
                continue;
            }
            $result_body_array[] = array(
                'tagName' => 'p',
                'content' => $el,
            );
        }
        return $result_body_array;
    }
    protected function encodeBody($body)
    {
        $body_string = '';
        foreach ($body as $el) {
            switch ($el['tagName']) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'span':
                case 'p':
                    $body_string .= $el['content'] . PHP_EOL;
                    break;
                case 'img':
                    $body_string .= self::IMG_SYMBOL;
            }

        }
        return $body_string;
    }
    protected function replaceOriginalContent($body, $original_content)
    {
        $result = [];
        $images = array_filter($original_content, function ($el) {
            return $el['tagName'] == 'img';
        });
        foreach ($body as $el) {
            if ($el['tagName'] == 'img') {
                $el=array_shift($images);
                if($el===null) continue;
            }
            $result[] = $el;
        }
        return array_merge($result, $images);
    }
    protected function extractHeadins($body)
    {
        $body_string = '';
        $counter = 1;
        foreach ($body as $el) {
            switch ($el['tagName']) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                    $body_string .= $counter . ' ' . $el['content'] . PHP_EOL;
                    $counter++;
                    break;
            }
        }
        return $body_string;
    }
    protected function mergeParagraphs($body)
    {
        $mergedBody = [];
        $previousElement = null;

        foreach ($body as $element) {
            if ($element['tagName'] === 'p') {
                if ($previousElement !== null && $previousElement['tagName'] == 'p') {
                    $previousElement['content'] .= ' ' . $element['content'] . PHP_EOL;
                } else {
                    $previousElement = $element;
                }
            } else {
                if ($previousElement !== null) {
                    $mergedBody[] = $previousElement;
                }

                $mergedBody[] = $element;
                $previousElement = null;
            }
        }
        if ($previousElement !== null) {
            $mergedBody[] = $previousElement;
        }

        return $mergedBody;
    }
    public function decodeParagraph($paragraph)
    {
        $result = [];
        $paragraphArray = explode(PHP_EOL, $paragraph);
        foreach ($paragraphArray as $el) {
            if ($el == '') {
                continue;
            }

            $result[] = array(
                'tagName' => 'p',
                'content' => $el,
            );
        }
        return $result;
    }
    protected function countParagraphs($body)
    {
        $counter = 0;
        foreach ($body as $el) {
            switch ($el['tagName']) {
                case 'p':
                    $counter++;
                    break;
            }
        }
        return $counter;
    }
}
