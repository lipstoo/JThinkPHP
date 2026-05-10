<?php

namespace JThink\Core\Support;

class ServiceProviderManager {
    protected $container;
    protected $providers = [];
    protected $loadedProviders = [];

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function registerProvider($provider) {
        if (is_string($provider)) {
            $provider = $this->container->make($provider);
        }

        $provider->register();
        
        $this->providers[get_class($provider)] = $provider;
        
        return $this;
    }

    public function registerProviders(array $providers) {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
        return $this;
    }

    public function bootProviders() {
        foreach ($this->providers as $provider) {
            if (!isset($this->loadedProviders[get_class($provider)])) {
                $provider->boot();
                $this->loadedProviders[get_class($provider)] = true;
            }
        }
        return $this;
    }

    public function load($provider) {
        if (isset($this->loadedProviders[$provider])) {
            return;
        }

        if (isset($this->providers[$provider])) {
            $this->providers[$provider]->boot();
            $this->loadedProviders[$provider] = true;
        }
    }
}
?>