<?php
namespace NewsParserPlugin\Parser;

use NewsParserPlugin\Parser\HTMLParser;

/**
 * Class HTMLPatternParser
 *
 * Parse HTML using pre-saved template pattern.
 *
 * @package NewsParserPlugin\Parser
 */
class HTMLPatternParser extends HTMLParser
{


    /**
     * HTMLPatternParser constructor.
     *
     * @param int              $cache_expiration  Cache expiration time in seconds.
     */
    public function __construct($cache_expiration = 3600)
    {
        parent::__construct($cache_expiration);
    }

    /**
     * Parse post body.
     *
     *
     * @return string Data that would be saved as the post content.
     * @throws \Exception If parsing template patterns are not set.
     */
    public function postBody()
    {
        $search_template = '';
        if (!isset($this->options['template'])) {
            throw new \Exception('Parsing template patterns should be set.');
        }
        $template = $this->options['template'];
        foreach ($template['children'] as $child_element) {
            // Create search template for Sunra\HtmlDomParser::find method
            // https://simplehtmldom.sourceforge.io/docs/1.9/manual/finding-html-elements/ How to find HTML elements? section.
            $search_template .= $child_element['searchTemplate'] . ',';
        }
        $search_template = substr($search_template, 0, -1);
        $container = $this->find($template['searchTemplate']);

        if (empty($container)) {
            return '';
        }
        if(count($container)>1){
            $elements=[];
            foreach ($container as $container_element){
                $elements=array_merge($elements,$container_element->find($search_template));
            }
        }else{
            $elements = $container[0]->find($search_template);
        }
        
        $body = $this->parseContainer($elements);
        return $body ? $body: '';
    }

    /**
     * Iterate over an array of HtmlDomParser elements and parse data.
     *
     * @param array $elements Array of HtmlDomParser element objects.
     *
     * @return array ['tagName'=>string,'content'=>string|array]
     */
    protected function parseContainer($elements)
    {
        $body = array();
        foreach ($elements as $el) {
            $el_tag = $el->tag;
            $el_data = array(
                'tagName' => $el_tag,
            );
            switch ($el_tag) {
                case 'img':
                    if (!$this->isImageFitToContext($el)) {
                        break;
                    }
                    $image_srcset = $this->parseSourceImageTag($el);
                    // If the element is an image, the content will be of type array.
                    $el_data['content'] = array(
                        'alt' => $el->alt,
                        // If the lazy load attribute data-src exists, take that as the source of the image. If none, take the src attribute.
                        'src' => $image_srcset !== false ? $this->srcSetSplit($image_srcset) : ((is_array($el->attr) && array_key_exists('data-src', $el->attr)) ? $this->srcSetSplit($el->attr['data-src']) : $el->src),
                        'srcSet' => $image_srcset!==false ? $image_srcset : '',
                        'sizes'=>''
                    );
                    break;
                case 'table':
                    $el_data['content'] = array();
                    foreach ($el->find('tr') as $tr) {
                        $tr_data = array();
                        foreach ($tr->find('td') as $td) {
                            $tr_data[] = $this->removeTags($td->innertext);
                        }
                        $el_data['content'][] = $tr_data;
                    }
                    break;
                case 'ul':
                    // If the element is a list, the content will be of type array.
                    $el_data['content'] = array();
                    foreach ($el->find('li') as $li) {
                        $el_data['content'][] = $this->removeTags($li->innertext);
                    }
                    break;
                case 'iframe':
                    // Find the YouTube video ID.
                    preg_match('/youtube\.com\/embed\/([a-zA-Z0-9\_]*)/i', $el->src, $match);
                    // Remove any symbols except those that are allowed.
                    $el_data['content'] = array_key_exists(1, $match) ? $match[1] : false;
                    break;
                default:
                    $el_data['content'] = $this->removeTags(trim($el->innertext));
            }
            if (array_key_exists('content', $el_data) && $el_data['content'] !== false) {
                $body[] = $el_data;
            }
        }

        return $body;
    }

    /**
     * Parse the source image tag.
     *
     * @param \simple_html_dom_node $image_element Image element.
     *
     * @return bool|string Source set attribute value, or false if not found.
     */
    protected function parseSourceImageTag($image_element)
    {
        // Check if the element has a parent picture
        if ($image_element->parent->tag == 'picture') {
            // Get child elements of the picture tag
            $children_elements = $image_element->parent->children;
            foreach ($children_elements as $child_element) {
                if ($child_element->tag == 'source') {
                    if (array_key_exists('srcset', $child_element->attr)) {
                        return $child_element->attr['srcset'];
                    }
                    if (array_key_exists('data-srcset', $child_element->attr)) {
                        return $child_element->attr['data-srcset'];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Split the srcset string and return the last element.
     *
     * @param string $srcSetString The srcset string.
     *
     * @return string The last element of the srcset array.
     */
    protected function srcSetSplit($srcSetString)
    {
        // Split the string into an array using ',' as the separator
        $array = explode(',', $srcSetString);

        // Iterate over each element in the array
        foreach ($array as &$element) {
            // Remove the part of the string that is separated by a space
            $parts = explode(' ', trim($element));
            $element = $parts[0];
        }

        return end($array);
    }

    /**
     * Check if the image fits the content based on keywords form the post title.
     *
     * @param \simple_html_dom_node $image_element Image element.
     *
     * @return bool Whether the image fits the content based on keywords.
     */
    protected function isImageFitToContext($image_element)
    {
        $pattern = '/\b(Or|And|It|Is|Are|Was|Were|Then|There|Here|If|Could|,|\.)\b/';
        $replacement = '';
        $keywords = preg_split('/\s+/', trim(preg_replace($pattern, $replacement, $this->post['title'])));
        $image_alt = array_key_exists('alt', $image_element->attr) ? $image_element->attr['alt'] : '';
        foreach ($keywords as $keyword) {
            if (stripos($image_alt, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}
