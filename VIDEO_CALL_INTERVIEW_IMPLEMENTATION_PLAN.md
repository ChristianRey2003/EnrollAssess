# 📹 Video Call Interview Implementation Plan

**Feature:** Online Video Call Interviews using Jitsi Meet  
**Layout:** Option 1 - Video Call in Sidebar  
**Status:** 📝 Planning Phase  
**Date:** November 2025

---

## 🎯 Overview

Implement online video call functionality for interviews using Jitsi Meet (free, open-source). The video call interface will be embedded in the interview sidebar, allowing instructors to conduct interviews while filling out the evaluation form.

### Goals
- Enable online video interviews directly within the system
- No external accounts required for applicants
- Free solution (Jitsi Meet)
- Seamless integration with existing interview workflow
- Auto-generate unique meeting links per interview

---

## 🏗️ Technical Approach

### Video Call Solution: Jitsi Meet
- **Why Jitsi Meet?**
  - ✅ Completely free
  - ✅ No account required
  - ✅ Works in browser (no app installation)
  - ✅ Can be embedded via iframe
  - ✅ Privacy-friendly
  - ✅ Self-hostable (optional)

### Implementation Method
- Use Jitsi Meet public instance: `meet.jit.si`
- Generate unique room names per interview
- Embed via iframe in sidebar
- Auto-generate meeting links when interview is scheduled

---

## 📊 Database Changes

### Migration: Add `meeting_link` field to `interviews` table

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_meeting_link_to_interviews_table.php`

```php
Schema::table('interviews', function (Blueprint $table) {
    $table->string('meeting_link', 500)->nullable()->after('schedule_date');
    $table->string('meeting_room_name', 100)->nullable()->after('meeting_link');
});
```

**Fields:**
- `meeting_link` - Full Jitsi Meet URL (e.g., `https://meet.jit.si/EnrollAssess-Interview-123`)
- `meeting_room_name` - Unique room identifier (e.g., `EnrollAssess-Interview-123`)

### Update Interview Model

**File:** `app/Models/Interview.php`

Add to `$fillable` array:
```php
'meeting_link',
'meeting_room_name',
```

---

## 🔧 Backend Implementation

### 1. Interview Controller Updates

**File:** `app/Http/Controllers/InterviewController.php`

#### Method: `schedule()` - Auto-generate meeting link
```php
// After creating interview, generate meeting link
$meetingRoomName = 'EnrollAssess-Interview-' . $interview->interview_id;
$meetingLink = 'https://meet.jit.si/' . $meetingRoomName;

$interview->update([
    'meeting_link' => $meetingLink,
    'meeting_room_name' => $meetingRoomName,
]);
```

#### Method: `bulkSchedule()` - Generate links for bulk interviews
```php
// Generate unique meeting link for each interview
$meetingRoomName = 'EnrollAssess-Interview-' . $interview->interview_id;
$meetingLink = 'https://meet.jit.si/' . $meetingRoomName;

$interview->update([
    'meeting_link' => $meetingLink,
    'meeting_room_name' => $meetingRoomName,
]);
```

### 2. Instructor Controller Updates

**File:** `app/Http/Controllers/InstructorController.php`

#### Method: `scheduleInterview()` - Generate meeting link
```php
// After updating interview schedule
if (!$interview->meeting_link) {
    $meetingRoomName = 'EnrollAssess-Interview-' . $interview->interview_id;
    $meetingLink = 'https://meet.jit.si/' . $meetingRoomName;
    
    $interview->update([
        'meeting_link' => $meetingLink,
        'meeting_room_name' => $meetingRoomName,
    ]);
}
```

#### Method: `bulkScheduleInterviews()` - Generate links
```php
// Generate meeting link for each interview in bulk
$meetingRoomName = 'EnrollAssess-Interview-' . $interview->interview_id;
$meetingLink = 'https://meet.jit.si/' . $meetingRoomName;

$interview->update([
    'meeting_link' => $meetingLink,
    'meeting_room_name' => $meetingRoomName,
]);
```

### 3. Helper Method (Optional)

**File:** `app/Models/Interview.php`

Add method to generate meeting link:
```php
/**
 * Generate Jitsi Meet link for this interview
 */
public function generateMeetingLink()
{
    if (!$this->meeting_link) {
        $roomName = 'EnrollAssess-Interview-' . $this->interview_id;
        $this->update([
            'meeting_link' => 'https://meet.jit.si/' . $roomName,
            'meeting_room_name' => $roomName,
        ]);
    }
    return $this->meeting_link;
}
```

---

## 🎨 Frontend Implementation

### 1. Update Interview Evaluation Form Component

**File:** `resources/views/components/interview/evaluation-form.blade.php`

#### Add Video Call Section to Sidebar

**Location:** Inside `.interview-sidebar` div, after Applicant Information card

```blade
<!-- Video Call Section -->
@if($interview->meeting_link)
<div class="card">
    <div class="card-header">
        <h3>📹 Video Call</h3>
    </div>
    <div class="card-body">
        <div id="video-call-container" style="display: none;">
            <iframe 
                id="jitsi-meet-iframe"
                allow="camera; microphone; fullscreen; display-capture"
                style="width: 100%; height: 300px; border: none; border-radius: 8px;"
                src="{{ $interview->meeting_link }}?config.startWithVideoMuted=false&config.startWithAudioMuted=false">
            </iframe>
        </div>
        
        <div id="video-call-controls">
            <button 
                type="button" 
                id="start-video-call-btn"
                class="btn btn-primary"
                style="width: 100%; margin-bottom: 8px;"
                onclick="startVideoCall()">
                🎥 Start Video Call
            </button>
            
            <button 
                type="button" 
                id="stop-video-call-btn"
                class="btn btn-secondary"
                style="width: 100%; display: none;"
                onclick="stopVideoCall()">
                ⏹️ Stop Video Call
            </button>
            
            <div style="margin-top: 12px; padding: 8px; background: #F3F4F6; border-radius: 4px; font-size: 0.75rem; color: #6B7280;">
                <strong>Meeting Link:</strong><br>
                <a href="{{ $interview->meeting_link }}" target="_blank" style="word-break: break-all; color: #800020;">
                    {{ $interview->meeting_link }}
                </a>
            </div>
            
            <button 
                type="button"
                class="btn btn-outline"
                style="width: 100%; margin-top: 8px; font-size: 0.75rem;"
                onclick="copyMeetingLink()">
                📋 Copy Link
            </button>
        </div>
    </div>
</div>
@endif
```

#### Add JavaScript Functions

**Location:** Add to `@push('scripts')` section at the end of the file

```javascript
<script>
function startVideoCall() {
    const container = document.getElementById('video-call-container');
    const startBtn = document.getElementById('start-video-call-btn');
    const stopBtn = document.getElementById('stop-video-call-btn');
    
    container.style.display = 'block';
    startBtn.style.display = 'none';
    stopBtn.style.display = 'block';
    
    // Request camera/mic permissions
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(() => {
            console.log('Camera and microphone access granted');
        })
        .catch((error) => {
            console.error('Error accessing camera/microphone:', error);
            alert('Please allow camera and microphone access to use video call.');
        });
}

function stopVideoCall() {
    const container = document.getElementById('video-call-container');
    const startBtn = document.getElementById('start-video-call-btn');
    const stopBtn = document.getElementById('stop-video-call-btn');
    const iframe = document.getElementById('jitsi-meet-iframe');
    
    container.style.display = 'none';
    startBtn.style.display = 'block';
    stopBtn.style.display = 'none';
    
    // Reload iframe to stop video
    iframe.src = iframe.src;
}

function copyMeetingLink() {
    const link = '{{ $interview->meeting_link ?? "" }}';
    navigator.clipboard.writeText(link).then(() => {
        alert('Meeting link copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = link;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Meeting link copied to clipboard!');
    });
}
</script>
```

#### Add CSS Styles

**Location:** Add to `@push('styles')` section

```css
#video-call-container {
    margin-bottom: 12px;
}

#video-call-controls .btn {
    padding: 10px 16px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

#video-call-controls .btn-primary {
    background: #800020;
    color: white;
    border: none;
}

#video-call-controls .btn-primary:hover {
    background: #600018;
}

#video-call-controls .btn-secondary {
    background: #6B7280;
    color: white;
    border: none;
}

#video-call-controls .btn-outline {
    background: transparent;
    color: #800020;
    border: 1px solid #800020;
}

#video-call-controls .btn-outline:hover {
    background: #F9FAFB;
}
```

### 2. Update Interview Schedule Email Template

**File:** `resources/views/emails/interview-schedule.blade.php`

Add video call information section:

```blade
@if($interview->meeting_link)
<div style="margin: 20px 0; padding: 16px; background: #F0F9FF; border-left: 4px solid #800020; border-radius: 4px;">
    <h3 style="margin: 0 0 12px 0; color: #800020; font-size: 1.1rem;">📹 Online Video Interview</h3>
    <p style="margin: 0 0 12px 0; color: #374151;">
        Your interview will be conducted online via video call. Please join using the link below:
    </p>
    <a href="{{ $interview->meeting_link }}" 
       style="display: inline-block; padding: 12px 24px; background: #800020; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin-bottom: 8px;">
        🎥 Join Video Interview
    </a>
    <p style="margin: 8px 0 0 0; font-size: 0.875rem; color: #6B7280;">
        Meeting Link: <a href="{{ $interview->meeting_link }}" style="color: #800020; word-break: break-all;">{{ $interview->meeting_link }}</a>
    </p>
    <p style="margin: 12px 0 0 0; font-size: 0.875rem; color: #6B7280;">
        <strong>Note:</strong> Please ensure you have a stable internet connection and allow camera/microphone access when prompted.
    </p>
</div>
@endif
```

---

## 📧 Email Integration

### Update InterviewScheduleMail

**File:** `app/Mail/InterviewScheduleMail.php`

Ensure meeting link is passed to email view (already included in `$interview` object).

### Update InterviewInvitationMail (if exists)

**File:** `app/Mail/InterviewInvitationMail.php`

Add meeting link information if this mail class is used for interview invitations.

---

## ⚙️ Configuration (Optional)

### Create Config File

**File:** `config/video_call.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Video Call Provider
    |--------------------------------------------------------------------------
    |
    | Currently using Jitsi Meet public instance.
    | For production, consider self-hosting Jitsi for better privacy.
    |
    */
    'provider' => env('VIDEO_CALL_PROVIDER', 'jitsi'),
    
    /*
    |--------------------------------------------------------------------------
    | Jitsi Meet Configuration
    |--------------------------------------------------------------------------
    */
    'jitsi' => [
        'base_url' => env('JITSI_BASE_URL', 'https://meet.jit.si'),
        'room_prefix' => env('JITSI_ROOM_PREFIX', 'EnrollAssess-Interview'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Video Call Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'auto_start_video' => env('VIDEO_CALL_AUTO_START_VIDEO', false),
        'auto_start_audio' => env('VIDEO_CALL_AUTO_START_AUDIO', false),
        'enable_recording' => env('VIDEO_CALL_ENABLE_RECORDING', false),
    ],
];
```

### Update .env.example

Add optional configuration:
```env
# Video Call Configuration
VIDEO_CALL_PROVIDER=jitsi
JITSI_BASE_URL=https://meet.jit.si
JITSI_ROOM_PREFIX=EnrollAssess-Interview
VIDEO_CALL_AUTO_START_VIDEO=false
VIDEO_CALL_AUTO_START_AUDIO=false
```

---

## 🔄 Workflow Integration

### When Interview is Scheduled

1. **Admin/Instructor schedules interview** → Meeting link auto-generated
2. **Email sent to applicant** → Includes video call link
3. **Interview page shows** → Video call section in sidebar

### During Interview

1. **Instructor opens interview page** → Sees "Start Video Call" button
2. **Instructor clicks button** → Video call loads in sidebar
3. **Applicant receives email** → Clicks link to join
4. **Both participants** → See each other in video call
5. **Instructor fills form** → While seeing applicant on video

### Applicant Experience

1. Receives email with meeting link
2. Clicks link → Opens Jitsi Meet in browser
3. Allows camera/mic permissions
4. Joins meeting room
5. Sees instructor when instructor starts call

---

## ✅ Implementation Checklist

### Phase 1: Database & Backend
- [ ] Create migration for `meeting_link` and `meeting_room_name` fields
- [ ] Run migration
- [ ] Update `Interview` model `$fillable` array
- [ ] Update `InterviewController::schedule()` method
- [ ] Update `InterviewController::bulkSchedule()` method
- [ ] Update `InstructorController::scheduleInterview()` method
- [ ] Update `InstructorController::bulkScheduleInterviews()` method
- [ ] Add `generateMeetingLink()` helper method to Interview model (optional)

### Phase 2: Frontend - Interview Form
- [ ] Add video call card to sidebar in `evaluation-form.blade.php`
- [ ] Add JavaScript functions for start/stop video call
- [ ] Add CSS styles for video call section
- [ ] Test video call iframe embedding
- [ ] Test camera/microphone permissions

### Phase 3: Email Integration
- [ ] Update `interview-schedule.blade.php` email template
- [ ] Add video call section with meeting link
- [ ] Test email rendering with meeting link
- [ ] Verify link works when clicked from email

### Phase 4: Testing
- [ ] Test meeting link generation on interview schedule
- [ ] Test video call in sidebar (instructor view)
- [ ] Test applicant joining via email link
- [ ] Test video/audio functionality
- [ ] Test on different browsers (Chrome, Firefox, Safari, Edge)
- [ ] Test on mobile devices
- [ ] Test with multiple participants
- [ ] Test copy link functionality

### Phase 5: Documentation
- [ ] Update system documentation
- [ ] Create user guide for instructors
- [ ] Create guide for applicants
- [ ] Document troubleshooting steps

---

## 🧪 Testing Scenarios

### Test Case 1: Schedule Interview with Video Call
1. Admin schedules interview for applicant
2. Verify `meeting_link` is generated in database
3. Verify email sent to applicant includes meeting link
4. Verify interview page shows video call section

### Test Case 2: Instructor Starts Video Call
1. Instructor opens interview conduct page
2. Clicks "Start Video Call" button
3. Verify iframe loads Jitsi Meet
4. Verify camera/mic permissions requested
5. Verify video call appears in sidebar

### Test Case 3: Applicant Joins Video Call
1. Applicant receives email with meeting link
2. Clicks link from email
3. Verifies Jitsi Meet opens in new tab
4. Allows camera/mic permissions
5. Joins meeting room
6. Verifies can see instructor when instructor is in call

### Test Case 4: Multiple Interviews
1. Schedule multiple interviews
2. Verify each has unique meeting link
3. Verify links don't conflict

### Test Case 5: Bulk Schedule
1. Bulk schedule multiple interviews
2. Verify all get unique meeting links
3. Verify all emails include correct links

---

## 🚀 Future Enhancements (Optional)

### Phase 2 Features
- [ ] Self-hosted Jitsi instance for better privacy
- [ ] Video call recording (requires paid Jitsi or alternative)
- [ ] Screen sharing capability
- [ ] Chat functionality during call
- [ ] Waiting room feature
- [ ] Video call analytics (duration, participants)
- [ ] Mobile app integration
- [ ] Calendar integration (Google Calendar, Outlook)

### Advanced Features
- [ ] Video call quality settings
- [ ] Bandwidth optimization
- [ ] Multiple interviewers support
- [ ] Interview replay/recording
- [ ] Automated transcription
- [ ] AI-powered interview insights

---

## 📝 Notes

### Browser Compatibility
- ✅ Chrome/Edge: Full support
- ✅ Firefox: Full support
- ✅ Safari: Full support (may require HTTPS)
- ⚠️ Mobile browsers: Supported but may have limitations

### Security Considerations
- Meeting links are unique per interview
- Room names include interview ID (not easily guessable)
- No password protection by default (can be added if needed)
- Consider self-hosting Jitsi for production for better privacy

### Performance
- Video call iframe loads only when "Start Video Call" is clicked
- No impact on page load time initially
- Video call uses browser's WebRTC (efficient)

### Privacy
- Using public Jitsi Meet instance (meet.jit.si)
- For sensitive interviews, consider self-hosting
- Meeting links are only sent to applicant and visible to interviewer

---

## 🔗 Resources

- **Jitsi Meet:** https://jitsi.org/jitsi-meet/
- **Jitsi Embedding Guide:** https://jitsi.github.io/handbook/docs/dev-guide/dev-guide-iframe
- **WebRTC Browser Support:** https://caniuse.com/rtcpeerconnection

---

## 📋 Quick Reference

### Meeting Link Format
```
https://meet.jit.si/EnrollAssess-Interview-{interview_id}
```

### Database Fields
- `meeting_link` - Full URL
- `meeting_room_name` - Room identifier

### Key Files to Modify
1. `database/migrations/` - Add meeting_link field
2. `app/Models/Interview.php` - Add to fillable
3. `app/Http/Controllers/InterviewController.php` - Generate links
4. `app/Http/Controllers/InstructorController.php` - Generate links
5. `resources/views/components/interview/evaluation-form.blade.php` - Add UI
6. `resources/views/emails/interview-schedule.blade.php` - Add to email

---

**Status:** Ready for Implementation  
**Estimated Time:** 4-6 hours  
**Priority:** Medium  
**Dependencies:** None

