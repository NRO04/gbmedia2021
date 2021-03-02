<?php

namespace App\Http\Middleware;

use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskUsersReceivers;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            // return redirect(RouteServiceProvider::HOME);

            if ($this->blockUsers() == true) {
                
                return redirect()->route('tasks.list');
            } else {
                return redirect()->route('home.dashboard');

                //return redirect()->route('home.dashboard');
            }
        }

        return $next($request);
    }


    public function blockUsers()
    {


        $donot_block = false;
        $role_id = Auth::user()->setting_rol_id;
        $date = Carbon::now();

        //dd( Auth::user());                
        if ($role_id == 1 || $role_id == 12) {

            $donot_block = false;
        } else {

            $tasks = Task::where('status', 0)->get();

            foreach ($tasks as $task) {

                $user_exists = TaskUsersReceivers::where('user_id', auth()->user()->id)->where('task_id', $task->id)->exists();

                if ($user_exists) {
                    $task_comment = TaskComment::where('user_id', auth()->user()->id)->where('task_id', $task->id)->first();
                    $created_at = $task->created_at;
                    $time_elapsed = $created_at->diffInHours($date);
                    //dd($time_elapsed);

                    if ($time_elapsed > 21) {
                        $donot_block = true;
                    }
                }
            }
        }


        return $donot_block;
    }
}
