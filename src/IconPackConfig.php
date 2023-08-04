<?php

namespace Blinq\Icons;

class IconPackConfig 
{
    public string $namespace;
    public string $path;
    public string $discovery;
    public string $defaultVariant;

    public string $name;
    public string|null $site = null;
    public string|null $description = null;
    public string|null $license = null;
    public string|null $copyright = null;
    
    public bool $showInBrowser = true;

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

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setSite(string $site)
    {
        $this->site = $site;
        return $this;
    }

    public function setCopyRight(string $copyright)
    {
        $this->copyright = $copyright;
        return $this;
    }

    public function setShowInBrowser(bool $showInBrowser = true)
    {
        $this->showInBrowser = $showInBrowser;
        return $this;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function setLicense(string $license)
    {
        $this->license = $license;
        return $this;
    }

    public function validate()
    {
        if (empty($this->name)) {
            throw new \Exception("Name is required");
        }
        if (empty($this->namespace)) {
            throw new \Exception("Namespace is required");
        }
        if (empty($this->path)) {
            throw new \Exception("Path is required");
        }
        if (empty($this->discovery)) {
            throw new \Exception("Discovery is required");
        }
        if (empty($this->defaultVariant)) {
            throw new \Exception("Default variant is required");
        }
    }
}
