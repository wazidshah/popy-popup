=== Popy – Simple Popups ===
Contributors: wazidshah
Tags: popup, modal, timed popup, cookie, WPBakery
Requires at least: 5.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Timed Popups That Respect Your Visitors.

== Description ==

Popy lets you display a beautiful timed popup on your WordPress site — and remembers when a visitor dismisses it so you never annoy them twice.

**How it works**

1. The popup appears after a configurable delay (default: 10 seconds).
2. When a visitor closes it, a cookie is set for a configurable number of days (default: 7).
3. The popup will not appear again until the cookie expires.

**Features**

* Simple on/off toggle — enable or disable the popup instantly.
* Configurable delay (seconds) and cookie lifetime (days).
* Fully editable content: emoji/icon, eyebrow, title, subtitle, body, footnote.
* Two call-to-action buttons with custom labels and URLs (mailto:, tel:, and https:// all supported).
* Optional dismiss link ("No thanks") with custom text.
* Single accent colour picker that styles the primary button and eyebrow label.
* Optional close-on-overlay-click.
* Live preview in the admin panel — see changes as you type.
* "Reset cookie" button in the admin so you can re-test without clearing browser cookies.
* Compatible with WPBakery Page Builder.
* Accessibility-ready: `role="dialog"`, `aria-modal`, focus management, and ESC-key support.
* Respects `prefers-reduced-motion`.
* No third-party scripts, no tracking, no upsells.

== Installation ==

1. Upload the `popy-popup` folder to the `/wp-content/plugins/` directory, or install via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Popy** in the admin sidebar to configure the popup.

== Frequently Asked Questions ==

= Does it show on every page? =

Yes — by default the popup appears on all pages and posts. There is no page-specific targeting in v1.0.0.

= What happens when a visitor closes the popup? =

A cookie (`popy_dismissed`) is set in the visitor's browser for the number of days you configure (default: 7). The popup will not appear again until the cookie expires.

= Can I use mailto: or tel: links in the buttons? =

Yes. Both `mailto:` and `tel:` schemes are supported in the button URL fields.

= Is it compatible with caching plugins? =

Yes. The cookie check happens entirely in JavaScript on the client side, so it works correctly even when pages are served from cache.

== Screenshots ==

1. The popup as it appears to visitors.
2. The Popy admin settings page with live preview.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.