<?php

namespace Blinq\Icons\Components;
use Illuminate\View\Component;

class Icon extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public $pack, public $name, public $lazy = false)
    {
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view("blinq.icons::components.icon");
    }
}