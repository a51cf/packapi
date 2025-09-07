<div align="center">

<h1>Pack 📦 API</h1>

```bash
composer req smnandre/packapi
```

&nbsp; ![PHP Version](https://img.shields.io/badge/PHP-8.3+-2e7d32?logoColor=6AB76E&labelColor=010)
&nbsp; ![CI](https://img.shields.io/github/actions/workflow/status/smnandre/packapi/CI.yaml?branch=main&label=Tests&logoColor=white&logoSize=auto&labelColor=010&color=388e3c)
&nbsp; ![Release](https://img.shields.io/github/v/release/smnandre/packapi?label=Stable&logoColor=white&logoSize=auto&labelColor=010&color=43a047)
&nbsp; [![GitHub Sponsors](https://img.shields.io/github/sponsors/smnandre?logo=github-sponsors&logoColor=66bb6a&logoSize=auto&label=%20Sponsor&labelColor=010&color=a5d6a7)](https://github.com/sponsors/smnandre)
&nbsp; ![License](https://img.shields.io/github/license/smnandre/packapi?label=License&logoColor=white&logoSize=auto&labelColor=010&color=2e7d32)

</div>


**PackAPI** is a small and extensible PHP library for analyzing Open Source packages across multiple ecosystems. 

Get comprehensive insights about **Composer**, **NPM**, **GitHub repositories**, and more through a unified, strongly-typed API.


## Features

- **Multi-Ecosystem Support**: Composer, NPM, GitHub, jsDelivr, OSV Security Database, BundlePhobia
- **Rich Package Analytics**: Metadata, download stats, security advisories, project activity, quality scoring
- **Security-First**: Built-in security advisory scanning and vulnerability detection
- **Fully Tested**: 334+ tests with comprehensive coverage
- **Type-Safe**: Strict typing throughout with PHP 8.3+ features
- **Extensible**: Plugin architecture for adding new package sources
- **Minimal Dependencies**: Built on Symfony components and PSR interfaces
- **HTTP/3 (QUIC) Ready**: Modern networking with fallback to HTTP/2/1.1

## Quick Start

### Installation

```bash
composer require smnandre/packapi
```

### Basic Usage

```php
use PackApi\Builder\PackApiBuilder;
use PackApi\Package\ComposerPackage;

// Create the analyzer
$packApi = (new PackApiBuilder())
    ->withGitHubToken($_ENV['GITHUB_TOKEN'] ?? null) // Optional: for higher rate limits
    ->build();

// Analyze any package
$package = new ComposerPackage('symfony/console');
$analysis = $packApi->analyze($package);

// Get comprehensive data
echo "Package: " . $analysis['metadata']->getName() . "\n";
echo "Downloads: " . $analysis['downloads']->get('monthly')?->getCount() . "\n";
echo "Quality Grade: " . $analysis['quality']->getGrade() . "\n";
echo "Security Issues: " . count($analysis['security']) . "\n";
```

## Supported Package Types

| Ecosystem    | Package Type      | Metadata | Downloads | Security | Activity | Content | Bundle Size |
|--------------|-------------------|----------|-----------|----------|----------|---------|-------------|
| **Composer** | `ComposerPackage` | Yes      | Yes       | Yes      | Yes      | Yes     | No          |
| **NPM**      | `NpmPackage`      | Yes      | Yes       | Yes      | Yes      | Yes     | Yes         |
| **GitHub**   | Any with repo URL | Yes      | Yes       | Yes      | Yes      | Yes     | No          |

## Usage Examples

### Package Metadata Analysis

```php
use PackApi\Inspector\MetadataInspector;
use PackApi\Bridge\Packagist\PackagistProviderFactory;
use PackApi\Package\ComposerPackage;

$factory = new PackagistProviderFactory($httpClient);
$inspector = new MetadataInspector([
    $factory->createMetadataProvider()
]);

$package = new ComposerPackage('laravel/framework');
$metadata = $inspector->getMetadata($package);

echo $metadata->getName() . "\n";        // laravel/framework  
echo $metadata->getDescription() . "\n"; // The Laravel Framework
echo $metadata->getLicense() . "\n";     // MIT
echo $metadata->getRepository() . "\n";  // https://github.com/laravel/framework
```

### Download Statistics

```php
use PackApi\Inspector\DownloadStatsInspector;
use PackApi\Package\NpmPackage;

$inspector = new DownloadStatsInspector([
    $npmFactory->createDownloadStatsProvider(),
    $packagistFactory->createStatsProvider()
]);

$package = new NpmPackage('react');
$stats = $inspector->getStats($package);

$monthly = $stats->get('monthly');
if ($monthly) {
    echo "Downloads this month: " . number_format($monthly->getCount()) . "\n";
    $days = $monthly->getEnd()->diff($monthly->getStart())->days + 1;
    echo "Daily average: " . number_format($monthly->getCount() / $days) . "\n";
}
```

### Security Advisory Scanning

```php
use PackApi\Inspector\SecurityInspector;
use PackApi\Bridge\OSV\OSVProviderFactory;
use PackApi\Bridge\GitHub\GitHubProviderFactory;

$inspector = new SecurityInspector([
    $osvFactory->createSecurityProvider(),      // OSV Database
    $githubFactory->createSecurityProvider()   // GitHub Security Advisories
]);

$advisories = $inspector->getSecurityAdvisories($package);

foreach ($advisories as $advisory) {
    echo "ALERT: {$advisory->getTitle()}\n";
    echo "   Severity: {$advisory->getSeverity()}\n";
    echo "   Link: {$advisory->getLink()}\n\n";
}
```

### Project Activity Analysis

```php
use PackApi\Inspector\ActivityInspector;

$inspector = new ActivityInspector([
    $githubFactory->createActivityProvider()
]);

$activity = $inspector->getActivitySummary($package);

echo "Last commit: " . $activity->getLastCommit()?->format('Y-m-d') . "\n";
echo "Contributors: " . $activity->getContributors() . "\n";
echo "Open issues: " . $activity->getOpenIssues() . "\n";
echo "Latest release: " . $activity->getLastRelease() . "\n";
```

### Package Content Analysis

```php
use PackApi\Inspector\ContentInspector;

$inspector = new ContentInspector([
    $jsDelivrFactory->createContentProvider(),
    $githubFactory->createContentProvider()
]);

$content = $inspector->getContentOverview($package);

echo "Files: " . $content->getFileCount() . "\n";
echo "Total size: " . $content->getHumanSize() . "\n";
echo "Has README: " . ($content->hasReadme() ? 'Yes' : 'No') . "\n";
echo "Has tests: " . ($content->hasTests() ? 'Yes' : 'No') . "\n";

// List largest files
foreach ($content->getLargestFiles(5) as $file) {
    echo "FILE: {$file->getPath()}: {$file->getHumanSize()}\n";
}
```

### Bundle Size Analysis (NPM)

```php
use PackApi\Bridge\BundlePhobia\BundlePhobiaProviderFactory;

$factory = new BundlePhobiaProviderFactory($httpClient);
$sizeProvider = $factory->createBundleSizeProvider();

$package = new NpmPackage('lodash');
$bundleSize = $sizeProvider->getBundleSize($package);

if ($bundleSize) {
    echo "Bundle size: " . $bundleSize->getFormattedSize() . "\n";
    echo "Gzipped: " . $bundleSize->getFormattedGzipSize() . "\n";
    echo "Dependencies: " . $bundleSize->getDependencyCount() . "\n";
}
```

## Configuration

### HTTP/3 (QUIC) Support

PackAPI supports HTTP/3 for improved performance:

```php
use PackApi\Http\HttpClientFactory;

$httpFactory = new HttpClientFactory();
$client = $httpFactory->createClient([
    'enable_quic' => true  // Automatic fallback if not supported
]);
```

### Caching

Add caching for better performance:

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter('packapi', 3600); // 1 hour TTL

$packApi = (new PackApiBuilder())
    ->useCache($cache)
    ->build();
```

### Logging

Enable request logging for debugging:

```php
use Psr\Log\LoggerInterface;

$packApi = (new PackApiBuilder())
    ->withLogger($logger)
    ->build();
```

### GitHub Authentication

For higher rate limits, provide a GitHub token:

```php
// Via environment variable
$_ENV['GITHUB_TOKEN'] = 'ghp_your_token_here';

// Or directly
$packApi = (new PackApiBuilder())
    ->withGitHubToken('ghp_your_token_here')
    ->build();
```

## Architecture

PackAPI uses a clean, extensible architecture:

### Core Components

- **Packages**: Represent different package types (`ComposerPackage`, `NpmPackage`)
- **Inspectors**: Analyze specific aspects (metadata, downloads, security, etc.)
- **Providers**: Fetch data from external sources (Packagist, GitHub, NPM, etc.)
- **Models**: Strongly-typed value objects for results
- **Builder**: Fluent API for configuration

### Provider Pattern

```php
// Each inspector can have multiple providers
$securityInspector = new SecurityInspector([
    new OSVSecurityProvider($httpClient),          // OSV Database  
    new GitHubSecurityProvider($httpClient),       // GitHub Advisories
    new PackagistSecurityProvider($httpClient)     // Packagist Security
]);

// Providers are tried in order until one succeeds
$advisories = $securityInspector->getSecurityAdvisories($package);
```

## Testing

PackAPI has comprehensive test coverage:

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage

# Check code style
composer cs

# Fix code style
composer cs-fix
```

### Adding New Providers

```php
// 1. Implement the provider interface
class MyCustomProvider implements MetadataProviderInterface 
{
    public function supports(Package $package): bool { /* ... */ }
    public function getMetadata(Package $package): ?Metadata { /* ... */ }
}

// 2. Create a factory
class MyCustomProviderFactory 
{
    public function createMetadataProvider(): MyCustomProvider 
    {
        return new MyCustomProvider($this->httpClient);
    }
}

// 3. Use in inspector
$inspector = new MetadataInspector([
    new MyCustomProvider($httpClient)
]);
```

## Requirements

- **PHP 8.3+** (uses modern PHP features)
- **ext-json** (for API responses)
- **ext-curl** (for HTTP requests)
- **ext-mbstring** (for string handling)

### Optional

- **ext-curl with HTTP/3** (for QUIC support)
- **Redis/Memcached** (for distributed caching)

---

## Contributing

Contributions are welcome! Please start by creating an issue to discuss your changes.

---

## Credits

Created and maintained by [Simon André](https://github.com/smnandre).

> [!TIP]
> This library is developed and maintained by a single developer in their free time.
>
> To ensure continued maintenance and improvements, consider [sponsoring development](https://github.com/sponsors/smnandre).

---

## License

MIT License - see [LICENSE](LICENSE) file for details.
