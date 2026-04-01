# TunElec - Supabase Migration Quick Start

Follow these steps to migrate your project from MySQL to Supabase.

## 1️⃣ Quick Setup (15 minutes)

### Create Supabase Project
1. Go to https://supabase.com → Sign up (free)
2. Create new project: `tunelec`
3. Wait for it to be created (~5-10 min)
4. Go to **Settings > API** and copy:
   - `Project URL`
   - `anon public key`

### Create .env.local File
1. Copy `.env.local.example` to `.env.local`
2. Fill in your Supabase credentials:
   ```
   SUPABASE_URL=https://your-id.supabase.co
   SUPABASE_ANON_KEY=your-key-here
   ```
3. Save and **don't commit this file** (it's in .gitignore)

### Create Database Tables
In Supabase, go to **SQL Editor** and run:

```sql
-- Users table
CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT NOW()
);

-- Questions table (replaces CSV)
CREATE TABLE questions (
  id BIGSERIAL PRIMARY KEY,
  question_text TEXT NOT NULL,
  row_number INT,
  created_at TIMESTAMP DEFAULT NOW()
);

-- Question images table
CREATE TABLE question_images (
  id BIGSERIAL PRIMARY KEY,
  question_id INT NOT NULL,
  image_type VARCHAR(50) NOT NULL,
  image_data TEXT,
  upload_date TIMESTAMP DEFAULT NOW(),
  UNIQUE(question_id)
);
```

## 2️⃣ Testing Locally

Your PHP files are **already updated** with Supabase support!

Files created for you:
- ✅ `supabase.php` - Database helper class
- ✅ `env-loader.php` - Environment loader
- ✅ `.env.local.example` - Configuration template
- ✅ `SUPABASE_MIGRATION_GUIDE.md` - Full documentation

Test it:
```bash
# Add a user (modify for your needs)
curl -X POST http://localhost/tunelec/add_user.php \
  -d "username=test&password=password123&full_name=Test User"

# Get questions
curl http://localhost/tunelec/questions_api.php
```

## 3️⃣ Update Your Existing PHP Files

### For login_handler.php
Replace your current code with the version in `SUPABASE_MIGRATION_GUIDE.md`

### For add_user.php
Replace with the version in the guide

### For questions_api.php
Replace with the version in the guide

**Or** create an `init.php` to include in all files:
```php
<?php
require_once 'env-loader.php';
require_once 'supabase.php';
```

## 4️⃣ Migrate Your Data

### Migrate Users from MySQL:
```bash
# Export from MySQL
mysqldump -u root -p tunelec users > users_backup.sql

# Insert into Supabase using your PHP API
# Or use Supabase dashboard SQL Editor
```

### Migrate Questions from CSV:
```bash
# Read your questions.csv and insert via questions_api.php
# See SUPABASE_MIGRATION_GUIDE.md for details
```

### Migrate Images:
1. Create a Storage bucket in Supabase: **Storage > New Bucket** → `question-images`
2. Make it public
3. Upload your images using the updated PHP code

## 5️⃣ Deploy to Free Hosting

Choose one (all are free):

### Option A: Vercel (Easiest) ⭐
```bash
# 1. Push to GitHub
git add .
git commit -m "Migrate to Supabase"
git push origin main

# 2. Go to vercel.com
# 3. Import your GitHub repo
# 4. Add environment variables from .env.local
# 5. Deploy!
```

### Option B: Railway.app
- Similar to Vercel, supports PHP
- Generous free tier

### Option C: Render.com
- Free tier with limited hours
- Good for hobby projects

### Option D: GitHub + Cloudflare Pages + Workers
- Use GitHub Pages for frontend
- Use Cloudflare Workers for PHP API
- Free tier available

## 6️⃣ Testing Live

Once deployed:

```bash
# Test API
curl https://your-domain.com/questions_api.php

# Test login
# Open https://your-domain.com/login.php in browser
```

## Common Issues

### "Missing Supabase credentials"
- Make sure `.env.local` is created and filled in
- Check you're using the correct **anon** key (not service key)

### "CORS error"
- Supabase Settings > API > Add your domain to allowed origins
- Example: `https://your-domain.com`

### "Unauthorized" in Supabase
- For testing, disable Row Level Security on tables:
  - Go **Authentication > Policies**
  - Or enable anonymous access

### Local PHP doesn't find classes
- Make sure `require_once 'supabase.php'` is at top of file

## File Structure After Setup

```
tunelec/
├── .env.local (YOUR CREDENTIALS - don't commit!)
├── .env.local.example
├── supabase.php (Database helper)
├── env-loader.php (Config loader)
├── login_handler.php (Updated for Supabase)
├── add_user.php (Updated for Supabase)
├── questions_api.php (Updated for Supabase)
├── SUPABASE_MIGRATION_GUIDE.md (Full docs)
└── ... rest of your files
```

## Next Steps

1. ✅ Create Supabase project
2. ✅ Fill `.env.local`
3. ✅ Create tables in Supabase
4. ✅ Test PHP files locally
5. ✅ Migrate your data
6. ✅ Push to GitHub
7. ✅ Deploy to Vercel/Railway/Render
8. ✅ Test live API

## Need Help?

- **Supabase Docs**: https://supabase.com/docs
- **Full Guide**: See `SUPABASE_MIGRATION_GUIDE.md`
- **Common Issues**: See section above

---

**You're ready to go!** 🚀
