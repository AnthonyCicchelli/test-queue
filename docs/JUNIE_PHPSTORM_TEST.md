<!--
Author: Anthony Cicchelli
Date: 2026-04-13
-->

# Junie And PHPStorm Test Brief

Use this file as a focused UAT brief for the `Assessment_SimpleQueue` module.

## Goal

Verify that the Magento module works on the live anthony Luma environment and that all three required entry points publish to the same queue.

## Environment

- Storefront: [https://luma.anthonycicchelli.com/](https://luma.anthonycicchelli.com/)
- RabbitMQ UI: [https://luma.anthonycicchelli.com/rabbitmq/](https://luma.anthonycicchelli.com/rabbitmq/)
- Queue page: [https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue](https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue)
- REST endpoint: [https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish](https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish)
- PDP test page: [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/)
- PDP cache-buster example: [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=junie-check-1](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=junie-check-1)

RabbitMQ login:

- Username: `Test`
- Password: `Test123`

Magento project path:

- `/Users/acicchelli/Code/anthonycicchelli-magento`

Module repo:

- [https://github.com/AnthonyCicchelli/test-queue](https://github.com/AnthonyCicchelli/test-queue)

## What To Verify

1. The storefront loads on the live anthony Luma domain.
2. The RabbitMQ UI loads and the queue `assessment.simple_queue` is visible.
3. The REST endpoint returns `OK`.
4. The CLI command returns `OK`.
5. The product detail page loads.
6. REST, CLI, and PDP all publish to the same queue.
7. The consumer log writes the required message format.

## Commands

### CLI publish

```bash
cd /Users/acicchelli/Code/anthonycicchelli-magento
/opt/homebrew/Cellar/php@8.3/8.3.30/bin/php -d memory_limit=2G bin/magento simple-queue:publish
```

Expected:

```text
OK
```

### REST publish

```bash
curl -sk -X POST https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish
```

Expected:

```text
OK
```

### Queue API check

```bash
curl -sk -u 'Test:Test123' \
  'https://luma.anthonycicchelli.com/rabbitmq/api/queues/anthonycicchelli/assessment.simple_queue' \
  | jq '{messages,messages_ready,messages_unacknowledged,consumers}'
```

Expected:

- Queue counts increase after REST, CLI, or PDP tests.
- If `consumers` is `0`, messages should remain visible.

### Consumer log check

```bash
tail -f /Users/acicchelli/Code/anthonycicchelli-magento/var/log/consumer.log
```

Expected line format:

```text
Message published at <timestamp> and consumed at <timestamp>
```

## Simple Test Flow

1. Open the queue page in RabbitMQ.
2. Record the current `Ready`, `Total`, and `Consumers` values.
3. Call the REST endpoint once and confirm the queue count increases.
4. Run the CLI command once and confirm the queue count increases.
5. Open the PDP with a unique `tc=` query string and confirm the queue count increases.
6. Check `var/log/consumer.log` and confirm the required line format is present.

## What To Report Back

Please report each item as `PASS` or `FAIL`:

1. Storefront load
2. RabbitMQ login
3. Queue page visible
4. REST returns `OK`
5. CLI returns `OK`
6. PDP loads
7. Queue count changes after REST
8. Queue count changes after CLI
9. Queue count changes after PDP
10. Consumer log format matches requirement

If anything fails, include:

- the exact URL or command used
- the exact output or screenshot
- whether the failure is from the browser, RabbitMQ UI, queue API, or Magento log
