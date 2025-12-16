<?php

namespace Recranet\MessengerAmqpDecorated;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Factory that wraps AMQP transports with RemoveErrorDetailsStampTransport.
 *
 * @implements TransportFactoryInterface<TransportInterface>
 */
#[AsDecorator(decorates: 'messenger.transport.amqp.factory')]
final class MessengerAmqpDecoratedTransportFactory implements TransportFactoryInterface
{
    /**
     * @param TransportFactoryInterface<TransportInterface> $inner
     */
    public function __construct(
        #[AutowireDecorated]
        private TransportFactoryInterface $inner,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createTransport(#[\SensitiveParameter] string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new MessengerAmqpDecoratedTransport(
            $this->inner->createTransport($dsn, $options, $serializer)
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return $this->inner->supports($dsn, $options);
    }
}