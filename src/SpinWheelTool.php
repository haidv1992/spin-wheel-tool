<?php

namespace Dbiz\SpinWheelTool;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as BaseTool;

class SpinWheelTool extends BaseTool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('spin-wheel-tool', __DIR__.'/../dist/js/tool.js');
        Nova::style('spin-wheel-tool', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Quay thưởng', [
            MenuItem::link('Cài đặt', '/spin-wheel-tool/setting'),
        ])
            ->collapsable()
            ->icon('document-text');
    }
}
