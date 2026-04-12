<!--
Author: Anthony Cicchelli
Date: 2026-04-12
-->

# Assessment Simple Queue UAT And Design Notes

This file explains the implementation choices made during build and UAT. It is intentionally separate from `TEST_CASES.md` and `TEST_RESULTS.md`.

## Purpose

- `TEST_CASES.md` answers what should be tested.
- `TEST_RESULTS.md` answers what passed during the current test run.
- `TC_UAT_CHANGES.md` answers why certain implementation choices were made and what a reviewer should know before comparing the code directly to the original prompt.

## Current Packaging

The public repository is structured as a Magento module package root so it can be published through Packagist and installed through Composer.

Current package:

- Composer package: `assessment/module-simple-queue`
- Module name: `Assessment_SimpleQueue`
- Repository: `https://github.com/AnthonyCicchelli/test-queue`

This is why the module files live at the repository root instead of under a nested `app/code/Assessment/SimpleQueue` path in the public repo.

## Storefront Trigger Choice

The original assessment asks for a product page observer that watches the appropriate controller event when a product detail page is viewed.

The current implementation uses Magento's storefront synchronize request path instead:

- `Plugin/FrontendStorageManagerPlugin.php`
- `Plugin/FrontendActionSynchronizePlugin.php`
- `etc/frontend/di.xml`

Reason:

- With full-page cache left enabled, the product controller render event is not the most reliable source for repeated storefront hits.
- Magento's `catalog/product/frontend_action_synchronize` request is a more dependable storefront-side signal for repeated product-view behavior when the page is cached.

Tradeoff:

- This was chosen to preserve cache behavior during UAT.
- It is functionally useful, but it is not a literal one-to-one implementation of the assessment's wording about a controller-event observer.

If a reviewer wants the most literal interpretation of the prompt, the storefront entry point should be changed to a true controller-event observer and then retested.

## Queue Adapter Note

The original assessment says the implementation should be adapter-agnostic and that either MySQL or AMQP is acceptable.

The current implementation is wired to AMQP explicitly in:

- `etc/queue_publisher.xml`

Reason:

- The tested environment used RabbitMQ.
- The goal of UAT was to prove the queue flow cleanly in the live Magento environment already configured with AMQP.

Tradeoff:

- This works in the current environment.
- It is not adapter-agnostic as written.

If a reviewer wants strict adherence to that requirement, the queue transport configuration should be generalized and retested against both AMQP expectations and a non-AMQP-friendly setup.

## REST Response Note

Magento web API responses will normally serialize a scalar string response as JSON.

To keep the response body as plain `OK`, the module adds:

- `Plugin/PlainTextResponsePlugin.php`

That choice was intentional so the REST endpoint behavior matches the assessment requirement more closely.

## UAT Framing

The current UAT proves that:

- the module packages cleanly as a standalone Composer-friendly Magento module
- the CLI entry point works
- the REST entry point works
- the queue consumer logs the expected format
- the storefront trigger path works with Magento full-page cache left enabled

The current UAT does not claim that every implementation detail is a literal line-by-line match to the original prompt. Where that distinction matters, this file calls it out directly.
