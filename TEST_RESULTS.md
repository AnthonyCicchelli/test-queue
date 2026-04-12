<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue Test Results v1

This file records the outcome of the current verification run against `TEST_CASES.md`.

It should be read together with `TC_UAT_CHANGES.md`, which explains implementation choices and any differences between the current module behavior and the original assessment wording.

Test environment:

- Magento 2.4.8-p3 at `luma.anthonycicchelli.com`
- PHP 8.3.30
- RabbitMQ 4.2.4
- Packagist: `assessment/module-simple-queue` (dev-main)

---

## Install And Registration

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-01](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#install-and-registration) | Module files present | YES | `git ls-files` confirmed 24 files: 19 module files, `.gitignore`, `README.md`, `TEST_CASES.md`, `composer.json`, `registration.php`. No stray files. |
| [TC-02](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#install-and-registration) | Module enables cleanly | YES | `bin/magento module:enable Assessment_SimpleQueue` — module was already enabled, no errors. |
| [TC-03](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#install-and-registration) | Setup upgrade succeeds | YES | `bin/magento setup:upgrade` completed. `Assessment_SimpleQueue` processed without DI, queue, or schema errors. |
| [TC-04](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#install-and-registration) | DI compile succeeds | YES | `bin/magento setup:di:compile` — "Generated code and dependency injection configuration successfully." |
| [TC-05](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#install-and-registration) | Module status is enabled | YES | `bin/magento module:status Assessment_SimpleQueue` returned "Module is enabled". |

## Queue Configuration

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-06](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#queue-configuration) | Topic is registered | YES | `etc/communication.xml` defines topic `assessment.simple_queue.publish` with `is_synchronous="false"` and handler `Assessment\SimpleQueue\Model\Consumer::process`. Confirmed by successful runtime publish. |
| [TC-07](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#queue-configuration) | Publisher is wired to AMQP | YES | `etc/queue_publisher.xml` specifies `connection="amqp"` and `exchange="magento"`. Confirmed by RabbitMQ queue activity during publish tests. |
| [TC-08](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#queue-configuration) | Queue topology exists | YES | `rabbitmqctl list_queues -p anthonycicchelli` shows `assessment.simple_queue`. `rabbitmqctl list_bindings -p anthonycicchelli` shows `magento -> assessment.simple_queue` with routing key `assessment.simple_queue.publish`. |
| [TC-09](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#queue-configuration) | Consumer is registered | YES | `bin/magento queue:consumers:list` includes `assessment.simple_queue.consumer`. Consumer started successfully with `queue:consumers:start`. |

## CLI Path

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-10](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#cli-path) | CLI command is available | YES | `bin/magento list` includes `simple-queue:publish` with description "Publish a SimpleQueue test message to Magento message queue." |
| [TC-11](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#cli-path) | CLI publish returns OK | YES | `bin/magento simple-queue:publish` printed `OK` and exited with success code. |
| [TC-12](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#cli-path) | CLI publish reaches queue | YES | Consumer stopped. Published via CLI. `rabbitmqctl list_queues -p anthonycicchelli` showed `assessment.simple_queue` went from 0 to 1 message. |
| [TC-13](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#cli-path) | CLI publish is consumed | YES | Started consumer with `--max-messages=1`. Queue drained from 1 to 0. `var/log/consumer.log` gained one new line: `Message published at 2026-04-12T21:19:13+00:00 and consumed at 2026-04-12T21:19:32+00:00`. |

## REST Path

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-14](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#rest-path) | REST route exists | YES | `curl -i -X POST https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish` returned HTTP 200. Route resolved successfully. |
| [TC-15](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#rest-path) | REST returns plain OK | YES | Response: HTTP 200, `content-type: text/plain; charset=utf-8`, body `OK`. Not JSON-serialized. |
| [TC-16](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#rest-path) | REST publish reaches queue | YES | Consumer stopped. Called REST endpoint. `rabbitmqctl list_queues -p anthonycicchelli` showed `assessment.simple_queue` increased to 2 messages. |
| [TC-17](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#rest-path) | REST publish is consumed | YES | Started consumer. Queue drained. `var/log/consumer.log` gained one new line: `Message published at 2026-04-12T21:22:20+00:00 and consumed at 2026-04-12T21:22:35+00:00`. |
| [TC-18](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#rest-path) | REST is anonymous as designed | YES | `curl` sent with no auth headers, no customer or admin token. Response was `200 OK`. Route uses `<resource ref="anonymous"/>`. |

## Consumer And Logging

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-19](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#consumer-and-logging) | Consumer starts cleanly | YES | `bin/magento queue:consumers:start assessment.simple_queue.consumer` started and ran without fatal exceptions. Process remained alive and responsive. |
| [TC-20](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#consumer-and-logging) | Consumer log format is correct | YES | Every line in `var/log/consumer.log` follows the format: `Message published at <ISO 8601 timestamp> and consumed at <ISO 8601 timestamp>`. |
| [TC-21](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#consumer-and-logging) | Invalid JSON payload is handled safely | YES | Published `NOT_VALID_JSON{{{` directly via Magento's `PublisherInterface`. Consumer did not crash. Queue drained to 0. No fatal exception. |

## Storefront Trigger Path

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-22](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Frontend storage config is forced on | YES | `curl` on PDP (`push-it-messenger-bag.html`) returned page source containing `"recently_viewed_product":{"requestConfig":{"syncUrl":"..."}` with `allowToSendRequest":1`. Other product types showed `null`. |
| [TC-23](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Product sync request fires on PDP load | YES | Playwright browser navigation to the PDP triggered `POST /catalog/product/frontend_action_synchronize/` and returned HTTP `200`. The captured request body included `type_id=recently_viewed_product` and `product_id=14`. |
| [TC-24](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Valid recently viewed sync triggers publish | YES | Sent qualifying sync POST. `rabbitmqctl list_queues -p anthonycicchelli` showed queue went from 0 to 1. Consumer processed it. `var/log/consumer.log` gained new line: `Message published at 2026-04-12T21:23:57+00:00 and consumed at 2026-04-12T21:24:00+00:00`. |
| [TC-25](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Full-page cache remains on | YES | `bin/magento cache:status` showed `full_page: 1`. Storefront trigger path worked with FPC enabled — sync POST still triggered publish and consume. |
| [TC-26](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Repeated same-product page loads still fire sync requests | YES | Playwright loaded the same PDP twice. On both loads, the browser emitted `POST /catalog/product/frontend_action_synchronize/` with `type_id=recently_viewed_product` and `product_id=14`. |
| [TC-27](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#storefront-trigger-path) | Repeated PDP syncs are consumed | YES | Consumer processed both messages. `var/log/consumer.log` gained 2 new lines (from 15 to 17). Each line had the expected format. |

## Negative And Boundary Cases

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-28](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#negative-and-boundary-cases) | Wrong type_id does not publish | YES | Sent sync POST with `type_id=recently_compared_product`. Queue stayed at 0. No message published. |
| [TC-29](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#negative-and-boundary-cases) | Missing product_id does not publish | YES | Sent sync POST with `type_id=recently_viewed_product` but `ids` containing only `added_at` (no `product_id`). Queue stayed at 0. No message published. |
| [TC-30](https://github.com/AnthonyCicchelli/test-queue/blob/main/TEST_CASES.md#negative-and-boundary-cases) | Missing AMQP backend fails visibly | YES | Stopped RabbitMQ via `brew services stop rabbitmq`. CLI publish: `stream_socket_client(): Unable to connect to tcp://127.0.0.1:5671 (Connection refused)`. Consumer start: same connection refused error. REST publish: HTTP 500 with `{"message":"Internal Error. Details are available in Magento log file. Report ID: webapi-69dc0f4a26732"}`. All three paths failed clearly and diagnosably. RabbitMQ restarted and verified healthy after test. |

## End-To-End Acceptance

| TC | Name | Pass | How |
|----|------|------|-----|
| [TC-31](TEST_CASES.md#end-to-end-acceptance) | Full happy path | YES | Module installed and enabled. Consumer started. CLI publish returned `OK` and was consumed. REST publish returned HTTP 200 `text/plain` `OK` and was consumed. Storefront sync POST with qualifying `type_id` and `product_id` triggered publish and was consumed. All three entry points logged to `var/log/consumer.log` with correct format. |
| [TC-32](TEST_CASES.md#end-to-end-acceptance) | Public repo contains only deliverable code | YES | `git ls-files` shows only module source files, `.gitignore`, `README.md`, and `TEST_CASES.md`. No IDE configs, vendor directories, `.env` files, or other artifacts. Packagist at `assessment/module-simple-queue` resolves and locks correctly via `composer require`. |

---

## Summary

| Section | Total | Pass | Fail |
|---------|-------|------|------|
| Install And Registration | 5 | 5 | 0 |
| Queue Configuration | 4 | 4 | 0 |
| CLI Path | 4 | 4 | 0 |
| REST Path | 5 | 5 | 0 |
| Consumer And Logging | 3 | 3 | 0 |
| Storefront Trigger Path | 6 | 6 | 0 |
| Negative And Boundary Cases | 3 | 3 | 0 |
| End-To-End Acceptance | 2 | 2 | 0 |
| **Total** | **32** | **32** | **0** |
