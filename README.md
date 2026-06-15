# Telepilot AI Engine 🚀

![Telepilot AI](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![Filament](https://img.shields.io/badge/Filament-5.x-FBBF24?style=for-the-badge&logo=laravel)
![MadelineProto](https://img.shields.io/badge/MadelineProto-Telegram-blue?style=for-the-badge&logo=telegram)

An advanced, fully autonomous Telegram channel management system powered by AI. Telepilot AI uses MadelineProto to act as a real Telegram user and leverages LLMs (Gemini, OpenAI, OpenRouter) to automatically generate, schedule, and publish highly engaging content to your channels and groups.

---

## 🌍 Language / ভাষা
- [English Documentation](#-english-documentation)
- [বাংলা গাইডলাইন (Bengali)](#-বাংলা-গাইডলাইন)

---

# 🇺🇸 English Documentation

### ✨ Key Features
- **True User Automation:** Uses MadelineProto (MTProto) to post as a real Telegram user, bypassing strict bot limitations.
- **Multiple AI Models:** Support for Google Gemini, OpenAI, OpenRouter, and Custom APIs with strict JSON output formatting.
- **Smart Formatting:** Automatically generates standard posts, polls, and photo captions based on rules.
- **Advanced Scheduling & Rules:** Set active days, time windows, frequency, and daily limits for auto-posting.
- **Multi-Account Support:** Manage multiple Telegram accounts and seamlessly link them to different channels/groups.
- **Glassmorphism UI:** Premium Filament-powered admin dashboard with stunning dark/light mode aesthetics.
- **Bot Fallback:** Automatically switches to Bot API if the MadelineProto session expires.

### 🤖 System Bot Setup (Alerts & Approval)
To receive error alerts and approve AI drafts directly from Telegram, set up the System Bot:
1. Open Telegram, search for [@BotFather](https://t.me/BotFather), and create a new bot. Copy the **HTTP API Token**.
2. In Telepilot AI Dashboard, go to **App Settings** -> **System Bot Token** and paste the token.
3. Configure your Telegram User ID in the same settings page so the system knows where to send alerts.
4. Set the webhook by running this URL in your browser:  
   `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/telegram/webhook`
   *(Replace `<YOUR_BOT_TOKEN>` and `yourdomain.com` with your actual token and domain).*

### 📋 System Requirements
- **PHP:** 8.2 or higher
- **Database:** MySQL 8.0+ / PostgreSQL / SQLite
- **Node.js & NPM:** For building frontend assets (Vite)
- **Telegram API Credentials:** `api_id` and `api_hash` from [my.telegram.org](https://my.telegram.org)

### 🚀 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/telepulse-ai.git
   cd telepulse-ai
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Configure your `.env` file with database credentials and queue connection:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=telepulse
   DB_USERNAME=root
   DB_PASSWORD=
   
   QUEUE_CONNECTION=database # (redis is recommended for production)
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Install Node Dependencies & Build:**
   ```bash
   npm install
   npm run build
   ```

6. **Start the Queue Worker:**
   *Telepilot requires background workers for AI generation and MadelineProto publishing.*
   ```bash
   php artisan queue:work --timeout=300
   ```

7. **Start the Application:**
   ```bash
   php artisan serve
   ```

### ⚙️ How to Use
1. **Access Dashboard:** Go to `http://127.0.0.1:8000/admin` and log in.
2. **Add Telegram Account:** Navigate to **Telegram Accounts** -> Click **New Account** -> Enter Phone Number -> Provide OTP.
3. **Configure AI Models:** Go to **AI Models** and add your Gemini/OpenAI API keys.
4. **Create a Rule:** Go to **Rules**, select a target channel, assign an AI model, specify a topic, and set the frequency (e.g., Every 3 hours).
5. **Start Automating:** The system's Heartbeat (Cron) will automatically dispatch jobs, generate content, and publish them exactly on schedule!

### ⏰ Cron Job Setup
Telepilot AI uses a central heartbeat to manage automation. You must run this every minute using **ONE** of the following methods:

**Method 1: Artisan Command (Recommended)**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Method 2: Wget**
```bash
* * * * * wget -qO- http://yourdomain.com/cron >/dev/null 2>&1
```

**Method 3: cURL**
```bash
* * * * * curl -s http://yourdomain.com/cron >/dev/null 2>&1
```

---

# 🇧🇩 বাংলা গাইডলাইন

### ✨ মূল ফিচারসমূহ
- **রিয়েল ইউজার অটোমেশন:** MadelineProto (MTProto) ব্যবহার করে রিয়েল ইউজারের মতো টেলিগ্রামে পোস্ট করে, ফলে কোনো বট লিমিটেশনে পড়তে হয় না।
- **মাল্টিপল AI সাপোর্ট:** Google Gemini, OpenAI এবং OpenRouter এর মতো AI মডেল দিয়ে অটোমেটিক পোস্ট, পোল এবং ফটো ক্যাপশন জেনারেট করে।
- **স্মার্ট শিডিউলিং:** কোন দিন, কয়টা থেকে কয়টা পর্যন্ত পোস্ট হবে, তা রুলস (Rules) দিয়ে সেট করা যায়।
- **মাল্টি-অ্যাকাউন্ট সাপোর্ট:** একসাথে একাধিক টেলিগ্রাম অ্যাকাউন্ট লগইন করে বিভিন্ন চ্যানেলে অ্যাসাইন করা যায়।
- **প্রিমিয়াম ড্যাশবোর্ড:** Filament দিয়ে তৈরি করা চমৎকার Glassmorphism UI, যা ডার্ক এবং লাইট মোড সাপোর্ট করে।

### 🤖 সিস্টেম বট সেটআপ (Alerts & Approval)
সিস্টেমের যেকোনো এরর এলার্ট পেতে এবং ড্রাফটগুলো টেলিগ্রাম থেকে সরাসরি অ্যাপ্রুভ করার জন্য বট সেটআপ করা জরুরি:
১. টেলিগ্রামে [@BotFather](https://t.me/BotFather)-এ গিয়ে একটি নতুন বট তৈরি করুন এবং **HTTP API Token** টি কপি করুন।
২. আপনার ড্যাশবোর্ড থেকে **App Settings** -> **System Bot Token** অপশনে গিয়ে টোকেনটি পেস্ট করুন।
৩. একই পেজে আপনার টেলিগ্রাম ইউজার আইডি (Telegram User ID) সেট করে দিন।
৪. আপনার ওয়েবসাইটের সাথে বট কানেক্ট করতে ব্রাউজারে নিচের লিংকটি রান করুন:  
   `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/telegram/webhook`  
   *(এখানে `<YOUR_BOT_TOKEN>` এর জায়গায় আপনার বটের টোকেন এবং `yourdomain.com` এর জায়গায় আপনার ওয়েবসাইটের ডোমেইন দিন)।*

### 📋 সার্ভার রিকোয়ারমেন্টস
- **PHP:** ৮.২ বা তার উপরের ভার্সন
- **ডেটাবেস:** MySQL / PostgreSQL
- **Node.js:** Vite বিল্ড করার জন্য
- **টেলিগ্রাম API:** [my.telegram.org](https://my.telegram.org) থেকে `api_id` এবং `api_hash` প্রয়োজন।

### 🚀 ইন্সটলেশন এবং সেটআপ

১. **প্রজেক্ট ক্লোন করুন:**
   ```bash
   git clone https://github.com/yourusername/telepulse-ai.git
   cd telepulse-ai
   ```

২. **প্যাকেজ ইন্সটল করুন:**
   ```bash
   composer install
   ```

৩. **এনভায়রনমেন্ট সেটআপ:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   এরপর `.env` ফাইলে আপনার ডেটাবেস এবং কিউ (Queue) কনফিগার করুন:
   ```env
   DB_CONNECTION=mysql
   DB_DATABASE=telepulse
   DB_USERNAME=root
   DB_PASSWORD=
   
   QUEUE_CONNECTION=database
   ```

৪. **ডেটাবেস মাইগ্রেট করুন:**
   ```bash
   php artisan migrate --seed
   ```

৫. **ফ্রন্টএন্ড বিল্ড করুন:**
   ```bash
   npm install
   npm run build
   ```

৬. **কিউ (Queue) ওয়ার্কার চালু করুন:**
   *AI জেনারেশন এবং টেলিগ্রাম পোস্টিংয়ের জন্য ব্যাকগ্রাউন্ড ওয়ার্কার চালু রাখা বাধ্যতামূলক।*
   ```bash
   php artisan queue:work --timeout=300
   ```

৭. **প্রজেক্ট রান করুন:**
   ```bash
   php artisan serve
   ```

### ⚙️ যেভাবে ব্যবহার করবেন
১. **ড্যাশবোর্ড লগইন:** `http://127.0.0.1:8000/admin` লিংকে গিয়ে লগইন করুন।
২. **অ্যাকাউন্ট অ্যাড করুন:** ড্যাশবোর্ড থেকে **Telegram Accounts**-এ গিয়ে আপনার ফোন নাম্বার দিয়ে OTP ভেরিফাই করে লগইন করুন।
৩. **AI কনফিগারেশন:** **AI Models**-এ গিয়ে আপনার পছন্দের AI (Gemini/OpenAI) এর API Key যুক্ত করুন।
৪. **রুল তৈরি করুন:** **Rules** মেনু থেকে কোন চ্যানেলে, কী টপিকে এবং কতক্ষণ পর পর পোস্ট হবে তা ঠিক করে দিন।
৫. **অটোমেশন শুরু:** এরপর আর কোনো কাজ নেই! সিস্টেম ব্যাকগ্রাউন্ডে অটোমেটিক্যালি কনটেন্ট বানাবে এবং শিডিউল অনুযায়ী চ্যানেলে পোস্ট করতে থাকবে।

### ⏰ ক্রন জব (Cron Job) সেটআপ
অটোমেশন ঠিকভাবে কাজ করার জন্য সার্ভারে ক্রন জব সেট করা বাধ্যতামূলক। নিচের **যেকোনো একটি** পদ্ধতি ব্যবহার করে প্রতি মিনিটে ক্রন জব সেট করুন:

**পদ্ধতি ১: Artisan কমান্ড দিয়ে (সবচেয়ে ভালো উপায়)**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**পদ্ধতি ২: Wget দিয়ে**
```bash
* * * * * wget -qO- http://yourdomain.com/cron >/dev/null 2>&1
```

**পদ্ধতি ৩: cURL দিয়ে**
```bash
* * * * * curl -s http://yourdomain.com/cron >/dev/null 2>&1
```

---
*Developed with ❤️ by **piashjodder** using Laravel & Filament.*
