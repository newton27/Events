# Facebook Link Events Importer for UNA 15

This UNA module creates UNA Events from shared Facebook event links without requiring Facebook Login, a Meta app, or a Page access token.

## Workflow

1. In UNA Studio, configure the UNA author profile ID, UNA event category ID, and default timezone.
2. Open Facebook Events Importer.
3. Paste a Facebook event URL.
4. Enter the event title and start date/time.
5. Optionally enter the end date/time, location, and description.
6. Click Create UNA event.

The original Facebook URL is added to the UNA event description as its source. The module rejects duplicate Facebook event IDs that were already imported.

## Installation

Copy the gmo_fb_events module folder into the matching UNA modules directory and install or enable it from Studio. For an existing installation, replace the module files and open the importer once; obsolete Graph API settings are removed automatically.

## Security

Only UNA administrators can access the importer. Submissions use UNA's native CSRF protection. Facebook URLs are validated and must contain a numeric Facebook event ID.
