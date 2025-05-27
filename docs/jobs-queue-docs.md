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
Kita perlu 2 container saja:
1. Container untuk aplikasi web
2. Container khusus untuk queue worker

### 2. Konfigurasi untuk External Database

#### A. Environment Variables
```env
DB_CONNECTION=mysql
DB_HOST=host.docker.internal     # Untuk development di Windows
# DB_HOST=172.17.0.1            # Untuk Linux VPS (IP docker bridge)
# DB_HOST=10.10.10.10          # Atau IP VPS Anda
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### B. Docker Compose untuk External DB
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
    environment:
      - DB_HOST=${DB_HOST}
      - DB_PORT=${DB_PORT}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}

  # Queue Worker
  queue:
    build: .
    command: php artisan queue:work --tries=3 --timeout=90
    volumes:
      - .:/var/www/html
    networks:
      - app-network
    environment:
      - DB_HOST=${DB_HOST}
      - DB_PORT=${DB_PORT}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
    deploy:
      replicas: 2
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 3

networks:
  app-network:
    driver: bridge

# Tidak perlu volumes karena database di luar Docker
```

### 3. Keuntungan Database di VPS

1. **Data Persistence**
   - Data tersimpan permanen di VPS
   - Tidak hilang saat container di-rebuild
   - Backup lebih straightforward

2. **Performance**
   - Tidak ada overhead container untuk database
   - Resource VPS bisa dioptimalkan
   - Network latency lebih rendah

3. **Maintenance**
   - Update database bisa dilakukan terpisah
   - Tidak perlu khawatir dengan Docker volume
   - Backup/restore lebih mudah

### 4. Hal yang Perlu Diperhatikan

1. **Network Access**
   - Pastikan firewall mengizinkan akses dari container ke database
   - Set MySQL bind-address dengan benar
   - Gunakan credentials yang aman

2. **Development vs Production**
   ```env
   # Development (Windows)
   DB_HOST=host.docker.internal
   
   # Production (Linux VPS)
   DB_HOST=172.17.0.1  # Docker bridge IP
   # atau
   DB_HOST=10.10.10.10 # IP VPS Anda
   ```

3. **Security**
   - Batasi akses database hanya dari IP yang diperlukan
   - Gunakan strong password
   - Regular security updates untuk MySQL/MariaDB

### 5. Deployment Steps

1. **Database Setup di VPS**
   ```bash
   # Create database dan user
   mysql -u root -p
   CREATE DATABASE your_database;
   CREATE USER 'your_user'@'%' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'%';
   FLUSH PRIVILEGES;
   
   # Configure MySQL untuk accept connections
   # Edit /etc/mysql/mysql.conf.d/mysqld.cnf
   # Change bind-address = 127.0.0.1
   # to bind-address = 0.0.0.0
   ```

2. **Deploy Containers**
   ```bash
   # Build dan start containers
   docker-compose up -d --build
   
   # Verify connections
   docker-compose exec app php artisan migrate:status
   ```

### 6. Troubleshooting

1. **Connection Issues**
   ```bash
   # Test database connection dari container
   docker-compose exec app php artisan tinker
   >>> DB::connection()->getPdo();
   ```

2. **Network Issues**
   ```bash
   # Test network dari container
   docker-compose exec app bash
   ping $DB_HOST
   telnet $DB_HOST 3306
   ```




(Tutorial)[https://dev.to/enrico_dev86/laravel-notification-system-with-queue-28p4]