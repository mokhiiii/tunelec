# Deployment Guide - Free Hosting Options

After you finish setting up Supabase locally, you need to deploy to a free hosting platform.

## ⭐ Option 1: Vercel (Recommended)

**Pros:**
- Very easy setup
- Great performance
- Supports PHP natively
- Free tier is generous

**Steps:**

1. **Push to GitHub**
   ```bash
   git add .
   git commit -m "Add Supabase integration"
   git push
   ```

2. **Create Vercel Account**
   - Go to https://vercel.com
   - Sign up with your GitHub account

3. **Import Your Project**
   - Click "New Project"
   - Select your `tunelec` repository
   - Click Import

4. **Add Environment Variables**
   - In the Vercel dashboard, go to Settings > Environment Variables
   - Add:
     ```
     SUPABASE_URL      = your_url
     SUPABASE_ANON_KEY = your_key
     ```

5. **Deploy**
   - Click "Deploy"
   - Wait ~2-5 minutes
   - Get your live URL: `https://tunelec-xxx.vercel.app`

6. **Configure CORS in Supabase**
   - Supabase Dashboard > Settings > API > Allowed Origins
   - Add: `https://tunelec-xxx.vercel.app`

**Cost:** Free ($0/month)

---

## Option 2: Railway.app

**Pros:**
- Easy setup
- Good free tier
- Supports PHP

**Steps:**

1. **Create Railway Account**
   - Go to https://railway.app
   - Sign up with GitHub

2. **Click "New Project"**
   - Select "Deploy from GitHub repo"
   - Choose your repo

3. **Add Variables**
   - In Railway dashboard, go to Variables
   - Add from your `.env.local`:
     ```
     SUPABASE_URL=xxx
     SUPABASE_ANON_KEY=xxx
     ```

4. **Deploy**
   - Click "Deploy"
   - Your app will be live in 1-2 minutes

5. **Get Your URL**
   - In Railway, click on your project
   - Copy the URL under "Deployments"

6. **Configure CORS**
   - Add your Railway URL to Supabase CORS settings

**Cost:** Free tier (~$5 credit/month, good for hobby projects)

---

## Option 3: Render

**Pros:**
- Very beginner friendly
- Free tier available
- Good documentation

**Steps:**

1. **Go to render.com**
   - Sign up with GitHub

2. **Create Service**
   - Click "New +"
   - Select "Web Service"
   - Select your GitHub repo

3. **Configure Service**
   - Build Command: Leave empty (PHP doesn't need build)
   - Start Command: `php -S 0.0.0.0:$PORT`

4. **Add Environment Variables**
   - Scroll to "Environment Variables"
   - Add:
     ```
     SUPABASE_URL = xxx
     SUPABASE_ANON_KEY = xxx
     ```

5. **Click "Create Web Service"**
   - Wait for deployment

6. **Configure CORS**
   - Add Render URL to Supabase

**Cost:** Free tier (limited, but good for testing)

---

## Option 4: Replit

**Pros:**
- Instant deployment
- Good for learning
- Integrated IDE

**Steps:**

1. **Go to replit.com**
   - Sign up

2. **Import from GitHub**
   - Click "Import"
   - Paste your GitHub repo URL

3. **Run**
   - Click "Run" button
   - It will start your PHP server

4. **Add Secrets**
   - Click "Secrets" icon (lock)
   - Add:
     ```
     SUPABASE_URL
     SUPABASE_ANON_KEY
     ```

5. **Get Public URL**
   - Click "Share" button
   - Copy the live URL

**Cost:** Free (but limited resources)

---

## Option 5: Fly.io

**Pros:**
- Very flexible
- Good free tier
- Good for production

**Steps:**

1. **Install Fly CLI**
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Sign up**
   ```bash
   flyctl auth signup
   ```

3. **Create Dockerfile** in your project root:
   ```dockerfile
   FROM php:8.2-apache
   COPY . /var/www/html
   RUN a2enmod rewrite
   EXPOSE 8080
   CMD ["apache2-foreground"]
   ```

4. **Deploy**
   ```bash
   flyctl launch
   flyctl secrets set SUPABASE_URL=xxx SUPABASE_ANON_KEY=xxx
   flyctl deploy
   ```

**Cost:** Free tier available

---

## Option 6: GitHub Pages + Vercel Functions

GitHub Pages can only host static files, but you can use:

1. **GitHub Pages for Frontend**
   - Your HTML/CSS/JS files

2. **Vercel Functions for Backend**
   - Your PHP API endpoints

This is more complex, so we recommend Option 1 (Vercel) instead.

---

## Recommended Setup for Your Project

### Best: Vercel
- Easiest setup
- Best performance
- Free tier is perfect for this project
- **Estimated setup time: 5 minutes**

### Backup: Railway.app
- If Vercel has issues
- Very similar experience
- **Estimated setup time: 5 minutes**

---

## After Deployment

### ✅ Testing Your Live API

```bash
# Replace with your actual domain
curl https://your-domain.vercel.app/questions_api.php

# Test login page
Open https://your-domain.vercel.app/login.php
```

### ✅ Configure CORS in Supabase

1. Go to your Supabase project dashboard
2. Settings > API > "Allowed Origins"
3. Add your deployed URL:
   - Example: `https://tunelec-xyz.vercel.app`

### ✅ Update External Links

If your frontend makes API calls, update them:

```javascript
// Before (localhost)
fetch('http://localhost/tunelec/questions_api.php')

// After (production)
fetch('https://your-domain.vercel.app/questions_api.php')
```

### ✅ Monitor Your Deployment

- **Vercel:** vercel.com/dashboard
- **Railway:** railway.app/dashboard
- **Render:** render.com/dashboard

---

## Troubleshooting Deployment

### "502 Bad Gateway" Error
- Check your environment variables are set
- Check `.env.local` exists with correct values
- View deployment logs for details

### "Cannot find module supabase"
- Make sure `supabase.php` is in your repository
- Check `require_once` paths are correct

### "CORS Error in Browser"
- Add your domain to Supabase CORS settings
- Check domain is spelled correctly

### "API returns 500 error"
- Check Supabase project is active
- Verify credentials in environment variables
- View Supabase Activity Logs for errors

### Local works but deployed doesn't
- Compare `.env.local` with deployed environment variables
- Check file permissions
- Verify database tables exist in Supabase

---

## Cost Comparison

| Platform | Free Tier | Cost | Notes |
|----------|-----------|------|-------|
| Vercel | Yes | $0-20/mo | Recommended |
| Railway | Limited | $5 credit | Good backup |
| Render | Limited | $7+/mo | Limited hours |
| Replit | Yes | Free | Limited resources |
| Fly.io | Yes | Free | More complex |

---

## Next Steps

1. Choose a platform (start with Vercel)
2. Follow the deployment steps
3. Test your live API
4. Share your project!

---

**Your free hosting setup is ready!** 🚀
