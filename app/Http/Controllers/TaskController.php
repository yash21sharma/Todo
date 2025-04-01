<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        return view('todo');
    }
    
    public function fetchTasks()
    {
        // Transform is_completed to completed for frontend consistency
        return response()->json(Task::all()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'completed' => (bool)$task->is_completed,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at
            ];
        }));
    }
    
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|unique:tasks,title']);
        $task = Task::create(['title' => $request->title]);
        
        // Return with proper format for frontend
        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'completed' => false,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at
        ]);
    }
    
    public function toggle(Task $task)
    {
        $task->update(['is_completed' => !$task->is_completed]);
        
        // Return with proper format for frontend
        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'completed' => (bool)$task->is_completed,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at
        ]);
    }
    
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}


