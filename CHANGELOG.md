# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [1.0.1] - 2026-11-07

### Changed
- Nouveau logo (fichier local `logo.png` + marketplace `docs/logo.png`).
- Correction des fichiers `.mo` (header gettext + chaînes NUL-terminées) pour supprimer le warning du loader laminas-i18n.

## [1.0.0] - 2026-11-06

### Added
- "Send an email to the requester" button in the ticket form.
- Pre-filled `mailto:` link with a subject tagged using the entity
  `notification_subject_tag` (default `GLPI`) and the zero-padded ticket id.
- Recipients auto-filled with the ticket requester(s) and the active mail collector.
- English (en_GB) and French (fr_FR) translations.
