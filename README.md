# Messenger AMQP Decorated

Transport decorator that removes `ErrorDetailsStamp` and `RedeliveryStamp` before sending messages to AMQP.

## Problem Solved

When Symfony Messenger retries failed messages, it adds stamps containing:
- `ErrorDetailsStamp`: Exception class name, message, and full stack trace
- `RedeliveryStamp`: Retry metadata with exception details

On each retry, **new stamps are added**, causing accumulation. After several retries, the AMQP headers become too large, resulting in:
- "Invalid AMQP data" errors
- "table too large for buffer" errors

## Solution

This decorator wraps AMQP transports and strips `ErrorDetailsStamp` and `RedeliveryStamp` before `send()`, keeping messages within AMQP header limits.

## Are Failed Messages Still Retried?

**Yes.** Both `ErrorDetailsStamp` and `RedeliveryStamp` are purely informational/diagnostic - they don't control retry behavior.

The retry mechanism is controlled by:
- `DelayStamp` - controls retry delay timing
- Retry count and strategy from `messenger.yaml` config

These are **not** removed by this decorator.

## Flow

1. Message fails -> `ErrorDetailsStamp` and `RedeliveryStamp` added (in memory)
2. Retry logic kicks in (based on retry strategy)
3. `send()` called -> **stamps removed** by this decorator
4. Message sent to AMQP without large headers
5. Worker picks it up, retries execution
6. If fails again -> new stamps added -> cycle repeats

## Trade-off

You lose error details history in the message headers, but the messages continue to retry correctly.

## Installation

```bash
composer require recranet/messenger-amqp-decorated
```

If you're using Symfony Flex, the bundle is registered automatically.

Otherwise, add it to `config/bundles.php`:

```php
return [
    // ...
    Recranet\MessengerAmqpDecorated\MessengerAmqpDecoratedBundle::class => ['all' => true],
];
```

## Usage

No configuration needed. The bundle automatically decorates `messenger.transport.amqp.factory`, wrapping all AMQP transports.