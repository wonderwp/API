<?php

use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Component\PluginSkeleton\ManagerInterface;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\API\APIServiceInterface;

add_action('wwp.abstract_manager.run', 'wwp_register_api_service_towards_manager', 10, 2);

function wwp_register_api_service_towards_manager(ManagerInterface $manager, Container $container)
{
    // Apis
    try {
        $apiService = $manager->getService(ServiceInterface::API_SERVICE_NAME);
        if ($apiService instanceof ApiServiceInterface) {
            $apiService->registerEndpoints();
        }
    } catch (ServiceNotFoundException $e) {
        if ($e->getServiceType() === ServiceInterface::API_SERVICE_NAME) {
            //No api service found, nothing to do here for now
        } else {
            throw $e;
        }
    }
}
