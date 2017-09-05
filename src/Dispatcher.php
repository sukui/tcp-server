<?php

namespace ZanPHP\TcpServer;

use Kdt\Iron\Nova\Nova;
use ZanPHP\Contracts\Foundation\Application;
use ZanPHP\Coroutine\Context;

class Dispatcher
{
    /**
     * @var Request
     */
    private $request = null;
    private $context = null;

    public function dispatch(Request $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;

        yield $this->runService();
    }

    private function runService()
    {
        $serviceName = $this->getServiceName();

        $service = new $serviceName();

        if ($this->request->isGenericInvoke()) {
            $method = $this->request->getGenericMethodName();
        } else {
            $method = $this->request->getMethodName();
        }

        $args    = $this->request->getArgs();
        $args    = is_array($args) ? $args : [$args];

        yield $service->$method(...array_values($args));
    }

    private function getServiceName()
    {
        $app = make(Application::class);
        $appNamespace = $app->getNamespace();
        $appName = $app->getName();

        if ($this->request->isGenericInvoke()) {
            $serviceName = $this->request->getGenericServiceName();
        } else {
            $serviceName = $this->request->getNovaServiceName();
        }

        $serviceName = str_replace('.', '\\', $serviceName);
        //$serviceName = Nova::removeNovaNamespace($serviceName, $appName);
        $serviceName = substr($serviceName,strpos($serviceName,"\\Service\\")+1);
        $serviceName = $appNamespace . $serviceName;

        return $serviceName;
    }
}