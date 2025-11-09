# 📊 Test Data Seeder - Complete Testing Data

## Overview

This seeder creates comprehensive test data for the EnrollAssess system, including:
- ✅ 200 applicants with diverse backgrounds
- ✅ Complete basic information data
- ✅ Exam scores (enrollassess_score)
- ✅ Interview scores with all 8 criteria
- ✅ Access codes
- ✅ GWAs (Grade Weighted Average)
- ✅ Various statuses (pending, exam-completed, interview-completed, admitted, rejected)

---

## 🎯 What Data is Created

### Applicants Distribution
- **Pending**: 10 applicants (10%)
- **Exam Completed**: 20 applicants (20%)
- **Interview Scheduled**: 15 applicants (15%)
- **Interview Completed**: 25 applicants (25%)
- **Admitted**: 20 applicants (20%)
- **Rejected**: 10 applicants (10%)

**Total**: 200 applicants

### Data Included for Each Applicant

#### 1. Applicant Data
- ✅ Application number
- ✅ Full name (first, middle, last)
- ✅ Preferred course (BS CS, BS IT, BS CE, BS Data Science)
- ✅ Email address
- ✅ Phone number
- ✅ Exam scores (enrollassess_score: 60-100)
- ✅ Interview scores (calculated from 8 criteria)
- ✅ GWA (Grade Weighted Average: 85-98)
- ✅ Status
- ✅ Exam completed date
- ✅ Verbal description

#### 2. Basic Information Data
- ✅ Sex (Male, Female, Other, Prefer not to say)
- ✅ Date of birth (ages 16-25)
- ✅ Age (calculated)
- ✅ Civil status (Single, Married, Widowed, Separated, Divorced)
- ✅ Applicant type (New College Applicant, Transferee, ALS passer)
- ✅ PWD status (Yes, No, Prefer not to answer)
- ✅ Complete address
- ✅ City/Municipality
- ✅ Province (18 different provinces, focus on Eastern Visayas)
- ✅ Senior High School strand (ABM, STEM, HUMSS, TVL, Others)
- ✅ Strand other (for "Others" strand)
- ✅ Senior High School name

#### 3. Access Codes
- ✅ Unique access codes
- ✅ Linked to exam
- ✅ Usage status (used/unused based on applicant status)
- ✅ Expiration dates

#### 4. Interviews
- ✅ Interview schedules
- ✅ Interviewer assignment
- ✅ 8 criteria scores (each 6-10 points):
  - Communication Skills
  - Motivation & Interest
  - Problem Solving Attitude
  - Program Understanding
  - Personality & Attitude
  - IT Background
  - Willingness to Learn
  - Overall Impression
- ✅ Overall interview score (calculated from criteria)
- ✅ Overall rating
- ✅ Recommendation (recommended, waitlisted, not-recommended)
- ✅ Written feedback (strengths, areas for improvement, notes)

---

## 🚀 How to Run

### Option 1: Run Only Test Data Seeder

```bash
php artisan db:seed --class=TestDataSeeder
```

### Option 2: Add to DatabaseSeeder (Recommended)

Add to `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        ExamSeeder::class,
        QuestionBankSeeder::class,
        ApplicantSeeder::class,
        SystemSettingsSeeder::class,
        TestDataSeeder::class, // Add this line
    ]);
}
```

Then run:
```bash
php artisan db:seed
```

### Option 3: Fresh Migration with Test Data

```bash
php artisan migrate:fresh --seed --seeder=TestDataSeeder
```

---

## 📊 Test Data Details

### Geographic Distribution
- **Provinces**: 18 different provinces
  - Eastern Visayas: Leyte, Southern Leyte, Biliran, Samar, Eastern Samar, Northern Samar
  - Other regions: Cebu, Bohol, Negros Oriental, Metro Manila, Quezon, Laguna, Palawan, Davao del Sur, Cagayan, Ilocos Norte, Pangasinan, Bulacan
- **Cities**: Multiple cities per province for realistic distribution

### Strand Distribution
- **ABM**: ~20% of applicants
- **STEM**: ~25% of applicants
- **HUMSS**: ~20% of applicants
- **TVL**: ~20% of applicants
- **Others**: ~15% of applicants (with various specifications)

### Age Distribution
- **16-20 years**: Majority of applicants
- **21-25 years**: Some applicants
- **26-30 years**: Few applicants
- **31+ years**: Very few applicants

### Gender Distribution
- **Male**: ~45%
- **Female**: ~50%
- **Other/Prefer not to say**: ~5%

### Score Ranges
- **Exam Scores**: 60-100 (realistic distribution)
- **Interview Scores**: 60-100 (calculated from 8 criteria)
- **GWA**: 85-98 (high academic performance)
- **Overall Rating**: Calculated using 60% exam, 30% GWA, 10% interview

---

## 🧪 Testing Reports

After running the seeder, you can test:

### 1. Geographic Performance Report
- ✅ Should show data from 18 provinces
- ✅ Average exam scores by province
- ✅ Top performing provinces
- ✅ Top 10 cities

### 2. Strand Distribution Report
- ✅ Distribution across 5 strands
- ✅ Performance by strand
- ✅ "Others" strand specifications

### 3. Demographic Overview Report
- ✅ Gender distribution
- ✅ Age distribution
- ✅ Civil status breakdown
- ✅ Applicant type distribution
- ✅ PWD statistics

### 4. Primary Reports
- ✅ EVSU Results Export
- ✅ Qualifiers List
- ✅ All reports should work with test data

---

## 🔍 Verification

After seeding, verify the data:

```bash
# Check applicants count
php artisan tinker
>>> App\Models\Applicant::count()
# Should return: 200

# Check basic info count
>>> App\Models\ApplicantBasicInfo::count()
# Should return: 200

# Check interviews count
>>> App\Models\Interview::count()
# Should return: ~70 (interview-scheduled + interview-completed + admitted + rejected)

# Check exam scores
>>> App\Models\Applicant::whereNotNull('enrollassess_score')->count()
# Should return: ~150 (exam-completed + interview-scheduled + interview-completed + admitted + rejected)

# Check interview scores
>>> App\Models\Applicant::whereNotNull('interview_score')->count()
# Should return: ~55 (interview-completed + admitted + rejected)
```

---

## ⚠️ Important Notes

1. **Prerequisites**: Make sure you have:
   - Users (especially instructors) - Run `UserSeeder` first
   - Exam created - Run `ExamSeeder` first
   - Questions created - Run `QuestionBankSeeder` first

2. **Data Overwrite**: This seeder creates new data. If you want to preserve existing data, run it on a fresh database.

3. **Realistic Data**: All data is generated using Faker, so it looks realistic but is completely fake.

4. **Relationships**: All relationships are properly established:
   - Applicants → Basic Info (one-to-one)
   - Applicants → Access Codes (one-to-one)
   - Applicants → Interviews (one-to-many)
   - Applicants → Instructors (many-to-one)

---

## 🐛 Troubleshooting

### Error: "No exam found"
**Solution**: Run `php artisan db:seed --class=ExamSeeder` first

### Error: "No instructors found"
**Solution**: Run `php artisan db:seed --class=UserSeeder` first

### Error: Foreign key constraint
**Solution**: Make sure all migrations are run: `php artisan migrate`

### Duplicate key errors
**Solution**: Clear existing data or run on fresh database: `php artisan migrate:fresh --seed`

---

## 📝 Customization

You can customize the seeder by modifying:
- **Total applicants**: Change `$totalApplicants = 200;`
- **Status distribution**: Modify the `$statuses` array
- **Provinces**: Add/remove provinces in the `$provinces` array
- **Score ranges**: Adjust score generation ranges
- **Date ranges**: Modify date generation ranges

---

## ✅ Success Indicators

After successful seeding, you should see:
```
Creating test data for applicants, basic info, exam scores, and interviews...
Created 50/200 applicants...
Created 100/200 applicants...
Created 150/200 applicants...
Created 200/200 applicants...
✅ Successfully created 200 applicants with complete test data!
   - Basic information data
   - Exam scores
   - Interview scores
   - Access codes
   - GWA scores
```

---

**Created**: January 2, 2025  
**Version**: 1.0  
**Status**: Ready for testing

