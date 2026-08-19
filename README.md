# UNA Facebook Importer for UNA 15

This UNA module creates UNA Events from shared Facebook event links without requiring Facebook Login, a Meta app, or a Page access token.

## Workflow

1. In UNA Studio, configure the UNA author profile ID, UNA event category ID, and default timezone.
2. Open UNA Facebook Importer.
3. Paste a Facebook event URL.
4. Enter the event title and start date/time.
5. Optionally enter the end date/time, location, and description.
6. Click Create UNA event.

The original Facebook URL is added to the UNA event description as its source. The module rejects duplicate Facebook event IDs that were already imported.

## Installation

Copy `modules/newton/gmo_fb_events` to the same path under the UNA installation, then install or enable it in Studio. The directory placed on the server must contain `install/config.php` directly beneath the module root; do not upload the repository wrapper folder as the module itself.

For an existing installation, replace the module files, recompile the English language in Studio if the displayed name is cached, clear UNA's cache, and reopen Studio.

## Security

Only UNA administrators can access the importer. Submissions use UNA's native CSRF protection. Facebook URLs are validated and must contain a numeric Facebook event ID.

## Compatibility and validation

- Target: UNA 15.0.x with the UNA Events module 15.0.0 or newer.
- Internal module ID: `gmo_fb_events` (retained for upgrade compatibility).
- The importer is restricted to UNA administrators and uses UNA's CSRF validation.
- The link parser accepts only numeric event URLs on approved `facebook.com` hosts.
- Run `php tests/extract_event_id_test.php` and `php tests/package_structure_test.php` from the repository root for the standalone checks.
