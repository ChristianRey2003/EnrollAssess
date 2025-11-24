# 📝 Adding DNS Records to Namecheap for SES Verification

## Records to Add

You need to add **5 DNS records** to Namecheap:

### 1. DKIM Records (3 CNAME records)
### 2. MAIL FROM Records (1 MX + 1 TXT record)

---

## Step-by-Step: Adding Records in Namecheap

### Step 1: Log in to Namecheap

1. Go to: https://www.namecheap.com/myaccount/login/
2. Log in with your account
3. Go to **"Domain List"** from the left menu

---

### Step 2: Access DNS Settings

1. Find `enrollassess-evsu.com` in your domain list
2. Click **"Manage"** button next to it
3. Click on **"Advanced DNS"** tab at the top

---

### Step 3: Add DKIM Records (3 CNAME records)

Add these **3 CNAME records** one by one:

#### Record 1:
- **Type:** `CNAME`
- **Host:** `hnz6qu67jtgostxwatg64fvhyvwlrhtj._domainkey`
- **Value:** `hnz6qu67jtgostxwatg64fvhyvwlrhtj.dkim.amazonses.com`
- **TTL:** `Automatic` (or `600`)

#### Record 2:
- **Type:** `CNAME`
- **Host:** `4dte2lzyzxleahvvzx3pq4uss5oobry4._domainkey`
- **Value:** `4dte2lzyzxleahvvzx3pq4uss5oobry4.dkim.amazonses.com`
- **TTL:** `Automatic` (or `600`)

#### Record 3:
- **Type:** `CNAME`
- **Host:** `ojyocyyrcyh4tze3tzpx6kd3fxdjmbtf._domainkey`
- **Value:** `ojyocyyrcyh4tze3tzpx6kd3fxdjmbtf.dkim.amazonses.com`
- **TTL:** `Automatic` (or `600`)

**How to add in Namecheap:**
1. Click **"Add New Record"** button
2. Select **"CNAME Record"** from dropdown
3. Enter the Host (without `.enrollassess-evsu.com` - just the part before)
4. Enter the Value (full value from AWS)
5. Click the checkmark (✓) to save
6. Repeat for all 3 records

---

### Step 4: Add MAIL FROM Records (MX + TXT)

#### MX Record (Optional but Recommended):
- **Type:** `MX Record`
- **Host:** `mail`
- **Value:** `feedback-smtp.ap-southeast-1.amazonses.com`
- **Priority:** `10`
- **TTL:** `Automatic` (or `600`)

**How to add in Namecheap:**
1. Click **"Add New Record"** button
2. Look for **"MX Record"** in the dropdown
3. **If MX Record option is NOT available:**
   - You can skip this - AWS SES will use default MAIL FROM domain
   - The TXT record below is more important
4. **If MX Record option IS available:**
   - Enter Host: `mail`
   - Enter Value: `feedback-smtp.ap-southeast-1.amazonses.com`
   - Enter Priority: `10`
   - Click the checkmark (✓) to save

**Note:** If you can't find MX records in Namecheap, that's okay! AWS SES will still work because you selected "Use default MAIL FROM domain" on MX failure. The TXT record is more critical.

#### TXT Record (SPF for MAIL FROM):
- **Type:** `TXT Record`
- **Host:** `mail`
- **Value:** `v=spf1 include:amazonses.com ~all`
- **TTL:** `Automatic` (or `600`)

**How to add in Namecheap:**
1. Click **"Add New Record"** button
2. Select **"TXT Record"** from dropdown
3. Enter Host: `mail`
4. Enter Value: `v=spf1 include:amazonses.com ~all`
5. Click the checkmark (✓) to save

---

## 📋 Complete Checklist

After adding all records, you should have:

- [ ] 3 CNAME records for DKIM (all with `_domainkey` in the host) - **REQUIRED**
- [ ] 1 TXT record for `mail.enrollassess-evsu.com` - **REQUIRED**
- [ ] 1 MX record for `mail.enrollassess-evsu.com` - **OPTIONAL** (if available in Namecheap)

**Minimum Required: 4 records (3 CNAME + 1 TXT)**
**If MX available: 5 records total**

---

## ⏰ After Adding Records

1. **Save all records** in Namecheap
2. **Wait 24-72 hours** for DNS propagation
3. **Check AWS SES Console** - status should change from "Pending" to "Verified"
4. AWS automatically checks for records every few hours

---

## 🔍 How to Verify Records Are Added

### In Namecheap:
- Go to Advanced DNS tab
- You should see all 5 records listed
- Make sure they're all active/enabled

### Check DNS Propagation (Optional):
You can check if records are live using online tools:
- https://mxtoolbox.com/SuperTool.aspx
- Enter: `enrollassess-evsu.com`
- Check for CNAME and MX records

---

## 🚨 Common Mistakes to Avoid

1. **Don't include the full domain in Host field:**
   - ✅ Correct: `mail` or `hnz6qu67jtgostxwatg64fvhyvwlrhtj._domainkey`
   - ❌ Wrong: `mail.enrollassess-evsu.com`

2. **Don't add quotes around TXT values:**
   - ✅ Correct: `v=spf1 include:amazonses.com ~all`
   - ❌ Wrong: `"v=spf1 include:amazonses.com ~all"`

3. **Make sure MX Priority is correct:**
   - ✅ Priority: `10` (not `10 feedback-smtp...`)

4. **Copy values exactly:**
   - Make sure there are no extra spaces
   - Copy the full value including `.dkim.amazonses.com`

---

## 📸 Visual Guide (Namecheap Interface)

When you click "Add New Record" in Namecheap, you'll see:

```
┌─────────────────────────────────────┐
│ Type: [CNAME Record ▼]              │
│ Host: [________________]            │
│ Value: [________________]           │
│ TTL: [Automatic ▼]                  │
│         [✓ Save]                    │
└─────────────────────────────────────┘
```

For MX records:
```
┌─────────────────────────────────────┐
│ Type: [MX Record ▼]                 │
│ Host: [mail]                        │
│ Value: [feedback-smtp...]          │
│ Priority: [10]                      │
│ TTL: [Automatic ▼]                  │
│         [✓ Save]                    │
└─────────────────────────────────────┘
```

---

## ✅ Next Steps After DNS Records Are Verified

Once AWS detects your DNS records (24-72 hours):

1. **Check AWS SES Console:**
   - Identity status should change to **"Verified"**
   - DKIM configuration should change to **"Success"**
   - MAIL FROM configuration should change to **"Success"**

2. **Request Production Access:**
   - Go to Account dashboard
   - Click "Request production access"
   - Fill out the form (see `SES_SANDBOX_EXIT_GUIDE.md`)

3. **Wait for Approval:**
   - Usually 24-48 hours
   - You'll get an email when approved

---

## 🆘 Troubleshooting

### Records not showing up after 72 hours?
- Double-check you entered values correctly
- Make sure records are saved in Namecheap
- Check DNS propagation with mxtoolbox.com
- Verify you're checking the right region in AWS (ap-southeast-1)

### Still showing "Pending"?
- AWS checks every few hours - be patient
- Make sure all 5 records are added
- Verify no typos in Host or Value fields

---

**Good luck! Once these records are verified, you're one step closer to production access!** 🚀

