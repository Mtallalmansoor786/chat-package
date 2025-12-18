# Pusher Configuration Guide

This guide will help you set up Pusher for real-time messaging in the chat package.

## Step 1: Create Pusher Account

1. **Sign up for Pusher:**
   - Go to https://pusher.com
   - Click "Sign Up" (free account available)
   - Complete the registration

2. **Create a Channels App:**
   - After signing in, go to Dashboard
   - Click "Create app" or "Channels apps"
   - Fill in:
     - **App name:** Your app name (e.g., "Chat Package")
     - **Cluster:** Choose closest to your location (e.g., `us2`, `eu`, `ap1`)
     - **Front-end tech:** JavaScript
     - **Back-end tech:** PHP
   - Click "Create app"

## Step 2: Get Your Pusher Credentials

After creating the app, you'll see your credentials:

1. **App ID** - Copy this
2. **Key** - Copy this (this is your App Key)
3. **Secret** - Copy this (keep this secure!)
4. **Cluster** - Copy this (e.g., `us2`, `eu`, `ap1`)

## Step 3: Add Credentials to .env

Open your Laravel project's `.env` file and add:

```env
# Pusher Configuration
PUSHER_APP_ID=your_app_id_here
PUSHER_APP_KEY=your_app_key_here
PUSHER_APP_SECRET=your_app_secret_here
PUSHER_APP_CLUSTER=your_cluster_here

# Broadcasting Driver
BROADCAST_DRIVER=pusher
```

**Example:**
```env
PUSHER_APP_ID=1234567
PUSHER_APP_KEY=abc123def456
PUSHER_APP_SECRET=xyz789secret123
PUSHER_APP_CLUSTER=us2
BROADCAST_DRIVER=pusher
```

## Step 4: Verify Laravel Broadcasting Config

Check `config/broadcasting.php` - it should already have Pusher configuration. If not, make sure it includes:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
    ],
],
```

## Step 5: Install Pusher PHP SDK (If Not Already Installed)

```bash
composer require pusher/pusher-php-server
```

## Step 6: Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Step 7: Test the Configuration

1. **Access Chat:**
   - Navigate to `/chat` in your browser
   - Login if required

2. **Create a Chat Room:**
   - Click "Create Room"
   - Add users and create the room

3. **Test Real-time Messaging:**
   - Open the chat room in two different browsers (or incognito windows)
   - Login as different users
   - Send a message from one browser
   - You should see it appear instantly in the other browser!

## Troubleshooting

### Messages Not Appearing in Real-time

1. **Check Browser Console:**
   - Open Developer Tools (F12)
   - Go to Console tab
   - Look for Pusher connection errors

2. **Verify Credentials:**
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   config('chat-package.pusher.key')
   config('chat-package.pusher.cluster')
   ```
   These should return your Pusher credentials.

3. **Check Pusher Dashboard:**
   - Go to your Pusher dashboard
   - Check "Debug Console" tab
   - You should see connection events when users join chat rooms

### Common Issues

**Issue: "Pusher is not defined"**
- Solution: Make sure Pusher JavaScript SDK is loaded (already included in the package views)

**Issue: "Authentication failed"**
- Solution: Double-check your PUSHER_APP_SECRET in .env

**Issue: "Cluster mismatch"**
- Solution: Make sure PUSHER_APP_CLUSTER in .env matches your Pusher app cluster

**Issue: Messages save but don't broadcast**
- Solution: Check that BROADCAST_DRIVER=pusher is set in .env

## Pusher Free Tier Limits

The free tier includes:
- ✅ 200,000 messages/day
- ✅ 100 concurrent connections
- ✅ 100 channels
- ✅ Perfect for development and small projects

## Production Considerations

For production:
1. **Enable Client Events** (if needed) in Pusher dashboard
2. **Set up Webhooks** for monitoring
3. **Use Environment Variables** - Never commit Pusher secrets to Git
4. **Monitor Usage** in Pusher dashboard

## Security Notes

- ✅ Never commit `.env` file to Git
- ✅ Keep `PUSHER_APP_SECRET` secure
- ✅ Use different Pusher apps for development and production
- ✅ The package uses Presence Channels for user tracking

## Next Steps

Once Pusher is configured:
1. ✅ Real-time messaging will work automatically
2. ✅ Users will see online/offline status
3. ✅ Messages will appear instantly for all room members
4. ✅ No page refresh needed!

## Need Help?

- Pusher Documentation: https://pusher.com/docs
- Pusher Support: https://support.pusher.com
- Check browser console for detailed error messages

