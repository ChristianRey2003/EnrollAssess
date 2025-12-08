<?php

namespace App\Http\Controllers;

use App\Data\PhilippineLocations;
use App\Models\Applicant;
use App\Models\ApplicantBasicInfo;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BasicInfoController extends Controller
{
    /**
     * Show the basic information form.
     */
    public function showBasicInfoForm(Request $request)
    {
        // Check session for applicant ID
        $applicantId = $request->session()->get('applicant_id');
        
        if (!$applicantId) {
            return redirect()->route('applicant.login')
                ->with('error', 'Please verify your access code first.');
        }

        try {
            // Load applicant with relationships
            $applicant = Applicant::with(['accessCode', 'basicInfo'])->findOrFail($applicantId);
            
            // Check if applicant has an access code
            if (!$applicant->accessCode) {
                return redirect()->route('applicant.login')
                    ->with('error', 'No access code found. Please contact the administrator.');
            }

            // Check if access code has already been used
            if ($applicant->accessCode->is_used) {
                return redirect()->route('applicant.login')
                    ->with('error', 'This access code has already been used. You cannot retake the exam.');
            }

            // Check if basic info already completed
            if ($applicant->hasCompletedBasicInfo()) {
                return redirect()->route('exam.start.form')
                    ->with('info', 'You have already completed the basic information form.');
            }

            // Get the currently active exam
            $exam = Exam::where('is_active', true)->first();
            
            if (!$exam) {
                return redirect()->route('applicant.login')
                    ->with('error', 'No active exam is currently available. Please contact the administration office.');
            }

            // Check exam availability
            if (!$exam->isAvailable()) {
                return redirect()->route('applicant.login')
                    ->with('error', $exam->getAvailabilityMessage());
            }

            // Get dropdown options
            $provinces = PhilippineLocations::provinces();
            $sexOptions = PhilippineLocations::sexOptions();
            $civilStatusOptions = PhilippineLocations::civilStatusOptions();
            $strandOptions = PhilippineLocations::strandOptions();
            $applicantTypeOptions = PhilippineLocations::applicantTypeOptions();
            $pwdStatusOptions = PhilippineLocations::pwdStatusOptions();
            $citiesByProvince = PhilippineLocations::citiesByProvince();

            return view('exam.basic-info', compact(
                'applicant',
                'provinces',
                'sexOptions',
                'civilStatusOptions',
                'strandOptions',
                'applicantTypeOptions',
                'pwdStatusOptions',
                'citiesByProvince'
            ));

        } catch (\Exception $e) {
            Log::error('Basic info form error: ' . $e->getMessage());
            return redirect()->route('applicant.login')
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Store the basic information.
     */
    public function storeBasicInfo(Request $request)
    {
        // Check session for applicant ID
        $applicantId = $request->session()->get('applicant_id');
        
        if (!$applicantId) {
            return redirect()->route('applicant.login')
                ->with('error', 'Please verify your access code first.');
        }

        // Validation rules
        $rules = [
            'sex' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date|before:tomorrow|after:' . now()->subYears(100)->toDateString(),
            'age' => 'required|integer|min:16|max:99',
            'civil_status' => 'nullable|in:Single,Married,Widowed,Separated',
            'applicant_type' => 'required|in:New College Applicant,Transferee,ALS passer',
            'is_pwd' => 'required|in:Yes,No',
            'complete_address' => 'required|string|max:1000',
            'province' => 'required|string|max:255',
            'city_municipality' => 'required|string|max:255',
            'senior_high_school_strand' => 'required_unless:applicant_type,ALS passer|nullable|in:ABM,STEM,HUMSS,TVL,Others',
            'senior_high_school_strand_other' => 'required_if:senior_high_school_strand,Others|nullable|string|max:255',
            'senior_high_school_name' => 'required_unless:applicant_type,ALS passer|nullable|string|min:5|max:255',
            'facebook_link' => 'required|url|max:500',
        ];

        $messages = [
            'sex.required' => 'Please select your sex.',
            'date_of_birth.required' => 'Please enter your date of birth.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'age.required' => 'Please select your age.',
            'age.min' => 'Age must be at least 16.',
            'age.max' => 'Age must not exceed 99.',
            'applicant_type.required' => 'Please select your applicant type.',
            'is_pwd.required' => 'Please answer the PWD question.',
            'complete_address.required' => 'Please enter your complete address.',
            'province.required' => 'Please select your province.',
            'city_municipality.required' => 'Please select your city/municipality.',
            'senior_high_school_strand.required' => 'Please select your senior high school strand.',
            'senior_high_school_strand_other.required_if' => 'Please specify your strand.',
            'senior_high_school_name.required' => 'Please enter your senior high school name.',
            'senior_high_school_name.min' => 'School name must be at least 5 characters. Abbreviations are not allowed.',
            'facebook_link.required' => 'Please enter your Facebook link.',
            'facebook_link.url' => 'Please enter a valid URL for your Facebook link.',
            'facebook_link.max' => 'Facebook link must not exceed 500 characters.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            // Load applicant
            $applicant = Applicant::with('basicInfo')->findOrFail($applicantId);

            // Validate province/city combination
            if (!PhilippineLocations::isValidCityProvinceCombo($validated['city_municipality'], $validated['province'])) {
                return back()
                    ->withInput()
                    ->withErrors(['city_municipality' => 'The selected city does not belong to the selected province.']);
            }

            // If ALS passer, set SHS fields to null
            if ($validated['applicant_type'] === 'ALS passer') {
                $validated['senior_high_school_strand'] = null;
                $validated['senior_high_school_strand_other'] = null;
                $validated['senior_high_school_name'] = null;
            }

            // Create or update basic info
            $basicInfo = $applicant->basicInfo ?? new ApplicantBasicInfo(['applicant_id' => $applicantId]);
            
            $basicInfo->fill($validated);
            $basicInfo->completed_at = now();
            $basicInfo->save();

            DB::commit();

            Log::info("Basic info completed for applicant {$applicantId}");

            return redirect()->route('exam.start.form')
                ->with('success', 'Basic information saved successfully. You may now proceed to the exam.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Basic info store error: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to save basic information. Please try again.');
        }
    }

    /**
     * Get cities for a specific province (API endpoint).
     */
    public function getCitiesByProvince(string $province)
    {
        try {
            // Decode URL-encoded province name
            $province = urldecode($province);
            
            $cities = PhilippineLocations::getCitiesForProvince($province);
            
            return response()->json([
                'success' => true,
                'cities' => $cities,
                'hasCities' => !empty($cities)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching cities for province: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'cities' => [],
                'hasCities' => false,
                'message' => 'Error fetching cities'
            ], 500);
        }
    }
}

