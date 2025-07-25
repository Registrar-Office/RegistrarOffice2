<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subject;
use App\Models\StudentGrade;
use App\Models\AcademicYear;
use App\Models\GradeCompletionApplication;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    private function getLoggedInStudent()
    {
        // First try to get from Laravel's built-in auth
        if (Auth::check() && Auth::user()->role === 'student') {
            return Auth::user();
        }
        
        // Then try to get from session
        $userId = session('user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->role === 'student') {
                return $user;
            }
        }
        
        // Fallback for demo purposes - this should ideally redirect to login
        return User::where('role', 'student')->whereNotNull('track')->first();
    }

    public function dashboard()
    {
        $student = $this->getLoggedInStudent();
        
        // Get student's grade completion applications with deadline information
        $applications = GradeCompletionApplication::where('student_id', $student->id)
            ->with(['subject'])
            ->whereNotNull('completion_deadline')
            ->orderBy('completion_deadline', 'asc')
            ->get();
            
        $overdueCount = $applications->filter(function($app) {
            return $app->isDeadlinePassed();
        })->count();
        
        $approachingDeadlineCount = $applications->filter(function($app) {
            return $app->isDeadlineApproaching(30);
        })->count();
        
        $activeCount = $applications->filter(function($app) {
            return $app->deadline_status === 'active';
        })->count();
        
        return view('student.dashboard', compact(
            'student', 
            'applications',
            'overdueCount',
            'approachingDeadlineCount', 
            'activeCount'
        ));
    }

    public function announcement()
    {
        $student = $this->getLoggedInStudent();
        
        // Get published announcements for students or all audiences
        $announcements = Announcement::published()
            ->forAudience('students')
            ->orderBy('published_at', 'desc')
            ->get();
        
        return view('student.announcement', compact('student', 'announcements'));
    }

    public function gradeCompletion()
    {
        $student = $this->getLoggedInStudent();
        
        // Get subjects with INC, NFE, or NG grades that need completion
        $incompleteSubjects = \App\Models\Subject::whereHas('grades', function ($query) use ($student) {
            $query->where('user_id', $student->id)
                  ->whereIn('grade', ['INC', 'NFE', 'NG']);
        })->with(['grades' => function ($query) use ($student) {
            $query->where('user_id', $student->id);
        }])->get();

        // Get existing applications for this student with their status
        $existingApplications = \App\Models\GradeCompletionApplication::where('student_id', $student->id)
            ->pluck('subject_id')
            ->toArray();

        // Get detailed application status for each subject with deadline information
        $applicationStatus = \App\Models\GradeCompletionApplication::where('student_id', $student->id)
            ->get()
            ->keyBy('subject_id');

        return view('student.grade-completion', compact('student', 'incompleteSubjects', 'existingApplications', 'applicationStatus'));
    }

    public function profile()
    {
        $student = $this->getLoggedInStudent();
        return view('student.profile', compact('student'));
    }

    // Checklist functionality removed
    /*
    public function checklist()
    {
        $student = $this->getLoggedInStudent();
        
        // Get subjects grouped by year and trimester for the student's course and track
        $subjectsByYear = $student->getSubjectsByYearAndTrimester();
        
        // Get current academic year
        $currentAcademicYear = \App\Models\AcademicYear::getCurrentYear();
        
        // Calculate total units
        $totalUnits = $student->getAvailableSubjects()->sum('units');
        
        return view('student.checklist', compact('student', 'subjectsByYear', 'currentAcademicYear', 'totalUnits'));
    }
    */

    public function updateProfile(Request $request)
    {
        $student = $this->getLoggedInStudent();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);

        $student->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
        ]);

        return redirect()->route('student.profile')->with('success', 'Profile updated successfully!');
    }
    
    public function applyForGradeCompletion(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'reason' => 'required|string|min:20|max:500',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120' // 5MB max
        ]);

        $student = $this->getLoggedInStudent();
        
        // Check if student has INC, NFE, or NG grade for this subject
        $grade = StudentGrade::where('user_id', $student->id)
                           ->where('subject_id', $request->subject_id)
                           ->whereIn('grade', ['INC', 'NFE', 'NG'])
                           ->first();
        
        if (!$grade) {
            // Debug: Let's check what grades exist for this student and subject
            $existingGrade = StudentGrade::where('user_id', $student->id)
                                       ->where('subject_id', $request->subject_id)
                                       ->first();
            
            if ($existingGrade) {
                return response()->json(['success' => false, 'message' => 'You can only apply for completion if you have INC, NFE, or NG grade. Your current grade is: ' . $existingGrade->grade]);
            } else {
                return response()->json(['success' => false, 'message' => 'No grade found for this subject.']);
            }
        }

        // Check if there's already a pending application
        $existingApplication = \App\Models\GradeCompletionApplication::where('student_id', $student->id)
                                                                   ->where('subject_id', $request->subject_id)
                                                                   ->where('status', 'pending')
                                                                   ->first();
        
        if ($existingApplication) {
            return response()->json(['success' => false, 'message' => 'You already have a pending application for this subject.']);
        }

        try {
            $documentPath = null;
            $originalFilename = null;
            
            if ($request->hasFile('supporting_document')) {
                $file = $request->file('supporting_document');
                $originalFilename = $file->getClientOriginalName();
                $documentPath = $file->store('grade_completion_documents', 'public');
            }
            
            \App\Models\GradeCompletionApplication::create([
                'student_id' => $student->id,
                'subject_id' => $request->subject_id,
                'current_grade' => $grade->grade,
                'reason' => $request->reason,
                'supporting_document' => $documentPath,
                'original_filename' => $originalFilename,
                'status' => 'pending'
            ]);
            
            return response()->json(['success' => true, 'message' => 'Your grade completion application has been submitted successfully!']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Grade completion application error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while processing your application. Please try again later.'], 500);
        }
    }
}
