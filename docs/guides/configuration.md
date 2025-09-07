# Configuration Guide

PackApi has no global configuration class. Configure behavior through:

- HTTP options on `HttpClientFactory` (timeouts, headers, HTTP/3)
- Environment variables (e.g., `GITHUB_TOKEN`)
- Your own composition of providers and inspectors

## HTTP Client

Create a Symfony HTTP client with options:

```php
$client = (new HttpClientFactory())->createClient([
    'timeout' => 20,
    'enable_quic' => true,
    'headers' => ['User-Agent' => 'PackApi/1.0'],
]);
```

## Authentication

Set a GitHub token for higher rate limits:

```bash
export GITHUB_TOKEN=ghp_your_token
```

Pass it to `GitHubProviderFactory`:

```php
$github = new GitHubProviderFactory($httpFactory, $_ENV['GITHUB_TOKEN'] ?? null);
```

## Caching

Enable HTTP caching at the client level using Symfony's `CachingHttpClient` with an HttpKernel `Store` in your application. PackApi does not require a separate configuration object.

## Logging

Provide a PSR‑3 logger to `HttpClientFactory` so providers can log outgoing requests where supported.

## Composition

Compose inspectors with one or more providers from the corresponding factories. Providers are tried in order until one returns data.
