# EW Blockade - Advanced IP Based Access Control

## Description
EW Blockade prevents unauthorized access to your WordPress site by blocking users from specific countries. Works with multiple IP geolocation services and offers flexible caching options.

## Installation
1. Upload the `ew-blockade` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings > EW Blockade** to configure

## Key Features
- **IP Black/White Listing** - Only allow specified countries
- **3 IP Providers** - Choose between IPAPI.co (free), IP2Location, or MaxMind
- **Caching Options** - Store IP lookups in database, JSON file, or disable caching
- **Bot Bypass** - Allow search engine crawlers access

## Configuration
### Step 1: Set Allowed Countries
Go to **Settings > EW Blockade > General Settings**
- Enter 2-letter ISO country codes separated by commas (e.g. US,CA,NZ)
- Leave blank to block ALL countries (except allowed bots)

### Step 2: Choose IP Provider
Under **IP Settings** select your preferred geolocation service:
- IPAPI.co (default - 1000/day free tier, no key required)\- IP2Location (50,000/month free tier, API key required)
- MaxMind GeoIP2 (1000/day free tier, API key required)

### Step 3: Configure Caching
Optimize performance with:
- `No Caching` - Best for low traffic sites
- `File JSON` - Stores data in json cache file
- `Database` - Saves geolocation data in wp_options table

## Important Notes
- **API Keys** - Required for IP2Location and MaxMind plans
- **Bot Allowlist** - Contains common crawler user-agents by default
- To reset configuration: Delete all options starting with 'ew_blockade_' in database

## Troubleshooting
- **403 Errors** - Clear cached entries from plugin settings
- **IP Detection Failure** - Test with a different provider
- **Plugin Not Working** - Disable all caching temporarily

## Changelog
See [changelog.txt](changelog.txt) for detailed release notes

## Support
Report issues at [EW Blockade Support Forum](https://example.com/support)

## License
GPLv2 or later