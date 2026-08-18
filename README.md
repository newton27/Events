# GayMen.Online Facebook Events Importer

UNA 15.0.0-RC1 module for importing events from Facebook Page event links into the UNA Events app.

## Important Facebook limitation

This module uses Meta's Graph API and does not scrape Facebook HTML. A Facebook Page access token can only return event fields that Meta permits for the app, Page, token, and approved permissions. A public-looking event link is not a guarantee that the Graph API will return the event.

## Install

1. Copy `modules/newton/gmo_fb_events` into the matching path in the UNA installation.
2. In UNA Studio, install **Facebook Events Importer**.
3. Confirm that UNA's **Events** app is installed and enabled.
4. In Studio Settings, open **Facebook Events Importer** and configure:
   - Meta Graph API version
   - Page access token
   - UNA author profile ID
   - UNA event category ID
   - Default timezone
5. Open `/modules/newton/gmo_fb_events/action.php` while signed in as an administrator.
6. Paste one Facebook event URL per line, preview, then import.

Do not commit a Page access token to GitHub. Store it only in the UNA Studio option.

## Supported URLs

- `https://www.facebook.com/events/123456789012345/`
- `https://facebook.com/some-page/events/123456789012345`
- `https://fb.me/e/...` only when the URL has already expanded to a URL containing the numeric event ID

## Import behavior

- Uses the numeric Facebook event ID as the deduplication key.
- Creates events through UNA's `BxEventsFormsEntryHelper::addData()` pipeline.
- Records successful and failed attempts in `gmo_fb_events_imports`.
- Never deletes UNA events.
- A repeated Facebook ID is skipped after a successful import.

## Meta setup

Create a Meta developer app, connect the Facebook Page, obtain the Page access token and complete any Meta App Review required for event/Page data. The module requests these fields:

`id,name,description,start_time,end_time,place,timezone,cover,event_times`

## Development and review

All changes should be made on an `agent/...` branch and merged through a draft pull request. Never add live tokens or production database exports to the repository.
