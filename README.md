# ClubSuite Training

[![Nextcloud Version](https://img.shields.io/badge/Nextcloud-28--32-blue.svg)](https://nextcloud.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1--8.3-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-AGPL%20v3-green.svg)](LICENSE)

> 🏃 Trainings- und Probenplanung für Vereine.

## 📋 Übersicht

ClubSuite Training koordiniert Ihre Vereinstermine:

- **Terminplanung**: Wiederkehrende und Einzeltermine
- **Räume**: Raumbelegungsplanung und -konflikte
- **Anwesenheit**: Teilnahmeerfassung pro Termin
- **Benachrichtigungen**: Erinnerungen an Teilnehmer
- **Kalender**: Übersicht aller Termine, Sync möglich

## 🚀 Installation

### Über den Nextcloud App Store
1. **ClubSuite Core** muss installiert sein
2. Apps → Organisation → "ClubSuite Training" suchen
3. Installieren und aktivieren

### Manuelle Installation
```bash
cd /path/to/nextcloud/apps
git clone https://github.com/clubsuite/clubsuite-training.git
php occ app:enable clubsuite-training
```

## 📦 Anforderungen

| Komponente | Version |
|------------|--------|
| Nextcloud | 28 - 32 |
| PHP | 8.1 - 8.3 |
| **clubsuite-core** | erforderlich |

## 🔒 DSGVO / Datenschutz

- Anwesenheitsdaten mit Personenbezug geschützt
- Datenexport über Nextcloud Privacy API
- Löschung/Anonymisierung möglich

## 📄 Lizenz

AGPL v3 – Siehe [LICENSE](LICENSE)

## 🐛 Bugs & Feature Requests

[GitHub Issues](https://github.com/clubsuite/clubsuite-training/issues)

---

© 2026 Stefan Schulz
