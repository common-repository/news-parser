<?php
namespace NewsParserPlugin\Menu\Admin;

use NewsParserPlugin\Interfaces\MenuPageInterface;
use NewsParserPlugin\Utils\MenuConfig;
use NewsParserPlugin\View\TemplateRender;
use NewsParserPlugin\View\TemplateCallbackRender;

/**
 * Class renders menu page.
 *
 * PHP version 5.6
 *
 * @package Menu
 * @author  Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license MIT
 */

class MenuPage implements MenuPageInterface
{
    protected $config;
    
    /**
     * Initiate and add menu configuration file.
     *
     * @return void
     */
    public function init(MenuConfig $config)
    {
        $this->config=$config;
        \add_action('admin_menu', array($this, 'addMainMenu'));
    }
    /**
     * Callback for "admin_menu" action
     *
     * @return void
     */
    public function addMainMenu()
    {
        $menu = $this->config->get()->menu;
        \add_menu_page($menu->page_title, $menu->menu_title, $menu->capability, $menu->menu_slug, '', $menu->icon);
        $this->addSubMenus();
    }
    /**
     * Renders submenu
     *
     * @return void
     */
    protected function addSubMenus()
    {
        $subMenu = $this->config->get('array')['menu']['subs'];
        foreach ($subMenu as $sub) {
            if(!is_string($sub['template']))
            {
                $template=$this->getTemplateCallbackRender($sub['template']);
            }else{
                $template=$this->getTemplateRender($sub['template']);
            }
            \add_submenu_page($sub['parent_slug'], $sub['page_title'], $sub['menu_title'], $sub['capability'], $sub['menu_slug'], array($template, 'render'));
        }
    }
    /**
     * Method factory for TemplateRender
     *
     * @param string $path_to_template
     * @return TemplateRender
     */
    protected function getTemplateRender($path_to_template)
    {
        return new TemplateRender($path_to_template);
    }
    protected function getTemplateCallbackRender($path_to_template)
    {
        return new TemplateCallbackRender($path_to_template);
    }
}
