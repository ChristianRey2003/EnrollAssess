# Email Template Customization Guide

## 📧 Customizing Exam Notification Emails

Your EnrollAssess system has fully customizable email templates that work with both SMTP (Gmail) and Amazon SES.

---

## 🎨 Quick EVSU Customization

### 1. Change to EVSU Colors (Maroon & Gold)

**File:** `resources/views/emails/exam-notification.blade.php`

Find line 25 and change:
```css
/* FROM: */
.email-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

/* TO: */
.email-header {
    background: linear-gradient(135deg, #800020 0%, #5C0016 100%); /* EVSU Maroon */
}
```

Find line 69 and change:
```css
/* FROM: */
.access-code-box {
    background: #eff6ff;
    border: 2px dashed #3b82f6;
}

/* TO: */
.access-code-box {
    background: #FFF8DC; /* Light Gold */
    border: 2px dashed #800020; /* EVSU Maroon */
}
```

Find line 82 and change:
```css
/* FROM: */
.access-code {
    color: #1e40af;
}

/* TO: */
.access-code {
    color: #800020; /* EVSU Maroon */
}
```

### 2. Add EVSU Logo

**Option A: External URL**
```html
<!-- Line 159, add inside email-header -->
<img src="https://evsu.edu.ph/images/evsu-logo.png" 
     alt="EVSU Logo" 
     style="max-width: 120px; margin-bottom: 15px;">
<h1>EVSU Entrance Examination</h1>
```

**Option B: Local Image**
1. Place logo in `public/images/evsu-logo.png`
2. Add in template:
```html
<img src="{{ asset('images/evsu-logo.png') }}" 
     alt="EVSU Logo" 
     style="max-width: 120px; margin-bottom: 15px;">
```

### 3. Change Department/School Name

Find line 159-162:
```html
<!-- FROM: -->
<div class="email-header">
    <h1>BSIT Entrance Examination</h1>
    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Computer Studies Department</p>
</div>

<!-- TO: -->
<div class="email-header">
    <h1>EVSU Ormoc Campus</h1>
    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Bachelor of Science in Information Technology</p>
    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">Entrance Examination</p>
</div>
```

### 4. Customize Email Subject

**File:** `app/Mail/ExamNotificationMail.php`

Find line 43:
```php
// FROM:
return $this->subject('BSIT Entrance Exam Notification')

// TO:
return $this->subject('EVSU BSIT Entrance Exam - Schedule & Access Code')
```

### 5. Customize Instructions

Find lines 220-231:
```html
<div class="instructions">
    <h3>Important Instructions:</h3>
    <ul>
        <li>Report to EVSU Ormoc Campus 15 minutes before exam time</li>
        <li>Bring valid ID (School ID, Student ID, or Government ID)</li>
        <li>Bring 2 pencils and eraser</li>
        <li>Stable internet connection required for online exam</li>
        <li>Use desktop or laptop (mobile devices not allowed)</li>
        <li>Calculator and reference materials are NOT permitted</li>
        <li>The exam must be completed in one sitting (no breaks)</li>
        <li>Late arrivals will NOT be accommodated</li>
    </ul>
</div>
```

### 6. Customize Footer

Find lines 243-249:
```html
<div class="footer">
    <p><strong>Eastern Visayas State University - Ormoc Campus</strong></p>
    <p>Computer Studies Department</p>
    <p>Contact: (053) XXX-XXXX | Email: csd@evsu.edu.ph</p>
    <p style="margin-top: 10px; font-size: 12px;">
        &copy; {{ date('Y') }} EVSU EnrollAssess System. All rights reserved.
    </p>
</div>
```

---

## 🎯 Adding Custom Fields

### Example: Add Campus Field

**Step 1: Update Mailable**
File: `app/Mail/ExamNotificationMail.php`

Add property:
```php
public $campus;
```

Update constructor:
```php
public function __construct(
    Applicant $applicant, 
    $accessCode, 
    $examDate = null, 
    $examTime = null, 
    $examVenue = null, 
    $specialInstructions = null,
    $campus = 'Ormoc Campus' // NEW
) {
    // ... existing code ...
    $this->campus = $campus; // NEW
}
```

**Step 2: Update Template**
File: `resources/views/emails/exam-notification.blade.php`

Add after line 190:
```html
<div class="info-row">
    <div class="info-label">Campus:</div>
    <div class="info-value">{{ $campus }}</div>
</div>
```

**Step 3: Update Controller**
File: `app/Http/Controllers/ApplicantController.php`

Find line 1062 and update:
```php
Mail::to($applicant->email_address)
    ->send(new \App\Mail\ExamNotificationMail(
        $applicant,
        $applicant->accessCode->code,
        $examDate,
        $examTime,
        $examVenue,
        $specialInstructions,
        'Ormoc Campus' // NEW parameter
    ));
```

---

## 🔄 Testing Your Changes

### 1. Save your changes

### 2. Clear cache
```bash
php artisan config:clear
php artisan view:clear
```

### 3. Send test email
- Go to Admin → Applicants
- Select an applicant with email
- Click "Send Exam Notification"
- Fill in details and send

### 4. Check your inbox
- Verify colors match EVSU branding
- Check logo displays correctly
- Confirm all custom text appears
- Test on mobile device

---

## 🎨 All Available Email Templates

### 1. Exam Notification (`exam-notification.blade.php`)
- **Purpose:** Main exam scheduling email with access code
- **Sent when:** Assigning exam to applicants
- **Customizable:** Yes ✅

### 2. Exam Assignment (`exam-assignment.blade.php`)
- **Purpose:** Bulk assignment notification
- **Sent when:** Bulk assigning exams
- **Customizable:** Yes ✅

### 3. Access Code (`access-code.blade.php`)
- **Purpose:** Send/resend access code only
- **Sent when:** Applicant requests access code
- **Customizable:** Yes ✅

### 4. Exam Results (`exam-result.blade.php`)
- **Purpose:** Send exam results to applicants
- **Sent when:** After exam completion (if enabled)
- **Customizable:** Yes ✅

### 5. Interview Schedule (`interview-schedule.blade.php`)
- **Purpose:** Interview invitation
- **Sent when:** Assigning interview schedule
- **Customizable:** Yes ✅

---

## 🎯 Quick Customization Checklist

- [ ] Change colors to EVSU maroon & gold
- [ ] Add EVSU logo
- [ ] Update school/department name
- [ ] Customize email subject
- [ ] Update instructions for EVSU rules
- [ ] Add campus information
- [ ] Update footer with EVSU contact info
- [ ] Clear cache
- [ ] Send test email
- [ ] Verify on mobile device

---

## 💡 Tips

### Keep it Professional
- Use school colors consistently
- Include official logo
- Keep text clear and concise
- Test on different email clients

### Make it Helpful
- Include all important information
- Provide clear instructions
- Add contact information
- Include direct links

### Ensure Deliverability
- Keep HTML simple
- Don't use too many images
- Test with both SMTP and SES
- Check spam folder if not received

---

## 🚀 Advanced: Database-Driven Templates (Future)

For even more flexibility, you could:

1. **Store template content in database**
   - Admin can edit via settings
   - No code changes needed
   - Version history tracking

2. **Use template variables**
   - `{{applicant_name}}`
   - `{{access_code}}`
   - `{{exam_date}}`

3. **Multiple template versions**
   - Different templates per program
   - Different templates per campus
   - A/B testing

This would require additional development but provides maximum flexibility.

---

## 📞 Need Help?

- Check Laravel Mail documentation: https://laravel.com/docs/12.x/mail
- Check Blade documentation: https://laravel.com/docs/12.x/blade
- Test changes in development first
- Always keep a backup before major changes

---

**Your email templates are production-ready and fully customizable!** 🎉

