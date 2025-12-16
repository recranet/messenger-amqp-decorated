<?php

namespace Recranet\MessengerAmqpDecorated\DependencyInjection;

use Recranet\MessengerAmqpDecorated\MessengerAmqpDecoratedTransportFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class MessengerAmqpDecoratedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register(MessengerAmqpDecoratedTransportFactory::class)
            ->setDecoratedService('messenger.transport.amqp.factory')
            ->setArguments([new Reference('.inner')]);
    }
}
