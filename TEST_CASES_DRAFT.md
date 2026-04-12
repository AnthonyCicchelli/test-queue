<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue Test Cases Draft

This draft is intentionally separate from `README.md` and is meant for review before any final documentation is merged into the main project guide.

## Scope

These test cases cover the public module in this repository:

- repository root module package

The matrix focuses on:

- module install and registration
- queue configuration and RabbitMQ wiring
- CLI publishing
- REST publishing
- consumer execution and logging
- storefront-triggered publishing through Magento's product frontend synchronize flow

## Test Cases

### Install And Registration

`TC-01 Module files present`

- Objective: confirm the repository contains only the intended deliverable files for the custom module.
- Steps:
  - Inspect the repository tree.
- Expected result:
  - The repository contains the module package files at the repo root, plus `.gitignore`, `README.md`, and this draft file.
- Evidence:
  - GitHub repo view or local file tree listing.

`TC-02 Module enables cleanly`

- Objective: verify Magento recognizes the module without registration or autoload failures.
- Steps:
  - Copy the repository contents into a Magento install at `app/code/Assessment/SimpleQueue`.
  - Run `bin/magento module:enable Assessment_SimpleQueue`.
- Expected result:
  - The command succeeds and enables the module.
- Evidence:
  - Terminal output.

`TC-03 Setup upgrade succeeds`

- Objective: verify setup scripts and XML configuration load correctly.
- Steps:
  - Run `bin/magento setup:upgrade`.
- Expected result:
  - Setup completes without DI, queue, or schema validation errors.
- Evidence:
  - Terminal output.

`TC-04 DI compile succeeds`

- Objective: verify plugins, command registration, and runtime wiring compile successfully.
- Steps:
  - Run `bin/magento setup:di:compile`.
- Expected result:
  - Compilation completes successfully.
- Evidence:
  - Terminal output.

`TC-05 Module status is enabled`

- Objective: confirm Magento reports the module as enabled after install.
- Steps:
  - Run `bin/magento module:status Assessment_SimpleQueue`.
- Expected result:
  - Magento reports the module as enabled.
- Evidence:
  - Terminal output.

### Queue Configuration

`TC-06 Topic is registered`

- Objective: verify the async topic exists and points to the consumer handler.
- Source:
  - `etc/communication.xml`
- Expected result:
  - Topic `assessment.simple_queue.publish` is async and uses `Assessment\SimpleQueue\Model\Consumer::process`.
- Evidence:
  - Config file review and successful runtime publish.

`TC-07 Publisher is wired to AMQP`

- Objective: verify publishing uses Magento's AMQP transport.
- Source:
  - `etc/queue_publisher.xml`
- Expected result:
  - The topic publishes through `connection="amqp"` using exchange `magento`.
- Evidence:
  - Config file review and RabbitMQ queue activity.

`TC-08 Queue topology exists`

- Objective: verify RabbitMQ queue creation and binding.
- Source:
  - `etc/queue_topology.xml`
- Steps:
  - Run Magento setup.
  - Inspect RabbitMQ queues and bindings.
- Expected result:
  - Queue `assessment.simple_queue` exists.
  - Binding exists for routing key `assessment.simple_queue.publish`.
- Evidence:
  - RabbitMQ UI screenshot or `rabbitmqctl` output.

`TC-09 Consumer is registered`

- Objective: confirm the named consumer is available in Magento.
- Source:
  - `etc/queue_consumer.xml`
- Steps:
  - Start consumer with `bin/magento queue:consumers:start assessment.simple_queue.consumer`.
- Expected result:
  - Magento recognizes the consumer and starts it.
- Evidence:
  - Terminal output.

### CLI Path

`TC-10 CLI command is available`

- Objective: verify the module registers the console command.
- Source:
  - `Console/Command/PublishCommand.php`
- Steps:
  - Run `bin/magento list | grep simple-queue`.
- Expected result:
  - `simple-queue:publish` appears in the command list.
- Evidence:
  - Terminal output.

`TC-11 CLI publish returns OK`

- Objective: verify the CLI path publishes and returns the expected output.
- Steps:
  - Run `bin/magento simple-queue:publish`.
- Expected result:
  - Command exits successfully and prints `OK`.
- Evidence:
  - Terminal output.

`TC-12 CLI publish reaches queue`

- Objective: verify the CLI path actually produces a queued message.
- Steps:
  - Stop the consumer.
  - Run `bin/magento simple-queue:publish`.
  - Inspect `assessment.simple_queue` in RabbitMQ.
- Expected result:
  - Queue count increases by 1.
- Evidence:
  - RabbitMQ UI screenshot or queue log.

`TC-13 CLI publish is consumed`

- Objective: verify the queued CLI message is processed end to end.
- Steps:
  - Start the consumer.
  - Run `bin/magento simple-queue:publish`.
  - Inspect `var/log/consumer.log`.
- Expected result:
  - Queue drains.
  - One new line is appended to `var/log/consumer.log`.
- Evidence:
  - Consumer log.

### REST Path

`TC-14 REST route exists`

- Objective: verify the web API route is exposed.
- Source:
  - `etc/webapi.xml`
- Steps:
  - Call `POST /rest/V1/simple-queue/publish`.
- Expected result:
  - The route resolves successfully.
- Evidence:
  - `curl -i` output.

`TC-15 REST returns plain OK`

- Objective: verify the REST response is plain text and not JSON-serialized.
- Source:
  - `Plugin/PlainTextResponsePlugin.php`
- Steps:
  - Run `curl -i -X POST https://your-magento-host/rest/V1/simple-queue/publish`.
- Expected result:
  - HTTP status `200`
  - `content-type: text/plain`
  - Response body `OK`
- Evidence:
  - `curl -i` output.

`TC-16 REST publish reaches queue`

- Objective: verify the REST path creates a queue message.
- Steps:
  - Stop the consumer.
  - Call the REST endpoint once.
  - Inspect RabbitMQ.
- Expected result:
  - Queue count increases by 1.
- Evidence:
  - RabbitMQ UI screenshot or queue log.

`TC-17 REST publish is consumed`

- Objective: verify the queued REST message is processed end to end.
- Steps:
  - Start the consumer.
  - Call the REST endpoint.
  - Inspect `var/log/consumer.log`.
- Expected result:
  - Queue drains.
  - One new line is appended to `var/log/consumer.log`.
- Evidence:
  - Consumer log.

`TC-18 REST is anonymous as designed`

- Objective: confirm the route works without Magento auth tokens.
- Steps:
  - Call the REST route without customer or admin authentication.
- Expected result:
  - Request still succeeds with `200 OK`.
- Evidence:
  - `curl` output.

### Consumer And Logging

`TC-19 Consumer starts cleanly`

- Objective: verify the consumer can run without queue transport errors.
- Steps:
  - Run `bin/magento queue:consumers:start assessment.simple_queue.consumer`.
- Expected result:
  - Consumer starts without fatal exceptions.
- Evidence:
  - Terminal output.

`TC-20 Consumer log format is correct`

- Objective: verify consumed messages are written in the required format.
- Source:
  - `Model/Consumer.php`
- Expected result:
  - Each log line follows:
    - `Message published at <timestamp> and consumed at <timestamp>`
- Evidence:
  - `var/log/consumer.log`

`TC-21 Invalid JSON payload is handled safely`

- Objective: verify malformed payloads do not crash the consumer.
- Steps:
  - Publish an invalid payload directly to the queue.
- Expected result:
  - Consumer does not fatal.
  - A warning is logged about decode failure.
- Evidence:
  - Consumer log or system log.

### Storefront Trigger Path

`TC-22 Frontend storage config is forced on`

- Objective: verify the module enables Magento's frontend sync request behavior for recently viewed products.
- Source:
  - `Plugin/FrontendStorageManagerPlugin.php`
- Steps:
  - Load a product detail page.
  - Inspect rendered page config.
- Expected result:
  - `allowToSendRequest = 1` for `recently_viewed_product`.
- Evidence:
  - Browser page source, browser evaluate output, or Playwright capture.

`TC-23 Product sync request fires on PDP load`

- Objective: verify the browser makes Magento's product frontend synchronize request.
- Steps:
  - Open a product page in a browser.
  - Inspect network requests.
- Expected result:
  - Browser sends `POST /catalog/product/frontend_action_synchronize/`.
- Evidence:
  - Playwright network log or browser devtools screenshot.

`TC-24 Valid recently viewed sync triggers publish`

- Objective: verify the module publishes when the qualifying storefront sync request completes.
- Source:
  - `Plugin/FrontendActionSynchronizePlugin.php`
- Steps:
  - Load a qualifying product detail page with consumer running.
  - Inspect consumer log.
- Expected result:
  - A new queue message is published and consumed.
- Evidence:
  - Playwright network log plus consumer log.

`TC-25 Full-page cache remains on`

- Objective: verify the module works without disabling Magento full-page cache.
- Steps:
  - Confirm FPC is enabled.
  - Load a product page.
- Expected result:
  - FPC remains enabled.
  - Storefront-trigger path still works.
- Evidence:
  - Magento cache config output, browser network log, and consumer log.

`TC-26 Repeated same-product page loads still fire sync requests`

- Objective: verify repeated PDP loads still produce Magento's synchronize request.
- Steps:
  - Load the same product page twice in the same browser context.
  - Inspect network requests.
- Expected result:
  - The browser makes the synchronize request on qualifying repeated loads.
- Evidence:
  - Playwright network log.

`TC-27 Repeated PDP syncs are consumed`

- Objective: verify repeated qualifying PDP sync requests append repeated consumer log entries.
- Steps:
  - Start consumer.
  - Trigger qualifying repeated PDP loads.
  - Inspect `var/log/consumer.log`.
- Expected result:
  - Each successful qualifying publish appends a new consumer log line.
- Evidence:
  - Consumer log.

### Negative And Boundary Cases

`TC-28 Wrong type_id does not publish`

- Objective: verify non-qualifying sync requests are ignored.
- Steps:
  - Submit the synchronize endpoint with a `type_id` other than `recently_viewed_product`.
- Expected result:
  - No queue message is published.
- Evidence:
  - Queue state and consumer log.

`TC-29 Missing product_id does not publish`

- Objective: verify malformed `ids` payloads are ignored.
- Steps:
  - Submit the synchronize endpoint without a valid `product_id`.
- Expected result:
  - No queue message is published.
- Evidence:
  - Queue state and consumer log.

`TC-30 Missing AMQP backend fails visibly`

- Objective: verify environment failure is obvious when queue infrastructure is absent.
- Steps:
  - Run the module in a Magento environment without a working AMQP backend.
- Expected result:
  - Setup, publish, or consumer startup fails in a clear and diagnosable way.
- Evidence:
  - Terminal log.

### End-To-End Acceptance

`TC-31 Full happy path`

- Objective: verify all supported publish entry points work together in one installed environment.
- Steps:
  - Install module.
  - Start consumer.
  - Run CLI publish.
  - Run REST publish.
  - Load a qualifying product detail page.
- Expected result:
  - All three entry points publish and consume successfully.
- Evidence:
  - Combined CLI logs, REST logs, browser network capture, and consumer log.

`TC-32 Public repo contains only deliverable code`

- Objective: confirm the public repository contains only the intended module deliverable and draft documentation.
- Steps:
  - Inspect repo contents.
- Expected result:
  - Repo contains only module files, `.gitignore`, `README.md`, and this draft file.
- Evidence:
  - GitHub repo screenshot or file tree listing.

## QA Checklist

- [ ] Confirm the repo contains only the intended deliverables.
- [ ] Confirm the module package files are present at the repository root.
- [ ] Confirm top-level author/date metadata exists in every file.
- [ ] Confirm PSR-12 check passes for PHP files.
- [ ] Copy the repository contents into a Magento project at `app/code/Assessment/SimpleQueue`.
- [ ] Run `bin/magento module:enable Assessment_SimpleQueue`.
- [ ] Run `bin/magento setup:upgrade`.
- [ ] Run `bin/magento cache:clean`.
- [ ] Run `bin/magento setup:di:compile` if required by the environment.
- [ ] Confirm `Assessment_SimpleQueue` is enabled in Magento.
- [ ] Confirm RabbitMQ or the Magento AMQP backend is available.
- [ ] Confirm queue `assessment.simple_queue` exists.
- [ ] Confirm routing/binding for `assessment.simple_queue.publish` exists.
- [ ] Confirm consumer `assessment.simple_queue.consumer` starts successfully.
- [ ] Confirm `simple-queue:publish` appears in `bin/magento list`.
- [ ] Run `bin/magento simple-queue:publish`.
- [ ] Confirm CLI returns `OK`.
- [ ] Confirm queue activity is visible.
- [ ] Confirm `var/log/consumer.log` gains a new entry after CLI consumption.
- [ ] Run `POST /rest/V1/simple-queue/publish`.
- [ ] Confirm response status is `200`.
- [ ] Confirm response content type is `text/plain`.
- [ ] Confirm response body is `OK`.
- [ ] Confirm no auth token is required.
- [ ] Confirm `var/log/consumer.log` gains a new entry after REST consumption.
- [ ] Load a product detail page.
- [ ] Confirm the page exposes `allowToSendRequest = 1` for recently viewed products.
- [ ] Confirm the browser sends `POST /catalog/product/frontend_action_synchronize/`.
- [ ] Confirm the request uses `type_id=recently_viewed_product`.
- [ ] Confirm a successful qualifying request creates a queue message.
- [ ] Confirm `var/log/consumer.log` gains a new entry after the storefront-triggered publish.
- [ ] Confirm Magento full-page cache remains enabled.
- [ ] Confirm the storefront-trigger path still works with FPC on.
- [ ] Confirm repeated same-product page loads still produce qualifying sync requests.
- [ ] Confirm repeated qualifying sync requests append repeated consumer log entries.
- [ ] Confirm wrong `type_id` does not publish.
- [ ] Confirm missing `product_id` does not publish.
- [ ] Confirm malformed payloads do not fatal the consumer.
- [ ] Confirm failures are visible when AMQP is unavailable.
- [ ] Capture GitHub/repo screenshot.
- [ ] Capture RabbitMQ queue screenshot or queue log.
- [ ] Capture CLI output log.
- [ ] Capture REST response log.
- [ ] Capture browser network screenshot or Playwright request log.
- [ ] Capture `var/log/consumer.log`.
- [ ] Confirm the module is ready for final documentation updates if approved.
