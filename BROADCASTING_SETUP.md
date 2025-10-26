# Broadcasting Setup Guide 📡

## Quick Reference for Real-time Updates

---

## 🎯 Overview

The EnrollAssess system supports **two modes** for dashboard updates:

1. **Polling Mode** (Default) - Works immediately, no setup needed
2. **Real-time Mode** (Optional) - Instant updates using Pusher

---

## ⚡ Option 1: Polling Mode (Recommended to Start)

### How It Works
- Dashboard automatically refreshes every 30 seconds
- Simple AJAX requests to Laravel API
- Works on any hosting (shared, VPS, cloud)
- No additional services needed

### Setup (Already Done!)
```env
BROADCAST_DRIVER=log
```

### Pros
✅ Zero configuration  
✅ Works everywhere  
✅ No external dependencies  
✅ No additional costs  
✅ Reliable and simple  

### Cons
❌ 30-second delay for updates  
❌ Not truly "real-time"  

---

## 🚀 Option 2: Real-time Mode with Pusher

### How It Works
- Instant updates via WebSockets
- Laravel broadcasts events to Pusher
- Pusher pushes to connected browsers
- No page refresh needed

### Setup Steps

#### Step 1: Get Pusher Account (Free Tier)

1. Go to https://pusher.com
2. Click "Sign up" (free tier available)
3. Create account
4. Create new app:
   - Name: EnrollAssess Production
   - Cluster: Choose closest to your users
   - Frontend: Vue (doesn't matter, we use vanilla JS)
   - Backend: Laravel

#### Step 2: Get Credentials

From Pusher dashboard, copy:
- App ID
- Key
- Secret
- Cluster

#### Step 3: Update `.env`

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=123456
PUSHER_APP_KEY=abc123def456
PUSHER_APP_SECRET=xyz789secret
PUSHER_APP_CLUSTER=ap1

# Frontend configuration
VITE_BROADCAST_DRIVER=pusher
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### Step 4: Rebuild Assets

```bash
npm run build
```

#### Step 5: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
```

#### Step 6: Test

Open dashboard in two browser windows:
1. Create new applicant in one window
2. Watch dashboard update instantly in other window!

### Pros
✅ Instant updates (< 1 second)  
✅ True real-time experience  
✅ Better user experience  
✅ Professional feel  
✅ Free tier available (200k messages/day)  

### Cons
❌ Requires external service  
❌ May incur costs at scale  
❌ Slightly more complex setup  

---

## 🆓 Pusher Free Tier Limits

**What You Get FREE:**
- **200,000 messages/day**
- **100 concurrent connections**
- **Unlimited channels**
- **SSL connections**
- **Support included**

**Is This Enough?**

For a university enrollment system:
- **10 admins** checking dashboard = 10 connections
- **Updates every 30s** for safety = ~29,000 messages/day
- **Plenty of headroom** for growth

**When to Upgrade?**

Only if you exceed 200k messages/day, which would require:
- 100+ concurrent admin users
- Or heavy real-time features
- Cost: $49/month for unlimited messages

---

## 📊 Comparison Table

| Feature | Polling Mode | Real-time Mode |
|---------|--------------|----------------|
| **Setup Time** | 0 minutes | 10 minutes |
| **Update Speed** | 30 seconds | < 1 second |
| **Monthly Cost** | $0 | $0 - $49 |
| **Hosting Requirement** | Any | Any |
| **User Experience** | Good | Excellent |
| **Reliability** | Very High | High |
| **Complexity** | Very Simple | Simple |

---

## 🔧 How to Switch Between Modes

### Switch to Real-time:
```bash
# 1. Update .env
BROADCAST_DRIVER=pusher
VITE_BROADCAST_DRIVER=pusher

# 2. Add Pusher credentials to .env

# 3. Rebuild
npm run build

# 4. Clear cache
php artisan config:clear
```

### Switch Back to Polling:
```bash
# 1. Update .env
BROADCAST_DRIVER=log

# 2. Rebuild
npm run build

# 3. Clear cache
php artisan config:clear
```

---

## 🧪 Testing Broadcasting

### Test Polling Mode

1. Open dashboard
2. Wait 30 seconds
3. Check browser console: "Refreshing stats..."
4. Stats should update

### Test Real-time Mode

1. Open dashboard in Chrome
2. Open browser console (F12)
3. Look for: "Connected to Pusher"
4. Open dashboard in another browser
5. In first browser: Create new applicant
6. Second browser: Should show toast notification immediately

---

## 🐛 Troubleshooting

### Polling Mode Issues

**Problem:** Stats not updating

**Check:**
```bash
# 1. API endpoint works
curl http://your-site.com/admin/api/dashboard/stats

# 2. Browser console for errors (F12)

# 3. Laravel logs
tail -f storage/logs/laravel.log
```

### Real-time Mode Issues

**Problem:** Not connected to Pusher

**Check:**
```bash
# 1. Credentials in .env correct?
cat .env | grep PUSHER

# 2. Rebuilt assets?
npm run build

# 3. Browser console (F12) shows:
# "Connected to Pusher" - Good!
# "Pusher connection error" - Check credentials

# 4. Test from Pusher dashboard
# Go to "Debug Console" on pusher.com
# Create test applicant
# Should see event appear in console
```

**Problem:** Still using polling with real-time setup

**Solution:**
```bash
# System automatically falls back to polling if:
# - Pusher credentials invalid
# - Pusher connection fails
# - VITE_BROADCAST_DRIVER not set

# Fix:
# 1. Verify VITE_* variables in .env
# 2. npm run build
# 3. Hard refresh browser (Ctrl+Shift+R)
```

---

## 💡 Best Practices

### Development
```env
BROADCAST_DRIVER=log
# Use polling mode for simplicity
```

### Production (Small Scale)
```env
BROADCAST_DRIVER=log
# Start with polling, works great
```

### Production (Better UX)
```env
BROADCAST_DRIVER=pusher
# Use Pusher free tier for real-time feel
```

### Production (High Scale)
```env
BROADCAST_DRIVER=redis
# Self-host with Laravel WebSockets
# More complex, but no external costs
```

---

## 📈 When to Use Each Mode

### Use Polling Mode If:
- Just getting started
- Budget is tight ($0 cost)
- Small number of users
- 30-second delay is acceptable
- Want simplicity

### Use Real-time Mode If:
- Want professional feel
- Have 10+ concurrent admins
- Instant updates important
- Budget allows ($0-49/month)
- Users expect real-time

---

## 🎯 Recommended Approach

### Phase 1: Launch (Week 1-4)
- **Use Polling Mode**
- Get familiar with system
- Monitor usage patterns
- Zero additional costs

### Phase 2: Scale (Month 2+)
- **Enable Pusher** (free tier)
- Test with real users
- Monitor message usage
- Still $0 if under 200k/day

### Phase 3: Optimize (Month 6+)
- **Stay on free tier** if possible
- **Upgrade to $49/month** if needed
- Consider self-hosting with Redis if > $100/month

---

## 🔐 Security Notes

### Pusher Security
- **App Secret** never exposed to frontend
- **API calls** authenticated via Laravel
- **Channels** can be private/presence
- **SSL** encryption included

### Broadcasting Channels

**Currently:** All channels are public

**To make private** (advanced):
```php
// routes/channels.php
Broadcast::channel('dashboard', function ($user) {
    return $user->role === 'department-head';
});
```

---

## 📞 Support

### Pusher Not Working?
1. Check credentials: Pusher dashboard → App Settings
2. Test in Debug Console on pusher.com
3. Check browser console (F12)
4. Verify assets rebuilt: `npm run build`

### Still Need Help?
- Review Laravel Broadcasting docs
- Check Pusher documentation
- Test with `php artisan tinker`:
```php
event(new App\Events\StatisticsUpdated(['test' => 123]));
```

---

## ✅ Quick Setup Summary

### For Polling (Current Setup)
**Already working!** No action needed.

### For Real-time
```bash
# 1. Get Pusher account (free)
# 2. Copy credentials to .env:
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster
VITE_BROADCAST_DRIVER=pusher
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# 3. Rebuild
npm run build

# 4. Clear cache
php artisan config:clear

# 5. Test!
```

---

**That's it! Choose what works best for your needs. Both options are fully supported and production-ready! 🚀**

