# mrmk Site Tools

mrmk Site Tools is a collection of optional tools for changing common WordPress site, security, privacy, and administration behavior.

Each feature is independent and disabled by default. Enable only the features you need, and review their effects before using them on a production site.

## Installation

1. Copy the `mrmk-site-tools` directory into `wp-content/plugins/`.
2. Activate **mrmk Site Tools** from **Plugins** in the WordPress admin.
3. Open **Settings > mrmk Site Tools**.
4. Enable the desired features and select **Save changes**.

## Features

### General

- **Block New Comments** — prevents new comments from being submitted; existing comments remain visible and stored.
- **Disable Author Pages** — returns a 404 for author archives and removes standard author archive links from the front end.
- **Disable Comments** — hides existing comments on public pages, blocks new comments, removes the admin-bar Comments menu item and the Discussion/Comments postboxes from the editor, and returns 404 for comment feeds. Comments remain stored and accessible in the Comments admin screen.
- **Disable Feed Links** — removes feed discovery links and returns 404 for feed URLs.
- **Disable WP Font Library** — removes the Fonts admin page, disables its REST API, and prevents user-managed font faces from loading. Existing font data remains stored, and theme fonts are unaffected.
- **Disable Tags** — removes the built-in post tags editing interface, hides tags and tag links from public output, and returns 404 for tag archives. Existing tags and post assignments remain stored.
- **Hide Default Category** — hides WordPress's configured default category (commonly “Uncategorized”) from public category lists, post category links and returns 404 for its archive. Posts remain assigned to it.
- **Limit Image Upload Size** — sets a configurable maximum image file size in KB. Videos and other file types are unaffected.
- **Remove Adjacent-Post Links** — removes previous and next post relation links from the document head.
- **Remove Login Language Switcher** — removes the language selector from the WordPress login page.
- **Remove WP Shortlink** — removes the shortlink from the document head and HTTP `Link` header.

### Security

- **Disable Application Passwords** — disables WordPress Application Passwords.
- **Disable Theme and Plugin File Editors** — blocks the built-in Theme File Editor and Plugin File Editor, including direct access. Plugin and theme installation and updates remain available.
- **Disable Password Reset** — hides password recovery links and blocks the public password reset flow.
- **Disable Pingbacks and Trackbacks** — blocks incoming and outgoing pingbacks and trackbacks, and removes the Send Trackbacks postbox from the editor. Existing ping comments are kept.
- **Disable REST API** — blocks REST API requests from unauthenticated users and removes standard REST API and oEmbed discovery links.
- **Disable XML-RPC** — disables the XML-RPC API and removes its RSD discovery link.
- **Remove Users from REST** — blocks public access to REST API user listings and individual user endpoints, and hides those routes from the API index. Users with the `list_users` capability retain access.
- **Remove WP generator meta tag** — removes the WordPress generator meta tag from the document head.
- **Restrict Login to Administrators** — rejects logins from users without the administrator role and logs out their existing sessions.

### Privacy

- **Hide Author Information** — hides standard author details on public pages and returns a 404 for author archives. Feeds, REST responses, and admin output are unchanged.

### Performance

- **Disable WP Emoji support** — prevents WordPress emoji scripts and styles from loading.

### Dashboard

- **Disable Browser check in Dashboard** — disables the Browse Happy check and its remote request.
- **Disable PHP version check in Dashboard** — disables the Dashboard PHP version check and its remote request.
- **Remove WP News dashboard widget** — removes the WordPress Events and News widget from the Dashboard.
- **Set Dashboard as WP Logo Link** — makes the admin-bar WordPress logo open the Dashboard and removes Get Involved, Support, and Feedback submenu links.

### Updates

- **Disable Auto updates - Plugins** — disables automatic updates for plugins.
- **Disable Auto updates - Translations** — disables automatic translation updates; translation updates can still be installed manually.
- **Disable Auto updates - Themes** — disables automatic updates for themes.

## Important notes

- **Disable Comments** includes the behavior of **Block New Comments**. Use **Block New Comments** alone if existing comments should remain visible.
- **Disable REST API** and **Remove Users from REST** overlap. The latter is intended for sites that want to keep the REST API available while restricting its user endpoints.
- Disabling automatic updates means updates must be applied manually.
- **Restrict Login to Administrators** can log out existing non-administrator users. Enable it only if those users should no longer have access.
- Features use WordPress hooks and filters. A theme or another plugin may provide custom behavior that these features do not cover. Test changes with your active theme and plugins, preferably on a staging site first.

## Compatibility

The plugin header currently reports testing up to WordPress 7.1.4. Minimum WordPress and PHP versions are not declared yet.

## Development

Feature modules are stored in `includes/features/` and are discovered automatically. To add a feature, create a `class-*.php` module that implements `MRMK_ST_Feature_Interface`, provide its settings metadata, and register its behavior in `boot()`. Features should default to disabled unless there is a specific reason otherwise.

Run PHP syntax checks from the plugin directory with:

```sh
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

## License

GPL-3.0-or-later. See [LICENSE](LICENSE).
