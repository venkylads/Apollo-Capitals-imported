<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\RecentlyViewedCustomer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTaskController extends Controller
{
    //returns a view all the tasks for admin
    public function showTasks(User $user){

        $this->authorize('viewTasksForAdmin', $user) ;

        $tasks = Task::paginate(3) ;
        $sortBy = 'created_at' ;
        $sortDirection = "asc" ;
        return view('view_tasks_for_admin', compact('tasks', 'sortBy', 'sortDirection')) ;
    }
    
    //returns a view with search results
    public function showTasksSearchResults(Request $request, User $user) {

        //checks the permissions based on user model, policy
        $this->authorize('viewTasksForAdmin', $user) ;
    
        $query = $request->input('query');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $priority = $request->input('priority');
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'created_at'); 
        $sortDirection = $request->input('sort_direction', 'desc'); 

        //query builder instance for Post
        $tasks = Task::query();

        if ($query) {
            $tasks = $tasks->where(function ($q) use ($query) {
            $q->where('task', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%")
              ->orWhereHas('user', function ($q) use ($query) {
                  $q->where('name', 'LIKE', "%{$query}%");
              });
        });
        }

        // date range filter
        if ($dateFrom && $dateTo) {
            $tasks->whereBetween('created_at', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $tasks->whereDate('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $tasks->whereDate('created_at', '<=', $dateTo);
        }

        // priority filter
        if ($priority) {
            $tasks->where('priority', $priority);
        }

        // status filter
        if ($status) {
            $tasks->where('status', $status);
        }

        // sorting
        $tasks->orderBy($sortBy, $sortDirection);

        //pagination
        $tasks = $tasks->paginate(3); 

        return view('view_tasks_for_admin', compact('tasks', 'sortBy', 'sortDirection'));
    }


    //returns a view to edit task form
    public function showEditForm(Request $request, $id) {
        $task = Task::findOrFail($id) ; 
        return view('edit_task_view_for_admin', compact('task')) ;
    }


    //update the task 
    public function updateTask(Request $request, $id) {
        $task = Task::findOrFail($id) ; 

        $request->validate([
            'task' => 'required|string',
            'description' => 'required|string',
            'due_date_time' => 'required',
            'priority' => 'required'
        ]) ;

        $task->task = $request->task ;
        $task->description = $request->description ;
        $task->due_date_time = $request->due_date_time ;
        $task->priority = $request->priority ;
        $task->save() ;

        return redirect()->route('view-tasks-for-admin')->with(['success' => 'Task Updated Successfully!']) ;
    }

    //deletes the task
    public function deleteTask(Request $request, $id) {
        $task = Task::findOrFail($id) ;
        $task->delete() ;

        //activity log
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
             'user_name' => $user->name,
             'role' => $user->role,
             'action' => 'user deleted a Task!',
             'ip_address' => request()->ip() ,
            'time' => now()
        ]) ;

        return redirect()->back()->with(['success' => 'Task Deleted Successfully!']) ;
    }


    /*
    *when we click on the view task it captures the user and customer information
    and it stores in database and it returns the view based on the roles
    *
    */
    public function viewCustomer(Request $request, $id){
        $customer = Customer::findOrFail($id) ;

        $is_email_existed = RecentlyViewedCustomer::where('email', $customer->email)
        ->where('user_id', auth()->user()->id)
        ->first() ;

        if(!$is_email_existed){
            $recentlyViewedCustomers = RecentlyViewedCustomer::create([
                'user_id' => auth()->user()->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number 
            ]) ;
        }else{
            $is_email_existed->created_at = now() ;
            $is_email_existed->save() ;
        }
        if(auth()->user()->role === 'Admin'){
            return view('customer_detailed_view_for_admin', compact('customer')) ;
        }
        return view('customer_detailed_view_for_user', compact('customer')) ;
    }


    /*it shows recently viewed customers to the specific user and deletes the customers in
    the recently viewed customers model if it is older then 3hrs and it shows the stuff based on
    the roles
    */
    public function recentlyViewedCustomers(Request $request) {

        $recentlyViewedCustomers = RecentlyViewedCustomer::where('created_at', '<', now()->subHours(3))
        ->where('user_id', '=', auth()->user()->id)
        ->get() ;
        foreach($recentlyViewedCustomers as $customer) {
            $customer->delete() ;
        }
        
        $recentlyViewedCustomers = RecentlyViewedCustomer::where('user_id', auth()->user()->id)->paginate(3) ;
        if('Admin' === auth()->user()->role) {
            $recentlyViewedCustomers = RecentlyViewedCustomer::paginate(3) ;
            return view('recently_viewed_customers', compact('recentlyViewedCustomers')) ;
        }
        return view('recently_viewed_customers', compact('recentlyViewedCustomers')) ;
    }


}