<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if (isset($_SERVER['APP_PATH'])) {
            return $_SERVER['APP_PATH'] . '/var/cache/' . $this->environment;
        }

        return dirname(__DIR__, 2) . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        if (isset($_SERVER['APP_PATH'])) {
            return $_SERVER['APP_PATH'] . '/var/log';
        }

        return dirname(__DIR__, 2) . '/var/log';
    }

}
