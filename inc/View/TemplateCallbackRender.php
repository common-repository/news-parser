<?php

namespace NewsParserPlugin\View;

/**
 * Class loading template file and render it with args.
 *
 *
 * PHP version 5.6
 *
 * @author  Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license MIT https://opensource.org/licenses/MIT
 */
class TemplateCallbackRender
{
    /**
     * Callback that will be called on page render.
     *
     * @var string
     */
    protected $callback;
    /**
     * Array of arguments for menu tamplate callback.
     *
     * @var array
     */
    protected $args;
    /**
     * Init function
     *
     * @param string $template_callback
     * @param array $args
     */
    public function __construct($template_callback, $args = array())
    {
        if (!is_callable($template_callback)) {
            throw new \Exception('Submenu template callback is not a callable.');
        }
        $this->callback=$template_callback;
        $this->args=$args;
    }
    /**
     * Render template.
     *
     * @param bool Echo output if true.
     * @return null|string
     */
    public function render($echo = true)
    {
       
        $output = call_user_func_array($this->callback, $this->args);
    }
}
