# MailTo

A lightweight [GLPI](https://glpi-project.org) plugin that adds a
**"Send an email to the requester"** button to the ticket form.

One click opens your mail client with a pre-filled message:

- **Subject**: `[<entity tag> #<ticket id>] <ticket title>`
- **Recipients**: the ticket requester(s) **and** GLPI's mail collector

The subject tag is read from GLPI's native per-entity
`notification_subject_tag` setting (default `GLPI`), so the collector's
`mailgate` action recognizes the reply and attaches it to the right ticket as a
follow-up. No more copy-pasting the ticket id or the collector address.

## Requirements

- GLPI **11.x** (tested against the 11.0 branch; 12.x compatibility anticipated)
- An **active mail collector** (`Setup > Receivers`) — it is auto-detected and
  added as a recipient
- A working `mailgate` automatic action

## Installation

1. Copy the `mailto` directory into the `plugins/` directory of your GLPI install.
2. Log in as Super-Admin.
3. Go to **Setup > Plugins**: find "MailTo", click **Install**, then **Enable**.

No database table, no configuration required.

## How it works

1. The `pre_show_item` hook fires when a ticket form is displayed.
2. The plugin checks that the current user can update the ticket (so the button
   is not shown to plain requesters).
3. It collects the requester emails (`Ticket_User::getActors()`, handling
   anonymous requesters via `alternative_email`) and the first active collector.
4. It builds a `mailto:` link with a tagged subject using
   `[<tag> #%07d]`, exactly matching GLPI's notification subject format.

## Notes

- The email is sent from the technician's own mailbox (their local mail client);
  the collector being in the recipient list is what records the follow-up.
- If several collectors are active, the first one found is used.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
