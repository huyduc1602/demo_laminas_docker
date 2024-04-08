<?php

declare(strict_types=1);

namespace UploadFile;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return $this->getModuleConfigs();
    }
    
    /**
     * Returns the routers
     */
    protected function getModuleConfigs(): array
    {
        return require 'Config/module.config.php';
    }
}
