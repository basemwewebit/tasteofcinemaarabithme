# tasteofcinemaarabithme Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-03-03

## Active Technologies
- PHP 8.0+, HTML5, CSS3, JS (Vanilla) + WordPress 6.0+, active theme (`tasteofcinemaarabithme`) (001-fix-single-post)
- MySQL/MariaDB (standard WP database schema) (001-fix-single-post)
- PHP 8.0+ + WordPress 6.0+, Tailwind CSS (001-home-layout-updates)
- MySQL/MariaDB (WordPress DB) (001-home-layout-updates)
- PHP 8.x + WordPress Core, Secure Custom Fields (SCF) (001-archive-ads-injection)
- WordPress Database (Options table via SCF) (001-archive-ads-injection)
- PHP 8.x, JavaScript (ES6+), CSS3 + WordPress Core, Taste of Cinema Theme (001-loader-ads)
- WordPress Database (wp_options for settings) (001-loader-ads)
- PHP 8+ (WordPress minimum requirements) + WordPress Core API (register_post_type, wp_insert_post), Advanced Custom Fields (ACF) PRO for the repeater field. (001-contact-enhancements)
- WordPress MySQL Database (`wp_posts` and `wp_postmeta`) (001-contact-enhancements)
- PHP 8.0+, JavaScript (ES6+), HTML5 + WordPress Core, Google reCAPTCHA v3 (001-google-recaptcha-v3)
- WordPress Options Table (`wp_options`) (001-google-recaptcha-v3)
- PHP 8+, JavaScript (ES6+), HTML5 + jQuery, IntersectionObserver API (001-fix-mobile-load-more)
- WordPress Database (WP_Query) (001-fix-mobile-load-more)
- PHP 8.0+ / JavaScript (ES2017, no transpile) + WordPress 6.0+, Tailwind CSS (JIT via `tailwind.config.js`), jQuery (already enqueued) (001-hero-carousel)
- N/A — features uses only existing WordPress post data (`sticky_posts` option, post meta) (001-hero-carousel)
- PHP 8.0+ (strict_types), JavaScript ES6+ + WordPress 6.x, TailwindCSS 3.4, jQuery (bundled with WP) (001-site-ui-updates)
- WordPress `wp_options` table (Customizer theme_mods) (001-site-ui-updates)

- PHP 8.1+, JavaScript (ES6+/jQuery), HTML5, CSS3 + WordPress 6.4+, Tailwind CSS 3.x (compiled), jQuery (WP bundled), Secure Custom Fields (SCF) plugin (001-slice-to-wp-theme)

## Project Structure

```text
src/
tests/
```

## Commands

npm test && npm run lint

## Code Style

PHP 8.1+, JavaScript (ES6+/jQuery), HTML5, CSS3: Follow standard conventions

## Recent Changes
- 001-site-ui-updates: Added PHP 8.0+ (strict_types), JavaScript ES6+ + WordPress 6.x, TailwindCSS 3.4, jQuery (bundled with WP)
- 001-hero-carousel: Added PHP 8.0+ / JavaScript (ES2017, no transpile) + WordPress 6.0+, Tailwind CSS (JIT via `tailwind.config.js`), jQuery (already enqueued)
- 001-fix-mobile-load-more: Added PHP 8+, JavaScript (ES6+), HTML5 + jQuery, IntersectionObserver API


<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
