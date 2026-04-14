<!--
Author: Anthony Cicchelli
Date: 2026-04-13
-->

# Luma Testing Guide

Use this file to test the live Magento Luma environment at:

- [https://luma.anthonycicchelli.com/](https://luma.anthonycicchelli.com/)

## Goal

Verify that all three required entry points publish to the same queue:

1. CLI
2. REST
3. Product detail page view

Also verify that the consumer log format matches the assessment requirement.

## Live URLs

- Storefront: [https://luma.anthonycicchelli.com/](https://luma.anthonycicchelli.com/)
- Queue dashboard: [https://luma.anthonycicchelli.com/rabbitmq/](https://luma.anthonycicchelli.com/rabbitmq/)
- Direct queue page: [https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue](https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue)
- REST endpoint: [https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish](https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish)
- Product page: [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/)
- Product page with cache-buster example: [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1)

Access details for the queue dashboard should be shared separately from this document.

## Important Notes

- Use the direct queue page, not the general `#/queues` list, when checking `assessment.simple_queue`.
- The REST route is `POST` only. If you open it directly in a browser tab, Magento will return `Request does not match any route.` That is expected for `GET`.
- If you want messages to stay visible in RabbitMQ, make sure no `assessment.simple_queue.consumer` process is running.
- For repeated PDP tests, change the `tc=` query string each time.

## Quick Human Test

1. Open the queue page:
   - [https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue](https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue)
2. Record the current values for:
   - `Ready`
   - `Total`
   - `Consumers`
3. Test the REST entry point.
4. Test the CLI entry point.
5. Test the product detail page entry point.
6. Confirm the queue count increases after each test.
7. Confirm the consumer log contains the required line format.

## REST Test

Run:

```bash
curl -sk -X POST https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish
```

Expected:

```text
OK
```

Then check the queue:

```bash
curl -sk -u 'Test:Test123' \
  'https://luma.anthonycicchelli.com/rabbitmq/api/queues/anthonycicchelli/assessment.simple_queue' \
  | jq '{messages,messages_ready,messages_unacknowledged,consumers}'
```

Expected:

- `messages` increases
- `messages_ready` increases

## CLI Test

Run:

```bash
cd /Users/acicchelli/Code/anthonycicchelli-magento
php83 bin/magento simple-queue:publish
```

Expected:

```text
OK
```

Then run the same queue API check again and confirm the count increases.

## PDP Test

Open:

- [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1)

Expected:

- The product page loads
- The queue count increases after the page view

For the next run, change the query string, for example:

- [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-2](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-2)

## Consumer Log Test

Watch:

```bash
tail -f /var/log/consumer.log
```

Expected format:

```text
Message published at <timestamp> and consumed at <timestamp>
```

Example live line:

```text
[2026-04-13T02:03:13.681435+00:00] assessment_simple_queue.INFO: Message published at 2026-04-13T02:03:13+00:00 and consumed at 2026-04-13T02:03:13+00:00 [] []
```

## Pass Criteria

Mark each as `PASS` or `FAIL`:

1. Storefront loads
2. Queue dashboard opens
3. Queue page opens
4. REST returns `OK`
5. CLI returns `OK`
6. PDP loads
7. Queue increases after REST
8. Queue increases after CLI
9. Queue increases after PDP
10. Consumer log format matches requirement

## Sample Results

Use this as an example of what a successful run can look like.

1. Storefront loads  
Result: `PASS`  
Example: [https://luma.anthonycicchelli.com/](https://luma.anthonycicchelli.com/) returned `HTTP 200`.

2. Queue dashboard opens  
Result: `PASS`  
Example: [https://luma.anthonycicchelli.com/rabbitmq/](https://luma.anthonycicchelli.com/rabbitmq/) loaded and the anthony queue was available.

3. Queue page opens  
Result: `PASS`  
Example: [https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue](https://luma.anthonycicchelli.com/rabbitmq/#/queues/anthonycicchelli/assessment.simple_queue) opened successfully.

4. REST returns `OK`  
Result: `PASS`  
Example command:

```bash
curl -sk -X POST https://luma.anthonycicchelli.com/rest/V1/simple-queue/publish
```

Example output:

```text
OK
```

5. CLI returns `OK`  
Result: `PASS`  
Example command:

```bash
cd /Users/acicchelli/Code/anthonycicchelli-magento
php83 bin/magento simple-queue:publish
```

Example output:

```text
OK
```

6. PDP loads  
Result: `PASS`  
Example: [https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1](https://luma.anthonycicchelli.com/catalog/product/view/id/14/s/push-it-messenger-bag/?tc=luma-test-1) returned `HTTP 200`.

7. Queue increases after REST  
Result: `PASS`  
Example broker check moved from `19` queued messages to `21` after the REST verification run.

8. Queue increases after CLI  
Result: `PASS`  
Example broker check moved from `26` queued messages to `27` after the CLI verification run.

9. Queue increases after PDP  
Result: `PASS`  
Example broker check increased after loading the public PDP with a fresh `tc=` cache-buster.

10. Consumer log format matches requirement  
Result: `PASS`  
Example line:

```text
[2026-04-13T02:03:13.681435+00:00] assessment_simple_queue.INFO: Message published at 2026-04-13T02:03:13+00:00 and consumed at 2026-04-13T02:03:13+00:00 [] []
```
