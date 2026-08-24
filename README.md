# COA Manager

COA Manager is a WordPress plugin for managing and displaying Certificate of Analysis records with searchable frontend cards, configurable layouts, WooCommerce product linking, and GitHub Releases update support.

## Requirements

- WordPress 6.5+
- PHP 8.0+
- WooCommerce optional

## Features

- `coa_record` custom post type
- `coa_category` taxonomy
- Required COA fields: product name, strength, purity, batch/lot, laboratory, tested date, and PDF/URL
- Verified COA display is automatic for valid published records
- Frontend manager shortcode with search, category filter, laboratory filter, total report counter, and responsive CSS Grid columns
- Product latest COA shortcode and PHP API
- Optional automatic WooCommerce product page integration
- Safe uninstall behavior, preserving data by default
- Public GitHub Release updater integration via Plugin Update Checker

## Shortcodes

```text
[coa_manager]
[coa_manager category="peptides" laboratory="janoshik" limit="12"]
[coa_manager show_title="no" desktop_columns="4" tablet_columns="2" mobile_columns="1"]
[coa_product_latest]
[coa_product_latest display="card"]
```

## PHP API

```php
if ( function_exists( 'coam_render_manager' ) ) {
	coam_render_manager();
}

if ( function_exists( 'coam_render_latest_product_coa' ) ) {
	coam_render_latest_product_coa( get_the_ID(), array( 'display' => 'card' ) );
}

if ( function_exists( 'coam_get_latest_coa_for_product' ) ) {
	$coa = coam_get_latest_coa_for_product( get_the_ID() );
}
```

## GitHub Updates

Set the public repository URL in `includes/class-coam-updater.php` by defining `COAM_GITHUB_REPOSITORY_URL` before plugin bootstrap or by using:

```php
add_filter(
	'coam_github_repository_url',
	static function () {
		return 'https://github.com/your-org/coa-manager';
	}
);
```

Before release, install the official Plugin Update Checker library into:

```text
vendor/plugin-update-checker/
```

## Release

Use semantic tags such as `v1.0.0`, `v1.0.1`, and publish GitHub Releases. If attaching a custom ZIP, name it `coa-manager.zip` and keep this internal structure:

```text
coa-manager/
  coa-manager.php
  includes/
  assets/
  templates/
  vendor/
```
