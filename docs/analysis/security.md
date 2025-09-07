# Security Analysis Documentation

## Overview
PackApi includes a dedicated security inspection component used to gather vulnerability reports from multiple sources. The goal is to detect known issues in third‑party packages so you can act before deploying them in production.

## Security Inspector

### Configuration
There is no global configuration class. Compose the `SecurityInspector` with providers created via their factories (e.g., Packagist, OSV, GitHub). Configure HTTP behavior via `HttpClientFactory` options and environment variables (e.g., `GITHUB_TOKEN`).

### Provider Registration
All security providers implement `SecurityProviderInterface`. They are automatically registered by their respective bridge factories but can also be added manually for custom implementations.

### Usage
```php
$inspector = new SecurityInspector([$securityProvider]);
$advisories = $inspector->getSecurityAdvisories(new ComposerPackage('vendor/package'));
```

## Security Advisory Model

### Properties
Every advisory exposes an identifier, summary, publication date and affected version range.

### Severity Levels
Advisories are classified into LOW, MEDIUM, HIGH and CRITICAL levels.

### CVSS Scoring
If available, CVSS vectors are mapped to numeric scores for easier comparison.

## Provider Implementations

### GitHub Security Provider
Retrieves advisories from the GitHub Advisory Database when a repository URL is available.

### Packagist Security Provider
Fetches advisories published on Packagist, including those propagated from the FriendsOfPHP security advisories repository.

### OSV Security Provider
Queries the [OSV.dev](https://osv.dev/) database for known vulnerabilities affecting the package. See the [OSV Bridge documentation](../bridges/osv.md) for configuration details.

## Vulnerability Detection

### Advisory Sources
Providers combine information from the above databases to build a complete list of known vulnerabilities.

### Severity Assessment
Severity is normalized across sources to make it easier to filter high risk advisories.

### Affected Versions
Each advisory describes the range of versions that are vulnerable so upgrades can be planned accordingly.

## Usage Examples

### Basic Security Scan
```php
$scanner = new SecurityInspector([$githubProvider, $osvProvider]);
$result = $scanner->getSecurityAdvisories(new ComposerPackage('symfony/console'));
```

### Vulnerability Assessment
After fetching advisories you may aggregate them by severity or affected versions to determine the risk level of a package.

### Risk Analysis
Combine security scanning results with metadata and activity information to decide whether a package should be used in your project.

## Error Handling

### Provider Exceptions
Network issues or malformed responses will throw a `ProviderException`. Always wrap calls in a try/catch block.

### API Failures
When a remote service is unavailable the inspector records the failure and continues with other providers.

### Missing Advisories
If no provider can deliver advisories for a package, an empty result is returned without errors.

## Security Best Practices

### Data Validation
Always validate package names and versions before passing them to the inspector to avoid injection issues.

### Secure Communication
Use HTTPS for all outgoing requests and verify SSL certificates.

### Access Control
Restrict who can trigger security scans and store advisories securely.

## Testing

### Unit Tests
Providers include unit tests for edge cases and error conditions.

### Integration Tests
Mocked HTTP clients are used to exercise full inspector workflows.

### Security Tests
You can add your own regression tests for known vulnerabilities using the OSV provider.

## Performance Optimization

### Parallel Scanning
Multiple providers can run in parallel to speed up analysis.

### Cache Strategy
Responses from remote services are cached to avoid hitting API limits.

### Rate Limiting
The inspector uses token bucket rate limiters when services enforce quotas.

## Future Enhancements

### Custom Advisory Sources
Support for additional ecosystems such as PyPI or Maven Central could be added via new bridges.

### Risk Scoring
Combine severity and metadata metrics into an overall package risk score.

### Automated Remediation
Future versions may offer suggestions for dependency updates or patches.
