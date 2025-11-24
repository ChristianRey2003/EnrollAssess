# 🚀 Getting Out of Amazon SES Sandbox Mode

## Quick Overview

**Current Status:** In sandbox mode, you can only send emails to verified email addresses.  
**Goal:** Request production access to send emails to ANY email address.  
**Time Required:** 10-15 minutes to submit request, 24-48 hours for AWS approval.

---

## ✅ Step-by-Step Guide

### Step 1: Log in to AWS SES Console

1. Go to: https://console.aws.amazon.com/ses/
2. Make sure you're in the **correct region** (ap-southeast-1 - Singapore recommended)
3. You should see your account status showing "Sandbox"

---

### Step 2: Navigate to Account Dashboard

1. In the left sidebar, click **"Account dashboard"**
2. You'll see your current sending limits:
   - **Sandbox mode:** Can only send to verified emails
   - **Production mode:** Can send to any email (with limits)

---

### Step 3: Request Production Access

1. On the Account dashboard page, look for:
   - A section showing "Sandbox" status
   - A button/link that says **"Request production access"** or **"Request sending limit increase"**
2. Click that button

---

### Step 4: Fill Out the Request Form

AWS will ask you several questions. Here's how to answer them for EnrollAssess:

#### **Use Case Description:**
```
We operate an enrollment and assessment system (EnrollAssess) for Eastern Visayas State University (EVSU). The system sends automated emails to students and applicants including:

- Access codes for online examinations
- Exam results and notifications
- Interview schedules and reminders
- Application status updates
- Password reset emails

Our domain is: enrollassess-evsu.com
We expect to send approximately 1,000-5,000 emails per month during enrollment periods.
```

#### **Website URL:**
```
https://enrollassess-evsu.com
```
(Or your actual deployment URL when ready)

#### **How do you plan to handle bounces and complaints?**
```
We will:
1. Monitor bounce and complaint rates through AWS SES metrics
2. Set up SNS notifications for bounces and complaints
3. Remove invalid email addresses from our database
4. Investigate and resolve any complaint issues promptly
5. Maintain a bounce rate below 5% and complaint rate below 0.1%
```

#### **How do you plan to build or maintain your reputation?**
```
We will:
1. Only send emails to users who have explicitly registered or applied
2. Include clear unsubscribe options in transactional emails
3. Use double opt-in for newsletter/marketing emails (if any)
4. Monitor sending statistics and adjust practices accordingly
5. Follow email best practices (SPF, DKIM, DMARC records)
6. Send only relevant, expected emails to recipients
```

#### **Expected Sending Volume:**
- **Emails per day:** 50-200 (during enrollment periods)
- **Emails per month:** 1,000-5,000
- **Peak sending rate:** 5-10 emails per second

#### **Email Content Type:**
Select: **"Transactional emails"** (access codes, results, notifications)

---

### Step 5: Verify Your Domain (Recommended Before Request)

**Why:** Having a verified domain improves your chances of approval and is required for production.

1. In SES Console, go to **"Verified identities"**
2. Click **"Create identity"**
3. Choose **"Domain"** (not email address)
4. Enter: `enrollassess-evsu.com`
5. Click **"Create identity"**

#### Add DNS Records to Namecheap:

AWS will provide you with DNS records to add. You'll need to add these in Namecheap:

1. **SPF Record:**
   - Type: `TXT`
   - Host: `@` (or leave blank)
   - Value: (provided by AWS, looks like: `v=spf1 include:amazonses.com ~all`)

2. **DKIM Records:**
   - Type: `TXT`
   - Host: (provided by AWS, e.g., `_amazonses.enrollassess-evsu.com`)
   - Value: (provided by AWS, long string)

3. **DMARC Record (Optional but Recommended):**
   - Type: `TXT`
   - Host: `_dmarc`
   - Value: `v=DMARC1; p=none; rua=mailto:admin@enrollassess-evsu.com`

**How to add in Namecheap:**
1. Log in to Namecheap
2. Go to **Domain List** → Click **"Manage"** next to `enrollassess-evsu.com`
3. Go to **"Advanced DNS"** tab
4. Click **"Add New Record"**
5. Select record type and enter values from AWS
6. Save changes
7. Wait 24-48 hours for DNS propagation

**Note:** You can submit the production access request even before domain verification completes, but verification helps with approval.

---

### Step 6: Submit the Request

1. Review all your answers
2. Check the box confirming you understand the terms
3. Click **"Submit"** or **"Request production access"**

---

### Step 7: Wait for Approval

- **Typical wait time:** 24-48 hours
- **You'll receive:** Email notification when approved (or if more info is needed)
- **Check status:** Go back to Account dashboard to see status

---

## 📋 What Happens After Approval?

### Immediate Benefits:
- ✅ Can send emails to ANY email address (not just verified ones)
- ✅ Higher sending limits (usually 50 emails/second, can be increased)
- ✅ Better deliverability
- ✅ Can use your verified domain for sending

### New Limits:
- **Default sending rate:** 1 email/second (can request increase)
- **Daily sending quota:** 200 emails/day (can request increase)
- **Can request increases** as your volume grows

---

## 🚨 If Your Request is Denied

### Common Reasons:
1. **Incomplete information** - AWS needs more details
2. **No domain verification** - Verify your domain first
3. **Unclear use case** - Be more specific about your email types
4. **High bounce/complaint risk** - Explain your bounce handling

### What to Do:
1. Check email from AWS for specific reasons
2. Address the concerns mentioned
3. Resubmit the request with more details
4. Consider verifying your domain first if you haven't

---

## ✅ Pre-Request Checklist

Before submitting, make sure you have:

- [ ] AWS account with SES enabled
- [ ] At least one verified email address (for testing)
- [ ] Clear understanding of your email use case
- [ ] Domain `enrollassess-evsu.com` purchased (you have this ✅)
- [ ] (Recommended) Domain verified in SES with DNS records
- [ ] Clear answers prepared for the request form

---

## 🔧 After Approval - Update Your Configuration

Once approved, you may want to:

1. **Request sending limit increase** (if needed):
   - Go to Account dashboard
   - Click "Request sending limit increase"
   - Request higher daily quota or sending rate

2. **Set up SNS notifications** (for bounces/complaints):
   - Go to SES → Configuration → Notifications
   - Set up SNS topics for bounces and complaints
   - Configure your application to handle these

3. **Monitor sending statistics**:
   - Check Account dashboard regularly
   - Monitor bounce and complaint rates
   - Keep rates low to maintain good reputation

---

## 📝 Quick Reference

### AWS SES Console Links:
- **SES Console:** https://console.aws.amazon.com/ses/
- **Account Dashboard:** https://console.aws.amazon.com/ses/v2/home?region=ap-southeast-1#/account
- **Verified Identities:** https://console.aws.amazon.com/ses/v2/home?region=ap-southeast-1#/verified-identities

### Namecheap DNS Settings:
- **Login:** https://www.namecheap.com/myaccount/login/
- **Domain Management:** Domain List → Manage → Advanced DNS

### Your Domain:
- **Domain:** `enrollassess-evsu.com`
- **Registrar:** Namecheap
- **Recommended Region:** ap-southeast-1 (Singapore)

---

## 🎯 Summary

1. **Go to AWS SES Console** → Account dashboard
2. **Click "Request production access"**
3. **Fill out form** with your use case details
4. **Verify domain** (recommended) - add DNS records in Namecheap
5. **Submit request**
6. **Wait 24-48 hours** for approval
7. **Start sending** to any email address!

---

## 💡 Pro Tips

- **Verify your domain first** - It significantly improves approval chances
- **Be specific** in your use case description
- **Mention your domain** in the request
- **Explain bounce handling** clearly
- **Request appropriate limits** - Don't request 1 million emails/day if you only need 5,000/month

---

**Good luck! Your request should be approved within 24-48 hours.** 🚀

If you need help with any step, let me know!

