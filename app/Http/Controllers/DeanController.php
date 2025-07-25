<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GradeCompletionApplication;
use App\Models\Subject;
use App\Models\Announcement;
use Carbon\Carbon;

class DeanController extends Controller
{
    public function dashboard()
    {
        // Get the logged-in dean user
        $dean = $this->getLoggedInDean();
        
        // If no dean found, redirect to login
        if (!$dean) {
            return redirect('/')->with('error', 'Please log in as dean to access this page.');
        }
        
        // Get pending applications count
        $pendingApplicationsCount = GradeCompletionApplication::deanPending()->count();
        
        // Get student and faculty counts
        $studentsCount = User::where('role', 'student')->count();
        $facultyCount = User::where('role', 'faculty')->count();
        
        // Get deadline statistics for approved applications
        $approvedApplications = GradeCompletionApplication::where('dean_status', 'approved')
            ->whereNotNull('completion_deadline')
            ->get();
            
        $overdueCount = $approvedApplications->filter(function($app) {
            return $app->isDeadlinePassed();
        })->count();
        
        $approachingDeadlineCount = $approvedApplications->filter(function($app) {
            return $app->isDeadlineApproaching(30);
        })->count();
        
        $activeCount = $approvedApplications->filter(function($app) {
            return $app->deadline_status === 'active';
        })->count();
        
        return view('dean.dashboard', compact(
            'dean', 
            'pendingApplicationsCount', 
            'studentsCount', 
            'facultyCount',
            'overdueCount',
            'approachingDeadlineCount',
            'activeCount'
        ));
    }

    public function announcement()
    {
        $dean = $this->getLoggedInDean();
        
        if (!$dean) {
            return redirect()->route('login')->with('error', 'Please log in as a dean to access this page.');
        }
        
        // Get published announcements
        $publishedAnnouncements = Announcement::published()
            ->where('created_by', $dean->id)
            ->orderBy('published_at', 'desc')
            ->get();
        
        // Get draft announcements
        $draftAnnouncements = Announcement::draft()
            ->where('created_by', $dean->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('dean.announcement', compact('dean', 'publishedAnnouncements', 'draftAnnouncements'));
    }

    public function profile()
    {
        $dean = $this->getLoggedInDean();
        return view('dean.profile', compact('dean'));
    }

    public function gradeCompletionApplications()
    {
        $dean = $this->getLoggedInDean();
        
        $applications = GradeCompletionApplication::with(['student', 'subject'])
            ->deanPending()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('dean.grade-completion-applications', compact('dean', 'applications'));
    }

    public function approvedApplications()
    {
        $dean = $this->getLoggedInDean();
        
        $applications = GradeCompletionApplication::with(['student', 'subject'])
            ->where('dean_reviewed_by', $dean->id)
            ->where('dean_status', 'approved')
            ->orderBy('dean_reviewed_at', 'desc')
            ->get();
        
        return view('dean.approved-applications', compact('dean', 'applications'));
    }

    public function reviewApplication(Request $request, $application)
    {
        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'dean_remarks' => 'nullable|string|max:1000'
            ]);

            $application = GradeCompletionApplication::findOrFail($application);
            $dean = $this->getLoggedInDean();

            if (!$dean) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dean user not found'
                ], 403);
            }

            $updateData = [
                'dean_status' => $request->action === 'approve' ? 'approved' : 'rejected',
                'dean_remarks' => $request->dean_remarks,
                'dean_reviewed_at' => Carbon::now(),
                'dean_reviewed_by' => $dean->id
            ];

            // Set deadline and signature for approved applications
            if ($request->action === 'approve') {
                // Set deadline to 3 months (1 term) from approval date
                $updateData['completion_deadline'] = Carbon::now()->addMonths(3);
                
                // Generate digital signature message
                $deanName = $dean->name ?? $dean->username ?? 'Dean';
                
                $signatureMessage = "Approved by: " . $deanName . "\n";
                $signatureMessage .= "Position: Dean\n";
                $signatureMessage .= "Date: " . Carbon::now()->format('F j, Y') . " at " . Carbon::now()->format('g:i A') . "\n";
                $signatureMessage .= "Reference: GCA-" . str_pad($application->id, 6, '0', STR_PAD_LEFT);
                
                $updateData['dean_signature'] = $signatureMessage;
                $updateData['dean_signature_type'] = 'digital_approval';
                $updateData['dean_signature_date'] = Carbon::now();
            }

            $application->update($updateData);

            if ($request->action === 'approve') {
                $message = 'Application approved successfully and forwarded to faculty.';
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('dean.application-approved', $application->id)
                ]);
            } else {
                $message = 'Application rejected. Student will be notified.';
                
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getApplicationDetails($application)
    {
        $application = GradeCompletionApplication::with(['student', 'subject'])
            ->findOrFail($application);

        return response()->json([
            'success' => true,
            'application' => [
                'id' => $application->id,
                'student_name' => $application->student->name,
                'student_id' => $application->student->student_id,
                'subject_code' => $application->subject->code,
                'subject_name' => $application->subject->description,
                'current_grade' => $application->current_grade,
                'reason' => $application->reason,
                'supporting_document' => $application->supporting_document,
                'created_at' => $application->created_at->format('F j, Y g:i A'),
                'dean_status' => $application->dean_status,
                'dean_remarks' => $application->dean_remarks,
                'dean_reviewed_at' => $application->dean_reviewed_at ? $application->dean_reviewed_at->format('F j, Y g:i A') : null
            ]
        ]);
    }

    public function showApprovedApplication($application)
    {
        $application = GradeCompletionApplication::with(['student', 'subject'])
            ->findOrFail($application);
        
        $dean = $this->getLoggedInDean();
        
        // Check if this application was approved by the current dean
        if ($application->dean_status !== 'approved' || $application->dean_reviewed_by !== $dean->id) {
            abort(403, 'Unauthorized access to this approval record.');
        }
        
        return view('dean.application-approved', compact('application', 'dean'));
    }

    public function viewDocument(Request $request, $application)
    {
        $application = GradeCompletionApplication::findOrFail($application);
        
        if (!$application->supporting_document) {
            abort(404, 'Document not found');
        }
        
        $filePath = storage_path('app/public/' . $application->supporting_document);
        
        if (!file_exists($filePath)) {
            abort(404, 'Document file not found');
        }
        
        // Use original filename if available, otherwise fall back to stored filename
        $fileName = $application->original_filename ?: basename($application->supporting_document);
        $mimeType = mime_content_type($filePath);
        
        // If download parameter is present, force download
        if ($request->get('download')) {
            return response()->download($filePath, $fileName);
        }
        
        // Display all files inline by default
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }

    public function generateSignedDocument($application)
    {
        $application = GradeCompletionApplication::with(['student', 'subject'])->findOrFail($application);
        
        if ($application->dean_status !== 'approved') {
            abort(403, 'Application not approved');
        }
        
        return view('dean.signed-document', compact('application'));
    }

    /**
     * Get the currently logged-in dean user
     */
    private function getLoggedInDean()
    {
        // First try to get from Laravel's built-in auth
        if (auth()->check() && auth()->user()->role === 'dean') {
            return auth()->user();
        }
        
        // Then try to get from session
        $userId = session('user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->role === 'dean') {
                return $user;
            }
        }
        
        // Return null if no authenticated dean found
        return null;
    }

    /**
     * Create a new announcement
     */
    public function createAnnouncement(Request $request)
    {
        $dean = $this->getLoggedInDean();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,academic,administrative,urgent',
            'audience' => 'required|in:all,students,faculty,staff',
            'priority' => 'required|in:normal,high,urgent',
            'action' => 'required|in:save_draft,publish'
        ]);
        
        $announcement = new Announcement();
        $announcement->title = $request->title;
        $announcement->content = $request->content;
        $announcement->category = $request->category;
        $announcement->audience = $request->audience;
        $announcement->priority = $request->priority;
        $announcement->created_by = $dean->id;
        
        if ($request->action === 'publish') {
            $announcement->is_published = true;
            $announcement->is_draft = false;
            $announcement->published_at = now();
        } else {
            $announcement->is_published = false;
            $announcement->is_draft = true;
            $announcement->published_at = null;
        }
        
        $announcement->save();
        
        $message = $request->action === 'publish' ? 'Announcement published successfully!' : 'Announcement saved as draft!';
        
        return redirect()->route('dean.announcement')->with('success', $message);
    }

    /**
     * Publish a draft announcement
     */
    public function publishAnnouncement(Request $request, Announcement $announcement)
    {
        $dean = $this->getLoggedInDean();
        
        // Check if the dean owns this announcement
        if ($announcement->created_by !== $dean->id) {
            return redirect()->route('dean.announcement')->with('error', 'You can only publish your own announcements.');
        }
        
        $announcement->is_published = true;
        $announcement->is_draft = false;
        $announcement->published_at = now();
        $announcement->save();
        
        return redirect()->route('dean.announcement')->with('success', 'Announcement published successfully!');
    }

    /**
     * Delete an announcement
     */
    public function deleteAnnouncement(Request $request, Announcement $announcement)
    {
        $dean = $this->getLoggedInDean();
        
        // Check if the dean owns this announcement
        if ($announcement->created_by !== $dean->id) {
            return redirect()->route('dean.announcement')->with('error', 'You can only delete your own announcements.');
        }
        
        $announcement->delete();
        
        return redirect()->route('dean.announcement')->with('success', 'Announcement deleted successfully!');
    }
}
