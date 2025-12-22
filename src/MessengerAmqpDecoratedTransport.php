<?php

namespace Recranet\MessengerAmqpDecorated;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\CloseableTransportInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\QueueReceiverInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * AMQP transport decorator that removes ErrorDetailsStamp and RedeliveryStamp
 * before sending to prevent "table too large for buffer" errors caused by
 * stacktraces accumulating on message retries.
 */
final class MessengerAmqpDecoratedTransport implements TransportInterface, QueueReceiverInterface, SetupableTransportInterface, CloseableTransportInterface, MessageCountAwareInterface
{
    public function __construct(
        private TransportInterface $inner,
    ) {
    }

    public function get(): iterable
    {
        return $this->inner->get();
    }

    /**
     * @param string[] $queueNames
     */
    public function getFromQueues(array $queueNames): iterable
    {
        if (!$this->inner instanceof QueueReceiverInterface) {
            throw new \LogicException(sprintf('The inner transport "%s" does not implement "%s".', $this->inner::class, QueueReceiverInterface::class));
        }

        return $this->inner->getFromQueues($queueNames);
    }

    public function ack(Envelope $envelope): void
    {
        $this->inner->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->inner->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->inner->send(
            $envelope
                ->withoutAll(ErrorDetailsStamp::class)
                ->withoutAll(RedeliveryStamp::class)
        );
    }

    public function setup(): void
    {
        if ($this->inner instanceof SetupableTransportInterface) {
            $this->inner->setup();
        }
    }

    public function close(): void
    {
        if ($this->inner instanceof CloseableTransportInterface) {
            $this->inner->close();
        }
    }

    public function getMessageCount(): int
    {
        if (!$this->inner instanceof MessageCountAwareInterface) {
            throw new \LogicException(sprintf('The inner transport "%s" does not implement "%s".', $this->inner::class, MessageCountAwareInterface::class));
        }

        return $this->inner->getMessageCount();
    }
}