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
