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

- `file`: required `.mp3`, max 64 MB

**Response:** `202 Accepted`
```json
{
  "id": 1,
  "status": "pending",
  "message": "File uploaded successfully. Processing in background."
}
```

`GET /api/upload/{id}`

Fetches the analysis results for a specific upload.

**Response:** `200 OK`
```json
{
  "id": 1,
  "status": "completed",
  "original_filename": "audio.mp3",
  "file_size_bytes": 47712138,
  "is_duplicate": false,
  "duplicate": {
    "is_duplicate": false,
    "original_upload_id": null
  },
  "analysis": {
    "duration_seconds": 123.45,
    "duration": "02:03",
    "is_duration_outlier": false,
    "quality_score": 8,
    "bitrate_kbps": 128,
    "sample_rate_hz": 44100
  }
}
```

## Architecture

- `AudioUploadController` owns request validation, storing the temporary file, and dispatching the background job.
- **Asynchronous Processing**: Analysis is dispatched to a background queue (`ProcessAudioUpload` job). This ensures that large file uploads do not cause web server timeouts during analysis, which is a critical improvement over synchronous processing.
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

- **Async over Sync**: The original requirement suggested returning the analysis results directly in the initial `POST` response. However, to prioritize stability and scalability for large files (up to 64MB), we made the explicit architectural trade-off to use a polling mechanism (`202 Accepted` + `GET` endpoint) with a background job.
- A hand-written MP3 parser keeps dependencies low and makes the heuristic easy to inspect, but it will not handle every edge case that `ffprobe` or a mature media library would.
- The API stores duplicate files as separate upload attempts to preserve auditability. A storage dedupe strategy would save disk space but adds lifecycle complexity.
- Validation enforces `.mp3` extension and size. Deeper content validation is left to the analyzer metadata result.

## With More Time

- Use `ffprobe` or a maintained media parser for broader MP3/VBR metadata support.
- Implement WebSockets or Server-Sent Events (SSE) to push status updates to the client instead of requiring polling.
- Add storage lifecycle cleanup and optional deduped blobs.
- Return richer validation errors for malformed MP3 content.
