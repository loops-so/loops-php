## v3.1.0 - Jun 10, 2026

Added support for image uploads and transactional email content management.

- `uploads->upload()` for uploading images.
- `transactional->create()`, `transactional->get()`, `transactional->update()`, `transactional->ensureDraft()`, and `transactional->publish()` for managing transactional emails.

## v3.0.0 - May 19, 2026

Added support for dedicated sending IPs, themes, components, campaigns, and email messages.

Renamed methods for consistency: single-resource lookups use `get()`, collection endpoints use `list()`. This affects `contactProperties`, `mailingLists`, and `transactional` from prior versions.

## v2.1.0 - Apr 8, 2026

Added `contacts->checkSuppression()` and `contacts->removeSuppression()` for managing contact suppressions.

## v2.0.0 - Sep 1, 2025

Added support for using either `$email` or `$user_id` in `contacts->update()`.

## v1.0.2 - May 29, 2025

Fixed an issue with underlying transactional API call attribute names.

## v1.0.1 - May 22, 2025

Added a `headers` argument in `events-send()` and `transactional->send()`, enabling support for the Idempotency-Key header.

## v1.0.0 - May 6, 2025

- Fixed client imports.
- Added test suite and some basic tests.

## v0.1.0 - Feb 27, 2025

Initial release.
