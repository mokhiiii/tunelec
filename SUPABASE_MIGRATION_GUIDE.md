# Supabase Migration Guide - TunElec Project

This guide walks you through migrating your MySQL database to Supabase (PostgreSQL) for free hosting.

## Step 1: Create a Supabase Project

1. Go to [supabase.com](https://supabase.com)
2. Sign up (free tier available)
3. Click "New project"
4. Fill in:
   - Project name: `tunelec`
   - Database password: Create a strong password
   - Region: Choose closest to you
5. Wait for the project to be created (5-10 minutes)

## Step 2: Get Your Supabase Credentials

After project creation, go to **Settings > Database**:
- Copy the connection string (looks like: `postgresql://user:password@host:port/postgres`)

Also get from **Settings > API**:
- `Project URL` (e.g., `https://xxxxx.supabase.co`)
- `anon public key`

## Step 3: Create Database Tables

Go to **SQL Editor** in Supabase and run these SQL commands:

### Create Users Table:
```sql
CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_users_username ON users(username);
```

### Create Question Images Table:
```sql
CREATE TABLE question_images (
  id BIGSERIAL PRIMARY KEY,
  question_id INT NOT NULL,
  image_data BYTEA NOT NULL,
  image_type VARCHAR(50) NOT NULL,
  upload_date TIMESTAMP DEFAULT NOW(),
  UNIQUE(question_id)
);

CREATE INDEX idx_question_images_question_id ON question_images(question_id);
```

### Create Questions Table (replace CSV):
```sql
CREATE TABLE questions (
  id BIGSERIAL PRIMARY KEY,
  question_text TEXT NOT NULL,
  row_number INT,
  created_at TIMESTAMP DEFAULT NOW()
);
```

## Step 4: Create Environment Configuration File

Create a file named `.env.local` in your project root:

```
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_ANON_KEY=your-anon-public-key
SUPABASE_SERVICE_KEY=your-service-role-key
```

**IMPORTANT:** Add `.env.local` to your `.gitignore` file so you don't push credentials to GitHub:
```
.env.local
.env
```

## Step 5: Create PHP Helper Class for Supabase

Create a file named `supabase.php`:

```php
<?php
class Supabase {
    private $url;
    private $key;
    private $table;
    
    public function __construct($table) {
        $this->url = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL');
        $this->key = $_ENV['SUPABASE_ANON_KEY'] ?? getenv('SUPABASE_ANON_KEY');
        $this->table = $table;
    }
    
    private function request($method, $query = '', $data = null) {
        $url = $this->url . "/rest/v1/{$this->table}";
        if ($query) {
            $url .= "?" . $query;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("Supabase API Error ($httpCode): $response");
        }
        
        return json_decode($response, true);
    }
    
    public function select($filters = '') {
        return $this->request('GET', $filters);
    }
    
    public function insert($data) {
        return $this->request('POST', '', $data);
    }
    
    public function update($id, $data) {
        return $this->request('PATCH', "id=eq.$id", $data);
    }
    
    public function delete($id) {
        $this->request('DELETE', "id=eq.$id");
    }
    
    public function query($filter) {
        return $this->request('GET', $filter);
    }
}
?>
```

## Step 6: Update Your PHP Files

### Update `login_handler.php`:

```php
<?php
session_start();
require_once 'supabase.php';

// Load environment variables
$env_file = __DIR__ . '/.env.local';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    try {
        $supabase = new Supabase('users');
        $results = $supabase->query("username=eq.$user");
        
        if ($results && count($results) > 0) {
            $row = $results[0];
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                header('Location: main.html');
                exit;
            }
        }
        
        header('Location: login.php?error=Identifiants invalides');
        exit;
    } catch (Exception $e) {
        header('Location: login.php?error=Erreur serveur: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
```

### Update `add_user.php`:

```php
<?php
require_once 'supabase.php';

// Load environment variables
$env_file = __DIR__ . '/.env.local';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullname = $_POST['full_name'] ?? '';
    
    try {
        $supabase = new Supabase('users');
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $result = $supabase->insert([
            'username' => $username,
            'password' => $hashedPassword,
            'full_name' => $fullname
        ]);
        
        header('Location: login.php?success=Utilisateur créé avec succès');
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
```

### Update `questions_api.php` to use database:

```php
<?php
require_once 'supabase.php';

// Load environment variables
$env_file = __DIR__ . '/.env.local';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

header('Content-Type: application/json');

try {
    $supabase = new Supabase('questions');
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $questions = $supabase->select('order=id.asc');
            echo json_encode(['success' => true, 'questions' => $questions]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['questions']) || !is_array($input['questions'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid input']);
                exit;
            }
            
            foreach ($input['questions'] as $q) {
                $text = is_array($q) ? ($q['question'] ?? '') : $q;
                $row = is_array($q) ? ($q['row'] ?? 0) : 0;
                
                if (trim($text) !== '') {
                    $supabase->insert([
                        'question_text' => trim($text),
                        'row_number' => $row
                    ]);
                }
            }
            
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
```

## Step 7: Handle Image Storage in Supabase

For binary image data, use Supabase Storage instead:

### Create Storage Bucket in Supabase:
1. Go to **Storage** in Supabase dashboard
2. Create a new bucket named `question-images`
3. Make it public

### Update image upload PHP:

```php
<?php
require_once 'supabase.php';

// Load environment variables
$env_file = __DIR__ . '/.env.local';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $question_id = $_POST['question_id'] ?? null;
    
    if (!$question_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing question_id']);
        exit;
    }
    
    try {
        $supabase_url = getenv('SUPABASE_URL');
        $supabase_key = getenv('SUPABASE_ANON_KEY');
        
        $filename = "question_" . $question_id . "_" . time() . ".jpg";
        $bucket_url = $supabase_url . "/storage/v1/object/question-images/" . $filename;
        
        $ch = curl_init($bucket_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabase_key,
            'Content-Type: ' . $file['type']
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file['tmp_name']));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("Storage upload failed: $response");
        }
        
        // Store image metadata in database
        $supabase = new Supabase('question_images');
        $supabase->insert([
            'question_id' => $question_id,
            'image_type' => $file['type'],
            'image_data' => $bucket_url
        ]);
        
        echo json_encode(['success' => true, 'filename' => $filename]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
```

## Step 8: Deploy to GitHub Pages + API Backend

Since GitHub Pages only hosts static files, you have two options:

### Option A: Use Vercel (Recommended for PHP)
1. Push your PHP code to GitHub
2. Go to [vercel.com](https://vercel.com)
3. Import your GitHub repository
4. Add environment variables from `.env.local`
5. Deploy

### Option B: Use Railway or Render
1. Push your code to GitHub
2. Connect to Railway.app or Render.com
3. Add environment variables
4. Deploy

### Option C: Use GitHub Actions + FTP
If you have web hosting with FTP:
1. Create `.github/workflows/deploy.yml`

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Deploy to FTP
      uses: SamKirkland/FTP-Deploy-Action@v4.3.4
      with:
        server: ${{ secrets.FTP_SERVER }}
        username: ${{ secrets.FTP_USERNAME }}
        password: ${{ secrets.FTP_PASSWORD }}
```

## Step 9: .gitignore Setup

Create/update `.gitignore`:

```
.env.local
.env
.env.production.local
.DS_Store
__pycache__/
*.pyc
new_venv/
vendor/
```

## Step 10: Update Frontend to Use Endpoints

For your frontend JavaScript, update API calls:

```javascript
// Before (local CSV)
fetch('/questions_api.php').then(r => r.json())

// Now works with Supabase database through your PHP API
// No changes needed if you keep the same endpoint names!
fetch('/questions_api.php').then(r => r.json())
```

## Verification Checklist

- [ ] Supabase project created
- [ ] Tables created in Supabase
- [ ] `.env.local` file created with credentials
- [ ] `.env.local` added to `.gitignore`
- [ ] `supabase.php` helper class created
- [ ] `login_handler.php` updated
- [ ] `questions_api.php` updated
- [ ] Test login with Supabase
- [ ] Test adding questions to Supabase
- [ ] Repository pushed to GitHub
- [ ] Deployed to Vercel/Railway/Render
- [ ] Test live API from deployed URL

## Troubleshooting

### "CORS error" when connecting
- Go to Supabase Settings > API > URL Configuration
- Add your deployment URL to allowed origins

### "Unauthorized" errors
- Check that your `SUPABASE_ANON_KEY` is correct
- Make sure tables have Row Level Security disabled (for testing)

### Images not uploading
- Verify your Storage bucket is public
- Check bucket permissions in Supabase dashboard

### Database operations failing
- Check Supabase Activity Logs in dashboard
- Verify table structure matches your queries
- Test with Supabase's built-in SQL editor first

## Next Steps

1. Migrate existing user data from MySQL to Supabase
2. Convert CSV files to database records
3. Update all PHP files to use Supabase helper class
4. Test all functionality locally
5. Deploy to production
