# Audio Quality Score

A small Laravel 13 API for uploading MP3 files, extracting basic MPEG frame metadata, assigning a simple quality score, and detecting exact duplicate uploads by content hash.

## Requirements

- PHP 8.4.1+
- Composer
- MySQL 8+

## Local Setup

### Docker

Build and start Laravel with MySQL:

```bash
docker compose up --build
```

The API will be available at:

```text
http://127.0.0.1:8000
```

The app container waits for MySQL, generates an app key if needed, and runs migrations automatically.

Run tests inside Docker:

```bash
docker compose exec app php artisan test
```

Stop the stack:

```bash
docker compose down
```

Remove database/storage volumes too:

```bash
docker compose down -v
```

### Manual PHP/MySQL

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database:

```sql
CREATE DATABASE audio_quality_score CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Confirm these values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=audio_quality_score
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and start the API:

```bash
php artisan migrate
php artisan serve
```

Upload an MP3:

```bash
curl -X POST http://127.0.0.1:8000/api/upload \
  -F "file=@/path/to/audio.mp3"
```

Run tests:

```bash
php artisan test
```

Tests use in-memory SQLite through `phpunit.xml` so they do not require a local MySQL database.

## API

`POST /api/upload`

Multipart field:

- `file`: required `.mp3`, max 50 MB

The response includes:

- upload id
- duplicate status and original upload id when applicable
- duration in seconds and `mm:ss`
- duration outlier flag
- quality score from 1 to 10
- bitrate, sample rate, and file size

## Architecture

- `AudioUploadController` owns request validation, storage, persistence, and response shape.
- `Mp3Analyzer` is a small service that reads MP3 frame headers directly, estimates duration, extracts bitrate/sample rate, and computes the quality score.
- `AudioUpload` stores each upload attempt. Duplicate uploads are still recorded, but point at the earliest original upload with matching SHA-256 content hash.
- Local Laravel storage is used for uploaded audio files.

## Assumptions

- Duplicate detection is exact-match only via SHA-256 of file contents; filenames do not matter.
- The analyzer focuses on common MPEG Layer III frames. It skips ID3v2 tags and scans audio frames, but it is intentionally not a full media parser.
- Duration outliers are files under 1 second or over 1 hour, or files whose duration cannot be parsed.
- Quality score starts at 1 and adds:
  - up to 4 points for bitrate
  - up to 3 points for sample rate
  - up to 2 points for bytes per second

These signals are simple, explainable proxies for MP3 quality without ML or advanced signal processing.

## Trade-offs

- A hand-written MP3 parser keeps dependencies low and makes the heuristic easy to inspect, but it will not handle every edge case that `ffprobe` or a mature media library would.
- The API stores duplicate files as separate upload attempts to preserve auditability. A storage dedupe strategy would save disk space but adds lifecycle complexity.
- Validation enforces `.mp3` extension and size. Deeper content validation is left to the analyzer metadata result.

## With More Time

- Use `ffprobe` or a maintained media parser for broader MP3/VBR metadata support.
- Add authentication and per-user upload history.
- Add async analysis for larger files.
- Add storage lifecycle cleanup and optional deduped blobs.
- Return richer validation errors for malformed MP3 content.
