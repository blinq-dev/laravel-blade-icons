<?php

namespace Blinq\Icons;

class IconPackConfig 
{
    public string $namespace = "fa6";
    public string $path = "https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs";
    public string $discovery = "https://api.github.com/repos/FortAwesome/Font-Awesome/git/trees/6.x?recursive=1";
    public string $defaultVariant = "solid";

    public function __construct()
    {
        
    }

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    public function setDiscovery(string $discovery)
    {
        $this->discovery = $discovery;
        return $this;
    }

    public function setDefaultVariant(string $defaultVariant)
    {
        $this->defaultVariant = $defaultVariant;
        return $this;
    }
}
