# Dokumentasi Queue System

## Persyaratan
1. PHP >= 8.1
2. Laravel >= 10.x
3. Database (MySQL/PostgreSQL/SQLite)
4. Supervisor (opsional, untuk production)

## Setup Awal

### 1. Konfigurasi Database Queue
Pastikan tabel-tabel queue sudah ada dengan menjalankan migrasi:
```bash
php artisan migrate
```

Ini akan membuat 3 tabel:
- `jobs`: Menyimpan jobs yang akan diproses
- `failed_jobs`: Menyimpan jobs yang gagal
- `job_batches`: Untuk batch processing

### 2. Konfigurasi Environment
Di file `.env`, set:
```env
QUEUE_CONNECTION=database
```

### 3. Implementasi Queue di Class
Contoh implementasi di notification class:
```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;  // Jumlah percobaan jika gagal
    public $timeout = 30;  // Batas waktu dalam detik
}
```

## Menjalankan Queue Worker

### Development
```bash
# Basic worker
php artisan queue:work

# Dengan opsi spesifik
php artisan queue:work --tries=3 --timeout=30

# Dalam mode daemon
php artisan queue:work --daemon

# Memproses satu job
php artisan queue:work --once
```

### Production (dengan Supervisor)
1. Install supervisor:
```bash
# Ubuntu/Debian
sudo apt-get install supervisor
```

2. Buat konfigurasi:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --timeout=30
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

3. Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Deployment Queue Worker

### 1. Deployment di Linux/Ubuntu (dengan Supervisor)

#### A. Install Supervisor
```bash
sudo apt-get update
sudo apt-get install supervisor
```

#### B. Buat Konfigurasi Supervisor
1. Buat file konfigurasi:
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

2. Isi dengan konfigurasi:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

3. Aktifkan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

4. Perintah supervisor lainnya:
```bash
# Check status
sudo supervisorctl status

# Stop worker
sudo supervisorctl stop laravel-worker:*

# Restart worker
sudo supervisorctl restart laravel-worker:*
```

### 2. Deployment di Windows Server

#### A. Menggunakan NSSM (Non-Sucking Service Manager)
1. Download NSSM dari https://nssm.cc/

2. Install service menggunakan PowerShell (Run as Administrator):
```powershell
# Buat Windows Service
.\nssm.exe install LaravelQueueWorker

# Set path ke PHP dan artisan
.\nssm.exe set LaravelQueueWorker Application "C:\path\to\php\php.exe"
.\nssm.exe set LaravelQueueWorker AppParameters "C:\path\to\your\project\artisan queue:work"

# Set working directory
.\nssm.exe set LaravelQueueWorker AppDirectory "C:\path\to\your\project"

# Set output logging
.\nssm.exe set LaravelQueueWorker AppStdout "C:\path\to\your\project\storage\logs\worker.log"
.\nssm.exe set LaravelQueueWorker AppStderr "C:\path\to\your\project\storage\logs\worker-error.log"

# Start service
Start-Service LaravelQueueWorker
```

3. Perintah manajemen service:
```powershell
# Check status
Get-Service LaravelQueueWorker

# Stop service
Stop-Service LaravelQueueWorker

# Start service
Start-Service LaravelQueueWorker

# Restart service
Restart-Service LaravelQueueWorker
```

### 3. Best Practices untuk Production

#### A. Monitoring
1. Set up alert untuk:
   - Worker down/crash
   - High fail rate
   - Queue backlog
   - Memory usage

2. Log monitoring:
   - Check worker logs daily
   - Set up log rotation
   - Monitor failed_jobs table

#### B. Performance
1. Jumlah worker:
   - Start dengan 2-3 worker processes
   - Monitor CPU & memory usage
   - Scale berdasarkan kebutuhan

2. Memory management:
   - Set memory limit yang sesuai
   - Restart worker secara berkala
   - Monitor memory leaks

#### C. Maintenance
1. Setelah deploy:
   ```bash
   php artisan queue:restart
   ```

2. Regular cleanup:
   ```bash
   # Clear completed jobs
   php artisan queue:prune-batches

   # Clear old failed jobs
   php artisan queue:prune-failed
   ```

3. Backup strategy:
   - Backup failed_jobs table
   - Backup logs
   - Archive old jobs data

#### D. Security
1. Queue worker harus run sebagai user dengan limited privileges
2. Protect log files dan queue database
3. Regular security updates untuk PHP dan Laravel
4. Monitor unusual patterns dalam job execution

## Monitoring & Maintenance

### 1. Melihat Failed Jobs
```bash
# List failed jobs
php artisan queue:failed

# Retry semua failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry {id}

# Hapus failed job
php artisan queue:forget {id}

# Hapus semua failed jobs
php artisan queue:flush
```

### 2. Monitoring Queue Size
```bash
# Jumlah jobs dalam queue
php artisan queue:size [connection] [queue]
```

### 3. Restart Worker
```bash
# Restart worker setelah deploy
php artisan queue:restart
```

## Best Practices

### 1. Error Handling
```php
try {
    Log::info('Attempting to send email to: ' . $email);
    Notification::route('mail', $email)->notify($notification);
    Log::info('Email notification queued successfully');
} catch (\Exception $e) {
    Log::error('Failed to queue email: ' . $e->getMessage());
}
```

### 2. Job Batching
```php
use Illuminate\Support\Facades\Bus;

Bus::batch([
    new ProcessEmails($emails)
])->then(function (Batch $batch) {
    // Batch selesai
})->catch(function (Batch $batch, Throwable $e) {
    // Error handling
})->dispatch();
```

### 3. Rate Limiting
```php
// Di AppServiceProvider
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('emails', function ($job) {
    return Limit::perMinute(60);
});
```

## Troubleshooting

### 1. Jobs Tidak Diproses
- Pastikan queue worker berjalan
- Cek log di `storage/logs/laravel.log`
- Verifikasi konfigurasi database

### 2. Memory Issues
- Set memory limit di php.ini
- Gunakan `--memory=256` pada queue worker
- Implement chunking untuk batch jobs

### 3. Timeout Issues
- Sesuaikan nilai timeout
- Implementasi job chunking
- Monitor server resources

## Tips
1. Selalu gunakan logging untuk tracking
2. Implement proper error handling
3. Set up monitoring alerts
4. Regular maintenance untuk failed jobs
5. Backup queue tables regularly

## Maintenance Schedule
1. Daily:
   - Monitor failed jobs
   - Check worker status

2. Weekly:
   - Review error logs
   - Clean old failed jobs

3. Monthly:
   - Performance review
   - Database optimization

## Security
1. Encrypt sensitive data dalam queue
2. Implement rate limiting
3. Regular security updates
4. Proper access control

## Cara Kerja Queue System

### 1. Flow Proses Queue
```
[Request] -> [Application] -> [Queue Worker] -> [Email Server]
    |             |               |                  |
    |        Simpan Job     Ambil Job dari     Kirim Email
    |        ke Database    Database & Proses       |
    |             |               |                 |
    └──Response── └──Database ────┴────Log─────────┘
```

### 2. Tahapan Detail

#### A. Penerimaan Request
1. User melakukan request (misal: upload dokumen)
2. Controller menerima request
3. Controller membuat notification job
4. Job disimpan ke database (tabel `jobs`)
5. Response dikirim ke user (tidak perlu menunggu email terkirim)

#### B. Pemrosesan Job
1. Queue worker berjalan di background
2. Worker mengecek tabel `jobs` untuk job baru
3. Job diambil dan diproses (kirim email)
4. Jika berhasil, job dihapus dari tabel
5. Jika gagal:
   - Coba ulang sesuai config ($tries)
   - Jika masih gagal, pindah ke tabel `failed_jobs`

#### C. Monitoring & Logging
1. Setiap tahap dicatat di log
2. Status job bisa dimonitor
3. Failed jobs bisa di-retry
4. Performance bisa dianalisis

### 3. Contoh Case: Kirim Email Notifikasi

#### A. Tanpa Queue
```php
// Proses synchronous (user harus menunggu)
$user->notify(new DocumentUploaded($document));
// Response setelah email terkirim (lambat)
```

#### B. Dengan Queue
```php
// Proses asynchronous
$user->notify(new DocumentUploaded($document));
// Response langsung (cepat)
// Email diproses di background
```

### 4. State Management

#### A. Job States
1. **Pending**: Job baru masuk ke queue
   ```sql
   -- Tabel jobs
   attempts = 0
   reserved_at = NULL
   ```

2. **Processing**: Sedang diproses worker
   ```sql
   -- Tabel jobs
   attempts = 1
   reserved_at = [timestamp]
   ```

3. **Failed**: Gagal setelah max tries
   ```sql
   -- Dipindah ke tabel failed_jobs
   -- Dengan detail error
   ```

4. **Completed**: Berhasil diproses
   ```sql
   -- Dihapus dari tabel jobs
   -- Success log dicatat
   ```

### 5. Database Structure Flow

```
[jobs table]
- Menyimpan job baru
- Track job yang sedang diproses
↓
[failed_jobs table]
- Menyimpan job yang gagal
- Menyimpan error details
↓
[job_batches table]
- Group related jobs
- Track progress batch
```

### 6. Memory Management

#### A. Per Job
- Setiap job diproses secara independen
- Memory di-reset setelah setiap job
- Prevents memory leaks

#### B. Batch Jobs
- Jobs diproses dalam chunks
- Mengoptimalkan resource usage
- Mencegah server overload

### 7. Error Recovery

#### A. Automatic Retry
1. Job gagal → tunggu interval
2. Coba lagi sesuai config
3. Log setiap percobaan

#### B. Manual Recovery
1. Admin cek failed_jobs
2. Analisis error message
3. Fix issue yang ada
4. Retry failed jobs

### 8. Mekanisme Penyimpanan Job

#### A. Automatic Job Creation
1. **Interface dan Trait**
   ```php
   use Illuminate\Contracts\Queue\ShouldQueue; // Interface untuk mark class sebagai queueable
   use Illuminate\Bus\Queueable;               // Trait untuk functionality queue
   
   class CustomEmailNotification extends Notification implements ShouldQueue
   {
       use Queueable;
   ```

2. **Proses di Balik Layar**
   ```php
   // Ketika kita memanggil:
   Notification::route('mail', $email)->notify($notification);
   
   // Laravel secara otomatis:
   // 1. Serialize notification object
   // 2. Insert ke tabel jobs:
   /*
   INSERT INTO jobs (
       queue,
       payload,    -- Berisi serialized notification
       attempts,   -- Set ke 0
       created_at,
       available_at
   ) VALUES (...)
   */
   ```

#### B. Struktur Data Job
1. **Payload Structure**
   ```json
   {
     "uuid": "123e4567-e89b-12d3-a456-426614174000",
     "displayName": "CustomEmailNotification",
     "job": "Illuminate\\Notifications\\SendQueuedNotifications",
     "data": {
       "command": "O:48..."   // Serialized notification object
     },
     "maxTries": 3,
     "timeout": 30,
     "timeoutAt": null
   }
   ```

2. **Database Record**
   ```sql
   -- Contoh record di tabel jobs
   id: 1
   queue: "default"
   payload: "{"uuid":"123e4567..."}"
   attempts: 0
   reserved_at: null
   available_at: 1684989600
   created_at: 1684989600
   ```

#### C. Trigger Points
1. **Notification Dispatch**
   ```php
   // Di NotificationController.php
   try {
       Notification::route("mail", ["email" => $email])
           ->notify($notification);
       // ^ Trigger point: Job disimpan ke database
   } catch (\Exception $e) {
       Log::error("Failed to queue email: " . $e->getMessage());
   }
   ```

2. **Document Upload Notification**
   ```php
   // Di DocumentController.php
   $user->notify(new DocumentUploaded($document));
   // ^ Trigger point: Job disimpan ke database
   ```

#### D. Konfigurasi Queue
1. **Di Class Notification**
   ```php
   public $tries = 3;      // Jumlah retry jika gagal
   public $timeout = 30;   // Max execution time
   public $maxExceptions = 3; // Max jumlah exception sebelum failed
   
   // Delay processing (opsional)
   public $delay = 60; // Delay 60 detik
   ```

2. **Di .env**
   ```env
   QUEUE_CONNECTION=database
   QUEUE_RETRY_AFTER=90    # Waktu (detik) sebelum retry
   QUEUE_FAILED_DRIVER=database-uuids # Driver untuk failed jobs
   ```

#### E. Database Interactions

1. **Insert Job**
   ```php
   // Yang Laravel lakukan di background
   DB::table('jobs')->insert([
       'queue' => 'default',
       'payload' => serialize($notification),
       'attempts' => 0,
       'available_at' => now()->timestamp,
       'created_at' => now()->timestamp,
   ]);
   ```

2. **Process Job**
   ```php
   // Yang Queue Worker lakukan
   DB::transaction(function () use ($job) {
       // 1. Lock job
       DB::table('jobs')
           ->where('id', $job->id)
           ->update(['reserved_at' => now()->timestamp]);
       
       // 2. Process job
       // 3. If success: delete job
       // 4. If fail: increment attempts or move to failed_jobs
   });
   ```

## Setup dengan Docker

### 1. Multi-Container Setup
Kita perlu minimal 2 container:
1. Container untuk aplikasi web
2. Container khusus untuk queue worker

### 2. Dockerfile
```dockerfile
# Base image yang sama untuk web dan queue
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# Copy aplikasi
COPY . /var/www/html/
WORKDIR /var/www/html

# Install composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage
```

### 3. Docker Compose
```yaml
version: '3'
services:
  # Web Server
  app:
    build: .
    volumes:
      - .:/var/www/html
    networks:
      - app-network
    depends_on:
      - db

  # Queue Worker
  queue:
    build: .
    command: php artisan queue:work --tries=3 --timeout=90
    volumes:
      - .:/var/www/html
    networks:
      - app-network
    depends_on:
      - db
    deploy:
      replicas: 2  # Jumlah worker processes
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 3

  # Database
  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  dbdata:
```

### 4. Menjalankan Services
```bash
# Build dan jalankan containers
docker-compose up -d

# Check status containers
docker-compose ps

# Lihat logs queue worker
docker-compose logs -f queue

# Scale jumlah queue workers
docker-compose up -d --scale queue=3
```

### 5. Monitoring di Docker

#### A. Check Queue Worker Logs
```bash
# Lihat logs realtime
docker-compose logs -f queue

# Lihat logs terakhir
docker-compose logs --tail=100 queue
```

#### B. Masuk ke Container
```bash
# Masuk ke container queue
docker-compose exec queue bash

# Cek status queue
php artisan queue:status

# List failed jobs
php artisan queue:failed
```

#### C. Health Checks
1. Tambahkan di docker-compose.yml:
```yaml
services:
  queue:
    # ...existing config...
    healthcheck:
      test: ["CMD", "php", "artisan", "queue:status"]
      interval: 30s
      timeout: 10s
      retries: 3
```

### 6. Best Practices untuk Docker

#### A. Container Management
1. **Restart Policy**
   ```yaml
   services:
     queue:
       restart: unless-stopped
   ```

2. **Resource Limits**
   ```yaml
   services:
     queue:
       deploy:
         resources:
           limits:
             cpus: '0.50'
             memory: 512M
   ```

#### B. Logging
1. **Log Driver Config**
   ```yaml
   services:
     queue:
       logging:
         driver: "json-file"
         options:
           max-size: "10m"
           max-file: "3"
   ```

#### C. Scaling
1. **Manual Scaling**
   ```bash
   docker-compose up -d --scale queue=3
   ```

2. **Auto-scaling dengan Docker Swarm**
   ```yaml
   deploy:
     mode: replicated
     replicas: 2
     update_config:
       parallelism: 1
       delay: 10s
     restart_policy:
       condition: on-failure
   ```

### 7. Deployment Steps

1. **Initial Deploy**
   ```bash
   # Clone repo dan masuk ke directory
   git clone <repository> && cd <directory>

   # Copy dan setup environment
   cp .env.example .env
   
   # Build dan start services
   docker-compose up -d --build
   
   # Run migrations
   docker-compose exec app php artisan migrate
   ```

2. **Updates/Maintenance**
   ```bash
   # Pull updates
   git pull

   # Rebuild containers
   docker-compose up -d --build

   # Restart queue workers
   docker-compose restart queue
   ```

3. **Rollback**
   ```bash
   # Rollback ke versi sebelumnya
   git checkout <previous-version>
   docker-compose up -d --build
   ```

### 8. Troubleshooting di Docker

#### A. Common Issues
1. **Queue Worker Mati**
   - Check logs: `docker-compose logs queue`
   - Check memory usage: `docker stats`
   - Restart container: `docker-compose restart queue`

2. **Memory Issues**
   - Increase container limits
   - Check memory leaks
   - Monitor dengan `docker stats`

3. **Koneksi Database**
   - Verify network connectivity
   - Check environment variables
   - Ensure database is ready
