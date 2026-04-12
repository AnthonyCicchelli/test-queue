<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue

This repository is the Magento 2 module package root for `Assessment_SimpleQueue`.

## What It Does

The module publishes a message to Magento's message queue from three entry points:

- CLI command: `bin/magento simple-queue:publish`
- REST endpoint: `POST /rest/V1/simple-queue/publish`
- Storefront product-view sync path: Magento's `catalog/product/frontend_action_synchronize` flow for `recently_viewed_product`

Consumed messages are written to `var/log/consumer.log` in this format:

`Message published at <timestamp> and consumed at <timestamp>`

## Install

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

## Notes

- The REST response is forced to plain text `OK`.
- The storefront hook uses Magento's product frontend synchronize controller so it can work with full-page cache left enabled.
