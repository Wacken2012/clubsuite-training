# ClubSuite Training App - Teilnehmerverwaltung & Anwesenheitserfassung

## ✅ Implementation Status: COMPLETED

### 📋 Überblick

Die ClubSuite Training App wurde erfolgreich um eine vollständige **Teilnehmerverwaltung und Anwesenheitserfassung** erweitert. Das System ist vollständig kompatibel mit Nextcloud 32 und folgt ausschließlich den offiziellen Developer Docs.

---

## 🗄️ Datenbankstruktur

### Tabelle: `training_attendance`

```sql
CREATE TABLE training_attendance (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    event_id INTEGER NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    member_id INTEGER NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'absent',
    checked_in_at DATETIME NULL,
    checked_out_at DATETIME NULL,
    FOREIGN KEY (event_id) REFERENCES training_event(id)
)
```

**Status-Werte:**
- `present` (Anwesend) - Grün
- `absent` (Abwesend) - Rot
- `excused` (Entschuldigt) - Gelb

---

## 📦 Backend-Implementation

### 1. AttendanceEntity (`lib/Db/AttendanceEntity.php`)

**Eigenschaften:**
- `eventId: int` - Referenz zum Trainingstermin
- `userId: string` - Nextcloud Benutzer-ID
- `memberId: ?int` - Mitglieds-ID (optional, aus ClubSuite Core)
- `status: string` - Status ('present'|'absent'|'excused')
- `checkedInAt: ?DateTime` - Zeitstempel Check-in
- `checkedOutAt: ?DateTime` - Zeitstempel Check-out

**Methoden:**
```php
public static function build(array $params): static
public function jsonSerialize(): array
```

**Getter/Setter:** Standard Nextcloud-Konvention

---

### 2. AttendanceMapper (`lib/Db/AttendanceMapper.php`)

**CRUD-Operationen:**

```php
public function create(AttendanceEntity $a): AttendanceEntity
public function update(AttendanceEntity $a): AttendanceEntity
public function findById(int $id): ?AttendanceEntity
public function findByEvent(int $eventId): array
public function findByMember(int $memberId): array
public function updateStatus(int $id, string $status): void
public function findWithEventByUser(string $userId): array
```

**QueryBuilder Usage:**
- Nutzt `IDBConnection::getQueryBuilder()` für alle Operationen
- Parameterized Queries mit `createNamedParameter()`
- Keine direkten SQL-Strings

---

### 3. AttendanceService (`lib/Service/AttendanceService.php`)

**Geschäftslogik:**

```php
public function checkIn(int $eventId, string $userId, ?int $memberId = null): AttendanceEntity
public function checkOut(int $attendanceId): AttendanceEntity
public function setStatus(int $id, string $status): void
public function getAttendanceForEvent(int $eventId): array
public function getAttendanceForMember(int $memberId): array
public function listByEvent(int $eventId): array
public function listByUser(string $userId): array
public function listByMember(int $memberId): array
```

---

### 4. AttendanceController (`lib/Controller/AttendanceController.php`)

**API-Endpunkte:**

| Methode | Route | Beschreibung |
|---------|-------|-------------|
| GET | `/apps/clubsuite-training/attendance` | Liste Teilnehmer (mit Filtern) |
| POST | `/apps/clubsuite-training/attendance` | Check-in |
| PUT | `/apps/clubsuite-training/attendance/{id}` | Status ändern |

**Query-Parameter:**
- `event_id`: Termine filtern
- `user_id`: Nach Benutzer filtern
- `member_id`: Nach Mitglied filtern

**Responses:** `DataResponse` mit `AttendanceEntity[]` oder Fehlermeldungen

---

### 5. Migration (`lib/Migration/Version000000Date20260115120000.php`)

Fügt neue Spalten zur bestehenden Tabelle hinzu:
- `member_id` (INTEGER NULL)
- `checked_in_at` (DATETIME NULL)
- `checked_out_at` (DATETIME NULL)

**Sicherheit:**
- Conditional checks: `if (!$table->hasColumn('member_id'))`
- ISchemaWrapper für sichere Migrationen

---

## 🎨 Frontend-Implementation

### App.vue - Anwesenheitsseite

**Features:**

1. **Terminwahl** (Select-Dropdown)
   - Alle Termine laden
   - Nach Titel und Zeit anzeigen

2. **Teilnehmerliste** (Tabelle)
   - Benutzer-ID
   - Status-Badge (farbig)
   - Check-in/Check-out Zeiten
   - Action-Buttons pro Zeile

3. **Statusverwaltung**
   - Button "Anwesend" → `status: 'present'` (grün)
   - Button "Abwesend" → `status: 'absent'` (rot)
   - Button "Entschuldigt" → `status: 'excused'` (gelb)

4. **Teilnehmer Hinzufügen**
   - Eingabefelder: `user_id`, `member_id` (optional)
   - POST `/attendance` mit Termin-ID

**CSS-Styling:**
```css
.badge-present { background: #4CAF50; } /* Grün */
.badge-absent { background: #F44336; }  /* Rot */
.badge-excused { background: #FFC107; } /* Gelb */

.status-present { background: rgba(76, 175, 80, 0.1); }
.status-absent { background: rgba(244, 67, 54, 0.1); }
.status-excused { background: rgba(255, 193, 7, 0.1); }
```

---

## 🔗 API-Beispiele

### Check-in
```bash
POST /apps/clubsuite-training/attendance
Content-Type: application/json

{
  "event_id": 5,
  "user_id": "john.doe",
  "member_id": 123
}

Response (201):
{
  "id": 42,
  "event_id": 5,
  "user_id": "john.doe",
  "member_id": 123,
  "status": "present",
  "checked_in_at": "2026-01-15T20:05:00+00:00",
  "checked_out_at": null
}
```

### Teilnehmer eines Termins laden
```bash
GET /apps/clubsuite-training/attendance?event_id=5

Response (200):
[
  {
    "id": 42,
    "event_id": 5,
    "user_id": "john.doe",
    "member_id": 123,
    "status": "present",
    "checked_in_at": "2026-01-15T20:05:00+00:00",
    "checked_out_at": "2026-01-15T21:00:00+00:00"
  },
  ...
]
```

### Status ändern
```bash
PUT /apps/clubsuite-training/attendance/42
Content-Type: application/json

{
  "status": "excused"
}

Response (200):
{ "status": "ok" }
```

---

## 🚀 Deployment

### Files Deployed

✅ **Backend (PHP):**
- `lib/Migration/Version000000Date20260115120000.php`
- `lib/Db/AttendanceEntity.php` (erweitert)
- `lib/Db/AttendanceMapper.php` (erweitert)
- `lib/Service/AttendanceService.php` (erweitert)
- `lib/Controller/AttendanceController.php` (erweitert)

✅ **Frontend (Vue.js):**
- `src/App.vue` (mit Anwesenheitsseite)
- `js/main.js` (kompiliert, 170 KiB)

### Installation Steps

```bash
# 1. PHP-Dateien kopieren
sudo cp lib/Migration/*.php /var/www/html/nextcloud/apps/clubsuite-training/lib/Migration/
sudo cp lib/Db/*.php /var/www/html/nextcloud/apps/clubsuite-training/lib/Db/
sudo cp lib/Service/*.php /var/www/html/nextcloud/apps/clubsuite-training/lib/Service/
sudo cp lib/Controller/*.php /var/www/html/nextcloud/apps/clubsuite-training/lib/Controller/

# 2. Frontend bauen und deployen
npm run build
sudo cp js/main.js /var/www/html/nextcloud/apps/clubsuite-training/js/

# 3. Migration ausführen
cd /var/www/html/nextcloud
sudo -u www-data php occ db:add-missing-columns

# 4. App neuladen
sudo -u www-data php occ app:disable clubsuite-training
sudo -u www-data php occ app:enable clubsuite-training
```

---

## ✅ Tests & Validierung

### PHP Syntax
```
✓ AttendanceEntity.php - No syntax errors
✓ AttendanceMapper.php - No syntax errors
✓ AttendanceService.php - No syntax errors
```

### Database Migration
```
✓ db:add-missing-columns - Done
```

### App Status
```
✓ clubsuite-training 0.1.1 enabled
✓ No training-specific errors in logs
```

### Frontend Build
```
✓ npm run build - Compiled successfully
✓ main.js - 170 KiB (contains attendance code)
```

---

## 📝 Verwendete Nextcloud APIs

### AppFramework
- `Controller` - HTTP Request Handling
- `Entity` - ORM Base Class
- `Db\Entity::build(array $params): static` - Factory Pattern

### Database
- `IDBConnection::getQueryBuilder()`
- `QueryBuilder::insert()->values()->executeStatement()`
- `QueryBuilder::update()->set()->executeStatement()`
- `QueryBuilder::select()->from()->where()->executeQuery()`
- `ISchemaWrapper` - Safe schema changes

### HTTP
- `JSONResponse` - JSON serialization
- `DataResponse` - Generic responses
- Attribute `#[NoAdminRequired]` - Permission handling
- Attribute `#[NoCSRFRequired]` - CSRF control

### Routing
- `appinfo/routes.php` - Endpoint definitions

---

## 🎯 Nächste Schritte (Optional)

1. **Integration mit ClubSuite Core**
   - Member-Autocomplete beim Hinzufügen
   - Automatisches Mapping `user_id` ↔ `member_id`

2. **Reports & Statistiken**
   - Anwesenheitsquote pro Mitglied
   - Trends über Zeit
   - Export zu CSV/PDF

3. **Benachrichtigungen**
   - E-Mail bei Abwesenheit
   - Reminder vor Trainings

4. **Konfigurierbare Status**
   - Admin-Interface für Status-Definitionen
   - Farbschemas anpassbar

---

## 📄 Copyright

© 2026 Stefan Schulz – Alle Rechte vorbehalten.

---

## 📚 Dokumentation

Alle Code-Beispiele folgen:
- **Nextcloud Developer Manual**: https://docs.nextcloud.com/server/latest/developer_manual/
- **AppFramework Documentation**: Database, Controllers, Entities & Mappers
- **Vue.js 2.7 Integration**: @nextcloud/axios, @nextcloud/router
- **NC32 Best Practices**: Strict Types, QueryBuilder, Dependency Injection

