<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue UAT Changes

## What the Module Does

Three entry points publish the same payload to a message queue:

1. **REST** — `POST /V1/simple-queue/publish` returns `200 OK`
2. **CLI** — `bin/magento simple-queue:publish`
3. **Product Page** — triggers on product detail page view

Each publishes:

```php
$payload = json_encode(["datetime" => date(DATE_ATOM)]);
```

The consumer processes the message and logs to `var/log/consumer.log`:

```
Message published at [publish_time] and consumed at [consumed_time]
```

That's it. The module is a logging exercise.

---

## One Deviation: Observer vs Plugin

### What the requirement says

> Observe the appropriate controller event when a product detail page is viewed

### What was built

Two plugins instead of an observer:

- `FrontendStorageManagerPlugin` — forces `allowToSendRequest = 1` so the browser sends the synchronize request.
- `FrontendActionSynchronizePlugin` — publishes the queue message when the synchronize request completes.

### Why

Magento's Full-Page Cache (FPC) is enabled by default in production. When FPC is on:

- Cached product pages are served without executing the PHP controller.
- Controller events like `catalog_controller_product_view` **do not fire** on cached pages.
- An observer would only trigger on the first uncached load. Every repeat visit skips it.

The plugin approach hooks into Magento's built-in `frontend_action_synchronize` POST request, which the browser sends client-side after every PDP load — cached or not. This means the queue message fires on every product page view regardless of cache state.

### Summary

Observer works without FPC. Plugin works with or without FPC. Production runs FPC. Plugin was the right call.

---

## KISS: Keeping It Simple

The module is 11 PHP files and 9 XML configs. No file exceeds 62 lines. Every class does one thing.

### PHP Files

| File | Lines | Purpose |
|------|-------|---------|
| `registration.php` | 16 | Module registration |
| `Api/PublishManagementInterface.php` | 20 | REST service contract |
| `Logger/Logger.php` | 21 | Custom logger channel |
| `Logger/Handler.php` | 24 | Writes to `var/log/consumer.log` |
| `Model/PublishManagement.php` | 27 | REST implementation, returns `OK` |
| `Model/MessagePublisher.php` | 32 | Publishes `{"datetime": ...}` to queue |
| `Console/Command/PublishCommand.php` | 39 | CLI command |
| `Plugin/FrontendStorageManagerPlugin.php` | 40 | Forces `allowToSendRequest = 1` |
| `Plugin/PlainTextResponsePlugin.php` | 41 | Forces REST response to `text/plain` |
| `Model/Consumer.php` | 44 | Processes queue message, logs result |
| `Plugin/FrontendActionSynchronizePlugin.php` | 62 | Publishes on qualifying PDP sync |

### XML Configs

| File | Lines | Purpose |
|------|-------|---------|
| `etc/queue_publisher.xml` | 11 | AMQP publisher wiring |
| `etc/queue_consumer.xml` | 12 | Consumer registration |
| `etc/queue_topology.xml` | 14 | Queue and binding |
| `etc/communication.xml` | 13 | Topic definition |
| `etc/webapi.xml` | 14 | REST route |
| `etc/module.xml` | 15 | Module declaration and sequence |
| `etc/frontend/di.xml` | 16 | Frontend plugins |
| `etc/di.xml` | 17 | Interface preference, CLI registration |
| `etc/webapi_rest/di.xml` | 12 | REST response plugin |

No abstract classes. No factories. No unnecessary interfaces. Three entry points, one publisher, one consumer, one log file.
