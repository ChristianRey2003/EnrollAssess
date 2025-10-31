# GWA and Overall Rating Implementation Summary

## Overview
Implemented the CARD/TOR GWA field and Overall Admission Rating calculation based on the 60/30/10 formula as required by the official EVSU entrance examination evaluation format.

## Formula
**Overall Rating = (60% × UEE) + (30% × GWA) + (10% × Interview/Skill)**

Where:
- **UEE (University Entrance Examination)**: Already stored in `applicants.score` as percentage (0-100)
- **GWA (General Weighted Average)**: From CARD/TOR, stored in `applicants.card_tor_gwa` as percentage (0-100)
- **Interview/Skill**: Simple average of:
  - EnrollAssess Exam Score (`applicants.enrollassess_score`)
  - Interview Evaluation Score (`applicants.interview_score` - scaled from 80-point rubric to 0-100)

## Changes Implemented

### 1. Database Changes
**Migration**: `2025_10_31_073538_add_card_tor_gwa_to_applicants_table.php`
- Added `card_tor_gwa` column to `applicants` table (decimal, nullable)
- Stored once per applicant (not per interview)

### 2. Service Layer
**File**: `app/Services/AdmissionScoringService.php`
- Centralized scoring logic for 60/30/10 calculation
- `calculateOverallRating()`: Returns overall rating and component breakdown
- `hasAllRequiredScores()`: Checks if all components are available
- `getMissingScores()`: Lists missing components
- `getVerbalDescription()`: Maps rating to verbal description
- `isPassing()`: Determines if rating meets threshold (default 70%)

### 3. Model Updates
**File**: `app/Models/Applicant.php`
- Added `card_tor_gwa` to `$fillable` and `$casts`
- `getOverallRating()`: Returns full rating data with components
- `getOverallRatingValueAttribute()`: Accessor for quick rating access
- `hasAllRequiredScores()`: Wrapper for service method
- `getMissingScores()`: Wrapper for service method

### 4. Interview Form Updates
**File**: `resources/views/components/interview/evaluation-form.blade.php`
- Added GWA input field in sidebar
- Input validation: required, numeric, 0-100 range, 2 decimal places
- Shows current GWA value if already saved
- Warning if GWA not yet recorded
- Added GWA to required fields check in JavaScript
- Real-time validation updates submit button state

### 5. Controller Updates

#### InterviewController (Admin)
**File**: `app/Http/Controllers/InterviewController.php`
- Added GWA validation rule: `'card_tor_gwa' => 'required|numeric|min:0|max:100'`
- Save GWA to applicant on both draft save and final submit
- GWA persists even when saving drafts

#### InstructorController
**File**: `app/Http/Controllers/InstructorController.php`
- Added same GWA validation rule
- Save GWA to applicant on interview submission

### 6. View Updates

#### Interview Detail View
**File**: `resources/views/admin/interviews/show.blade.php`
- Display GWA if recorded
- Display Overall Admission Rating if all scores available
- Show component breakdown:
  - UEE (60%) weighted contribution
  - GWA (30%) weighted contribution
  - Interview/Skill (10%) weighted contribution

## User Flow

### During Interview
1. Interviewer opens interview evaluation form
2. Sidebar shows **CARD/TOR GWA** input field (required)
3. Interviewer asks applicant for their GWA and enters it as percentage
4. GWA must be provided before form submission
5. On submit, GWA is saved to `applicants` table

### After Interview Completion
1. All score components are available:
   - UEE from import
   - GWA from interview
   - EnrollAssess Exam from exam completion
   - Interview Score from rubric evaluation
2. Overall Rating is calculated using 60/30/10 formula
3. Rating is displayed on:
   - Interview detail page
   - Applicant detail pages (TODO: needs update)
   - Applicant list page (TODO: needs update)
4. Rating can be exported to XLSX (TODO: needs implementation)

## Component Weights Breakdown

| Component | Raw Score | Weight | Contribution Range |
|-----------|-----------|--------|-------------------|
| UEE       | 0-100%    | 60%    | 0-60 points       |
| GWA       | 0-100%    | 30%    | 0-30 points       |
| Interview/Skill | 0-100% | 10% | 0-10 points      |
| **TOTAL** | -         | **100%** | **0-100 points** |

### Interview/Skill Calculation
```
Interview/Skill = (EnrollAssess Exam % + Interview Evaluation %) / 2
```
Then multiplied by 10% for final contribution.

## Example Calculation

Given:
- UEE: 85%
- GWA: 90%
- EnrollAssess Exam: 80%
- Interview Score: 88% (70.4/80 scaled)

```
Interview/Skill = (80 + 88) / 2 = 84%

Overall Rating = (0.60 × 85) + (0.30 × 90) + (0.10 × 84)
              = 51 + 27 + 8.4
              = 86.4%
```

## Verbal Descriptions

| Overall Rating | Description |
|----------------|-------------|
| 95-100%        | Outstanding |
| 90-94%         | Excellent   |
| 85-89%         | Very Good   |
| 80-84%         | Good        |
| 75-79%         | Satisfactory|
| 70-74%         | Fair        |
| 60-69%         | Conditional |
| <60%           | Below Standards |

## Data Integrity

### Storage Rules
- **GWA**: Stored once per applicant (not per interview)
- **GWA Persistence**: Saved on both draft and final submission
- **Overwrite Behavior**: Latest GWA value overwrites previous (allows correction)
- **Required**: GWA is mandatory before interview form submission

### Validation Rules
- **Type**: Numeric (decimal with 2 places)
- **Range**: 0-100
- **Required**: Yes (cannot submit without GWA)
- **Frontend**: Real-time validation prevents submission
- **Backend**: Server-side validation enforces rules

## Files Modified

### New Files
1. `app/Services/AdmissionScoringService.php`
2. `database/migrations/2025_10_31_073538_add_card_tor_gwa_to_applicants_table.php`
3. `GWA_AND_OVERALL_RATING_IMPLEMENTATION.md` (this file)

### Modified Files
1. `app/Models/Applicant.php`
2. `app/Http/Controllers/InterviewController.php`
3. `app/Http/Controllers/InstructorController.php`
4. `resources/views/components/interview/evaluation-form.blade.php`
5. `resources/views/admin/interviews/show.blade.php`
6. `database/migrations/2025_10_27_072428_add_missing_production_indexes.php` (bugfix)

## Pending Tasks

### High Priority
1. **XLSX Export**: Implement official EVSU format export with 60/30/10 formula
2. **Export Button**: Add export functionality to admin interface
3. **Applicant List View**: Add GWA and Overall Rating columns
4. **Applicant Detail View**: Display GWA and Overall Rating prominently

### Medium Priority
5. **Instructor Views**: Add GWA and Overall Rating to instructor applicant portfolio
6. **Filtering/Sorting**: Add ability to sort/filter by Overall Rating
7. **Bulk Actions**: Export selected applicants with Overall Ratings

### Low Priority
8. **Analytics Dashboard**: Show statistics based on Overall Ratings
9. **Reports**: Generate reports segmented by Overall Rating ranges
10. **Validation History**: Track GWA changes/updates with audit log

## Testing Checklist

- [x] Migration runs successfully
- [x] GWA field added to applicants table
- [x] GWA input appears on interview form (admin & instructor)
- [x] GWA validation prevents submission without value
- [x] GWA saved to database on submission
- [ ] GWA displayed on interview detail page
- [ ] Overall Rating calculated correctly
- [ ] Overall Rating displayed when all scores available
- [ ] Component breakdown shows correct weighted values
- [ ] End-to-end test: import → exam → interview → GWA → Overall

## API Response Structure

When calling `$applicant->getOverallRating()`:

```php
[
    'overall_rating' => 86.40,
    'components' => [
        'uee' => [
            'raw' => 85.00,
            'weighted' => 51.00,
            'weight' => 60
        ],
        'gwa' => [
            'raw' => 90.00,
            'weighted' => 27.00,
            'weight' => 30
        ],
        'interview_skill' => [
            'raw' => 84.00,
            'weighted' => 8.40,
            'weight' => 10,
            'breakdown' => [
                'exam' => 80.00,
                'interview' => 88.00
            ]
        ]
    ]
]
```

## Notes
- The system now requires GWA input during interviews
- GWA is stored per applicant (not per interview session)
- Overall Rating is computed on-the-fly when all components are available
- Export functionality still needs to be implemented for official EVSU format
- Applicant list and detail views need updates to show new fields

