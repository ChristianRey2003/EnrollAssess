# EnrollAssess - System Architecture & Deployment Overview

## 🏗️ Production Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         INTERNET                                 │
│                  (Applicants, Admins, Instructors)              │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ HTTPS (Port 443)
                         │ SSL: Let's Encrypt
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                    DIGITALOCEAN DROPLET                          │
│                   (Singapore Region - ap1)                       │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    NGINX WEB SERVER                        │  │
│  │  • Reverse Proxy                                          │  │
│  │  • SSL Termination                                        │  │
│  │  • Static File Serving                                    │  │
│  │  • Gzip Compression                                       │  │
│  │  • Rate Limiting (Infrastructure Level)                   │  │
│  └────────────────────┬──────────────────────────────────────┘  │
│                       │ Unix Socket                              │
│                       ↓                                          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │              PHP 8.2-FPM (Laravel 12)                      │  │
│  │  ┌─────────────────────────────────────────────────────┐  │  │
│  │  │           ENROLLASSESS APPLICATION                   │  │  │
│  │  │                                                       │  │  │
│  │  │  AUTHENTICATION                                       │  │  │
│  │  │  ├─ Admin Login (rate limited: 5/min)               │  │  │
│  │  │  ├─ Applicant Access Code (rate limited: 10/min)    │  │  │
│  │  │  └─ Role-based Access (Admin, Instructor)           │  │  │
│  │  │                                                       │  │  │
│  │  │  EXAM SYSTEM                                          │  │  │
│  │  │  ├─ Question Bank Management                         │  │  │
│  │  │  ├─ Exam Builder (Sections)                          │  │  │
│  │  │  ├─ Access Code Generation                           │  │  │
│  │  │  ├─ Exam Interface (rate limited: 3/min)            │  │  │
│  │  │  └─ Scoring Engine                                   │  │  │
│  │  │                                                       │  │  │
│  │  │  INTERVIEW SYSTEM                                     │  │  │
│  │  │  ├─ Instructor Assignment                            │  │  │
│  │  │  ├─ Interview Scheduling                             │  │  │
│  │  │  ├─ Rubric-based Scoring                             │  │  │
│  │  │  └─ Pool Management                                  │  │  │
│  │  │                                                       │  │  │
│  │  │  REPORT GENERATION                                    │  │  │
│  │  │  ├─ XLSX Export (PhpSpreadsheet)                     │  │  │
│  │  │  ├─ PDF Export (LibreOffice conversion)              │  │  │
│  │  │  ├─ DOCX Export (PhpWord)                            │  │  │
│  │  │  └─ Statistical Analysis                             │  │  │
│  │  │                                                       │  │  │
│  │  │  BACKGROUND JOBS                                      │  │  │
│  │  │  ├─ Email Notifications (Queue)                      │  │  │
│  │  │  ├─ Report Generation (Queue)                        │  │  │
│  │  │  └─ Data Processing (Queue)                          │  │  │
│  │  │                                                       │  │  │
│  │  │  REAL-TIME FEATURES                                   │  │  │
│  │  │  ├─ Live Notifications (Pusher)                      │  │  │
│  │  │  ├─ Interview Assignment Alerts                      │  │  │
│  │  │  └─ Status Updates                                   │  │  │
│  │  └─────────────────────────────────────────────────────┘  │  │
│  └───────┬──────────┬───────────┬────────────┬──────────────┘  │
│          │          │           │            │                  │
│          ↓          ↓           ↓            ↓                  │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌──────────────┐      │
│  │  MySQL  │  │  Redis  │  │ Libre   │  │  Supervisor  │      │
│  │    8    │  │         │  │ Office  │  │ (Queue Mgr)  │      │
│  │─────────│  │─────────│  │─────────│  │──────────────│      │
│  │ Users   │  │ Cache   │  │ Headless│  │ Worker × 2   │      │
│  │ Exams   │  │ Sessions│  │ Convert:│  │ Auto-restart │      │
│  │ Applicants│ │ Queues │  │ XLSX→PDF│  │ Log rotate   │      │
│  │ Results │  │ Locks   │  │ DOCX→PDF│  │              │      │
│  │ Interviews│ │ Broadcast│          │  │              │      │
│  │ Reports │  │         │  │         │  │              │      │
│  └─────────┘  └─────────┘  └─────────┘  └──────────────┘      │
│                                                                  │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    CRON SCHEDULER                          │  │
│  │  • Task scheduling (every minute)                         │  │
│  │  • Automated backups (daily 2 AM)                        │  │
│  │  • Log cleanup (weekly)                                   │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    SECURITY LAYER                          │  │
│  │  • UFW Firewall (22, 80, 443)                            │  │
│  │  • Fail2Ban (brute-force prevention)                     │  │
│  │  • Automatic Security Updates                             │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                         │           │
                         ↓           ↓
        ┌─────────────────────┐   ┌──────────────────┐
        │   EXTERNAL SERVICES  │   │  MANAGED SERVICES│
        │─────────────────────│   │──────────────────│
        │ Pusher (ap1)        │   │ MySQL (Optional) │
        │ • Real-time updates │   │ • Auto backups   │
        │ • 100 connections   │   │ • Failover       │
        │ • 200k msg/day      │   │ • $15/month      │
        │ • FREE TIER         │   │                  │
        │                     │   │ Redis (Optional) │
        │ SMTP (Gmail)        │   │ • High perf      │
        │ • Access codes      │   │ • $15/month      │
        │ • Notifications     │   │                  │
        │ • App password      │   │                  │
        │                     │   │ Backup Storage   │
        │ Let's Encrypt       │   │ • Daily backups  │
        │ • SSL Certs         │   │ • Point-in-time  │
        │ • Auto-renewal      │   │ • Recovery       │
        │ • FREE              │   │                  │
        └─────────────────────┘   └──────────────────┘
```

---

## 📊 Data Flow Diagrams

### 1. Applicant Exam Flow

```
┌──────────────┐
│  Applicant   │
│   Browser    │
└──────┬───────┘
       │
       │ 1. Visit domain
       ↓
┌──────────────────────────────────┐
│  Access Code Login Page          │
│  Route: /applicant/login         │
└──────────────────┬───────────────┘
                   │
                   │ 2. Enter access code
                   │    Rate limit: 10/min
                   ↓
┌──────────────────────────────────┐
│  Verify Access Code              │
│  • Check valid & not used        │
│  • Check exam active             │
│  • Create session                │
└──────────────────┬───────────────┘
                   │
                   │ 3. Redirect to pre-requirements
                   ↓
┌──────────────────────────────────┐
│  Pre-Requirements Page           │
│  • Show exam details             │
│  • Privacy consent               │
│  • Rules and regulations         │
└──────────────────┬───────────────┘
                   │
                   │ 4. Accept & proceed
                   ↓
┌──────────────────────────────────┐
│  Basic Information Form          │
│  • Demographics                  │
│  • Education background          │
│  • Contact details               │
└──────────────────┬───────────────┘
                   │
                   │ 5. Submit info
                   ↓
┌──────────────────────────────────┐
│  Exam Start Confirmation         │
│  • Final instructions            │
│  • Timer warning                 │
│  • Ready check                   │
└──────────────────┬───────────────┘
                   │
                   │ 6. Start exam
                   │    Rate limit: 3/min
                   ↓
┌──────────────────────────────────┐
│  Exam Interface                  │
│  • Question display              │
│  • Section navigation            │
│  • Timer countdown               │
│  • Submit section                │
└──────────────────┬───────────────┘
                   │
                   │ 7. Complete all sections
                   │    Rate limit: 3/min
                   ↓
┌──────────────────────────────────┐
│  Exam Completion                 │
│  • Mark access code used         │
│  • Calculate score               │
│  • Queue notifications           │
└──────────────────┬───────────────┘
                   │
                   │ 8. Show results
                   ↓
┌──────────────────────────────────┐
│  Results Page                    │
│  • Score (weighted 60%)          │
│  • Status                        │
│  • Next steps                    │
└──────────────────────────────────┘
```

---

### 2. Admin Report Generation Flow

```
┌──────────────┐
│     Admin    │
│   Browser    │
└──────┬───────┘
       │
       │ 1. Navigate to Reports
       ↓
┌──────────────────────────────────┐
│  Reports Dashboard               │
│  • EVSU Results (XLSX)           │
│  • EVSU Results (PDF) ← Select   │
│  • Qualifiers List (DOCX)        │
│  • Statistical Analysis          │
└──────────────────┬───────────────┘
                   │
                   │ 2. Choose filters
                   ↓
┌──────────────────────────────────┐
│  Filter Selection                │
│  • Program (BSIT, BSCS...)       │
│  • Date range                    │
│  • Status (Passed, All)          │
└──────────────────┬───────────────┘
                   │
                   │ 3. Click Generate PDF
                   ↓
┌──────────────────────────────────┐
│  Queue Job (Background)          │
│  Step 1: Load template           │
│  Step 2: Query applicants        │
│  Step 3: Fill Excel template     │
│  Step 4: Save temp XLSX          │
└──────────────────┬───────────────┘
                   │
                   │ 4. LibreOffice conversion
                   ↓
┌──────────────────────────────────┐
│  LibreOffice Headless            │
│  Command:                        │
│  soffice --headless              │
│    --convert-to pdf              │
│    --outdir /tmp                 │
│    temp.xlsx                     │
└──────────────────┬───────────────┘
                   │
                   │ 5. PDF generated
                   ↓
┌──────────────────────────────────┐
│  Store Report                    │
│  • Save to storage/app/reports/  │
│  • Record in generated_reports   │
│  • Clean up temp files           │
└──────────────────┬───────────────┘
                   │
                   │ 6. Notify admin (Pusher)
                   ↓
┌──────────────────────────────────┐
│  Browser Notification            │
│  "Report ready for download"     │
│  [Download] button enabled       │
└──────────────────┬───────────────┘
                   │
                   │ 7. Download PDF
                   ↓
┌──────────────────────────────────┐
│  Admin's Computer                │
│  EVSU_Results_BSIT_2025.pdf      │
│  Ready to print/distribute       │
└──────────────────────────────────┘
```

---

### 3. Interview Assignment Flow

```
┌──────────────┐
│ Admin/Dept   │
│    Head      │
└──────┬───────┘
       │
       │ 1. View applicants
       ↓
┌──────────────────────────────────┐
│  Applicants List                 │
│  • Filter: Status = "Exam Done"  │
│  • Sort by score                 │
│  • Bulk actions available        │
└──────────────────┬───────────────┘
                   │
                   │ 2. Select applicants for interview
                   ↓
┌──────────────────────────────────┐
│  Assign Interview Modal          │
│  • Choose instructor(s)          │
│  • Set deadline                  │
│  • Add notes                     │
└──────────────────┬───────────────┘
                   │
                   │ 3. Confirm assignment
                   ↓
┌──────────────────────────────────┐
│  Create Interview Records        │
│  • Link applicant ↔ instructor   │
│  • Status: "Pending"             │
│  • Generate notifications        │
└──────────────────┬───────────────┘
                   │
                   ├─────────────────────┐
                   │                     │
                   ↓                     ↓
┌──────────────────────────┐  ┌──────────────────────────┐
│  Pusher Broadcast        │  │  Email Queue Job         │
│  Real-time notification  │  │  Send to instructor      │
│  to instructor dashboard │  │  "New interview assigned"│
└──────────────────────────┘  └──────────────────────────┘
                   │
                   │ 4. Instructor notified
                   ↓
┌──────────────────────────────────┐
│  Instructor Dashboard            │
│  • View assigned interviews      │
│  • Claim from pool               │
│  • Schedule appointments         │
└──────────────────┬───────────────┘
                   │
                   │ 5. Conduct interview
                   ↓
┌──────────────────────────────────┐
│  Interview Scoring Form          │
│  • Rubric criteria (1-10)        │
│  • Comments                      │
│  • Final score calculation       │
└──────────────────┬───────────────┘
                   │
                   │ 6. Submit scores
                   ↓
┌──────────────────────────────────┐
│  Update Applicant Record         │
│  • interview_score (10%)         │
│  • enrollassess_score (old UX)   │
│  • Calculate overall rating      │
│  • Status: "Interview Complete"  │
└──────────────────┬───────────────┘
                   │
                   │ 7. Notify admin
                   ↓
┌──────────────────────────────────┐
│  Admin Dashboard                 │
│  Live update: Interview complete │
│  Applicant moves to next stage   │
└──────────────────────────────────┘
```

---

## 🔐 Security Architecture

### Defense in Depth Layers

```
┌─────────────────────────────────────────────────────────────┐
│  LAYER 1: NETWORK SECURITY                                  │
│  ├─ UFW Firewall (only ports 22, 80, 443 open)            │
│  ├─ DigitalOcean Cloud Firewall (optional)                │
│  └─ DDoS Protection (DigitalOcean infrastructure)          │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 2: TRANSPORT SECURITY                                │
│  ├─ HTTPS/TLS 1.3 (Let's Encrypt SSL)                     │
│  ├─ HSTS Headers                                           │
│  └─ Secure Cookie Flags                                    │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 3: WEB SERVER SECURITY                               │
│  ├─ Nginx Rate Limiting (infrastructure level)             │
│  ├─ Security Headers (X-Frame, X-XSS, etc.)               │
│  ├─ Hidden Server Version                                  │
│  └─ Request Size Limits                                    │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 4: APPLICATION SECURITY                              │
│  ├─ Rate Limiting Middleware (application level)           │
│  │  • Admin login: 5 attempts/min                         │
│  │  • Access code: 10 attempts/min                        │
│  │  • Exam submit: 3 attempts/min                         │
│  ├─ CSRF Protection (Laravel default)                      │
│  ├─ SQL Injection Prevention (Eloquent ORM)                │
│  ├─ XSS Prevention (Blade escaping)                        │
│  ├─ Mass Assignment Protection (fillable)                  │
│  └─ Password Hashing (bcrypt, 12 rounds)                   │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 5: ACCESS CONTROL                                    │
│  ├─ Role-Based Access Control (RBAC)                       │
│  │  • Administrator (full access)                         │
│  │  • Department Head (dept access)                       │
│  │  • Instructor (interview access)                       │
│  ├─ Middleware Guards (ajax.auth, role)                   │
│  └─ Session Management (Redis, secure cookies)             │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 6: DATA SECURITY                                     │
│  ├─ Database User Privileges (limited access)              │
│  ├─ Redis Password Authentication                          │
│  ├─ Private File Storage (storage/app/private)            │
│  ├─ Encrypted Sessions                                     │
│  └─ Secure Environment Variables (.env)                    │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  LAYER 7: MONITORING & INTRUSION PREVENTION                 │
│  ├─ Fail2Ban (auto-ban on repeated failures)              │
│  ├─ Laravel Logging (error tracking)                       │
│  ├─ Nginx Access Logs (audit trail)                       │
│  └─ Automatic Security Updates                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📈 Scaling Architecture

### Small Deployment (Current - $18-33/month)

```
┌────────────────────────────────────────┐
│      Single Droplet                    │
│  ┌──────────────────────────────────┐  │
│  │  Nginx + PHP-FPM + Laravel       │  │
│  │  + MySQL + Redis + LibreOffice   │  │
│  └──────────────────────────────────┘  │
│  Handles: <500 concurrent users       │
└────────────────────────────────────────┘
```

### Medium Deployment ($48-78/month)

```
┌────────────────────┐
│  Web Droplet       │     ┌─────────────────┐
│  • Nginx           │────→│ Managed MySQL   │
│  • PHP-FPM         │     │ • Auto backups  │
│  • Laravel         │     │ • Failover      │
│  • LibreOffice     │     └─────────────────┘
└────────────────────┘
         │                  ┌─────────────────┐
         └─────────────────→│ Managed Redis   │
                            │ • Cache         │
                            │ • Queues        │
                            └─────────────────┘
Handles: 500-1,500 concurrent users
```

### Large Deployment ($150+/month)

```
                     ┌─────────────────┐
                     │ Load Balancer   │
                     └────────┬────────┘
                              │
              ┌───────────────┼───────────────┐
              │               │               │
              ↓               ↓               ↓
     ┌────────────┐  ┌────────────┐  ┌────────────┐
     │ Web Node 1 │  │ Web Node 2 │  │ Web Node 3 │
     └──────┬─────┘  └──────┬─────┘  └──────┬─────┘
            │               │               │
            └───────────────┼───────────────┘
                            │
            ┌───────────────┴───────────────┐
            │                               │
            ↓                               ↓
   ┌─────────────────┐           ┌─────────────────┐
   │ MySQL Primary   │           │ Redis Cluster   │
   │ ┌─────────────┐ │           │ • Cache         │
   │ │Read Replica │ │           │ • Sessions      │
   │ └─────────────┘ │           │ • Queues        │
   └─────────────────┘           └─────────────────┘

Handles: 2,000+ concurrent users, multiple campuses
```

---

## 💾 Backup Strategy

```
┌─────────────────────────────────────────────────────────┐
│                     BACKUP LAYERS                        │
└─────────────────────────────────────────────────────────┘

LAYER 1: DATABASE BACKUPS
┌──────────────────────────────────────┐
│  Daily Automated Backups (2 AM)      │
│  • mysqldump to .sql.gz              │
│  • Keep last 7 days                  │
│  • Stored in /home/deployer/backups  │
└──────────────────────────────────────┘

LAYER 2: MANAGED DATABASE BACKUPS (if using)
┌──────────────────────────────────────┐
│  Automatic Daily Backups             │
│  • Point-in-time recovery            │
│  • 7-day retention                   │
│  • Managed by DigitalOcean           │
└──────────────────────────────────────┘

LAYER 3: DROPLET SNAPSHOTS
┌──────────────────────────────────────┐
│  Weekly Manual Snapshots             │
│  • Full server image                 │
│  • Quick restoration                 │
│  • $1.20/month per 20GB              │
└──────────────────────────────────────┘

LAYER 4: CODE VERSION CONTROL
┌──────────────────────────────────────┐
│  GitHub/GitLab Repository            │
│  • Source code versioned             │
│  • Easy rollback                     │
│  • Off-site storage                  │
└──────────────────────────────────────┘

RESTORATION TIME:
├─ Database only: 5-10 minutes
├─ Full droplet: 15-30 minutes
└─ Complete rebuild: 2-3 hours
```

---

## 🎓 Summary

### Your System is Production-Ready With:
- ✅ Modern, secure architecture
- ✅ Scalable deployment path
- ✅ Multiple security layers
- ✅ Automated backups
- ✅ Real-time capabilities
- ✅ Background job processing
- ✅ PDF/Excel export via LibreOffice

### Recommended Starting Point:
**Standard Setup ($33-48/month)**
- Droplet + Managed MySQL
- Handles 300-500 concurrent users
- Automated backups included
- Easy to scale up when needed

---

**Next Steps:** Follow `DEPLOYMENT_QUICK_START.md` or `DIGITALOCEAN_DEPLOYMENT_GUIDE.md`

**Last Updated:** November 6, 2025  
**Architecture Version:** v1.0 (Production)

