# Messenger AMQP Decorated

Transport decorator that removes `ErrorDetailsStamp` before sending messages to AMQP.

## Problem Solved

When Symfony Messenger retries failed messages, it adds an `ErrorDetailsStamp` containing:
- Exception class name
- Exception message
- Full stack trace

On each retry, a **new stamp is added**, causing accumulation. After several retries, the AMQP headers become too large, resulting in:
- "Invalid AMQP data" errors
- "table too large for buffer" errors

## Solution

This decorator wraps AMQP transports and strips `ErrorDetailsStamp` before `send()`, keeping messages within AMQP header limits.

## Are Failed Messages Still Retried?

**Yes.** The `ErrorDetailsStamp` is purely informational/diagnostic - it doesn't control retry behavior.

The retry mechanism is controlled by other stamps:
- `RedeliveryStamp` - marks message for redelivery
- `DelayStamp` - controls retry delay timing
- Retry count and strategy from `messenger.yaml` config

These stamps are **not** removed by this decorator.

## Flow

1. Message fails -> `ErrorDetailsStamp` added (in memory)
2. Retry logic kicks in (based on retry strategy)
3. `send()` called -> **stamps removed** by this decorator
4. Message sent to AMQP without large headers
5. Worker picks it up, retries execution
6. If fails again -> new `ErrorDetailsStamp` added -> cycle repeats

## Trade-off

You lose error details history in the message headers, but the messages continue to retry correctly.

## Installation

The factory uses Symfony's `#[AsDecorator]` attribute to automatically wrap the AMQP transport factory. Just include the classes and they will be auto-configured.

## Usage

No configuration needed. The decorator automatically wraps all AMQP transports.