<?php

namespace Recranet\MessengerAmqpDecorated;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Factory decorator that wraps AMQP transports with MessengerAmqpDecoratedTransport.
 *
 * Decorates the default AMQP transport factory to add resilience against
 * common AMQP failures like corrupted messages and oversized headers.
 *
 * @implements TransportFactoryInterface<TransportInterface>
 */
final class MessengerAmqpDecoratedTransportFactory implements TransportFactoryInterface
{
    /**
     * @param TransportFactoryInterface<TransportInterface> $inner
     */
    public function __construct(
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