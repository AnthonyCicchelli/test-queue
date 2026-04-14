<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue

This repository is the Magento 2 module package root for `Assessment_SimpleQueue`.

## Documentation

- `README.md` explains installation and day-one usage.
- `TEST_CASES.md` defines the verification matrix.
- `TEST_RESULTS.md` records the current test run against that matrix.

## Package

- Packagist: `assessment/module-simple-queue`
- Packagist URL: `https://packagist.org/packages/assessment/module-simple-queue`

## What It Does

The module publishes a message to Magento's message queue from three entry points:

- CLI command: `bin/magento simple-queue:publish`
- REST endpoint: `POST /rest/V1/simple-queue/publish`
- Storefront product page observer: Magento's `catalog_controller_product_view` event

Consumed messages are written to `var/log/consumer.log` in this format:

`Message published at <timestamp> and consumed at <timestamp>`

## Install

### Composer / Packagist install

If the package is available in your Composer sources, install it with:

```bash
composer require assessment/module-simple-queue:dev-main
php bin/magento module:enable Assessment_SimpleQueue
php bin/magento setup:upgrade
php bin/magento cache:clean
```

If your project uses production mode, also run:

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

### Manual drop-in install

Copy this repository into your Magento project so the final path is:

```text
app/code/Assessment/SimpleQueue
```

Then run:

```bash
bin/magento module:enable Assessment_SimpleQueue
bin/magento setup:upgrade
bin/magento cache:clean
```

If your project uses production mode, also run:

```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
```

## Queue Requirements

This module is intended for Magento projects with a working message queue backend. The tested environment used RabbitMQ over Magento's `amqp` connection.

The module expects Magento's queue infrastructure to be available so that:

- the topic `assessment.simple_queue.publish` can be published
- the queue `assessment.simple_queue` can be consumed

## How To Test

Start a consumer:

```bash
bin/magento queue:consumers:start assessment.simple_queue.consumer
```

Test the CLI publisher:

```bash
bin/magento simple-queue:publish
```

Test the REST publisher:

```bash
curl -X POST https://your-magento-host/rest/V1/simple-queue/publish
```

Expected REST response:

```text
OK
```

Then check:

```bash
tail -f var/log/consumer.log
```

## Consumer Log

The consumer writes to:

```text
var/log/consumer.log
```

Expected log format:

```text
Message published at <timestamp> and consumed at <timestamp>
```

You can watch it live with:

```bash
tail -f var/log/consumer.log
```

Example output from the anthony UAT environment:

```text
[2026-04-12T23:34:36.040467+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:34:29+00:00 and consumed at 2026-04-12T23:34:36+00:00 [] []
[2026-04-12T23:36:56.187552+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:36:44+00:00 and consumed at 2026-04-12T23:36:56+00:00 [] []
[2026-04-12T23:38:38.444792+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:38:37+00:00 and consumed at 2026-04-12T23:38:38+00:00 [] []
[2026-04-12T23:40:53.995933+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:40:53+00:00 and consumed at 2026-04-12T23:40:53+00:00 [] []
[2026-04-12T23:40:53.998496+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:40:53+00:00 and consumed at 2026-04-12T23:40:53+00:00 [] []
[2026-04-12T23:43:23.684897+00:00] assessment_simple_queue.INFO: Message published at 2026-04-12T23:43:23+00:00 and consumed at 2026-04-12T23:43:23+00:00 [] []
[2026-04-13T02:03:13.681435+00:00] assessment_simple_queue.INFO: Message published at 2026-04-13T02:03:13+00:00 and consumed at 2026-04-13T02:03:13+00:00 [] []
```

## Notes

- The REST response is forced to plain text `OK`.
- The storefront hook uses a Magento product view observer.
