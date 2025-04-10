<?php

namespace Jabsa\LaravelLogs\Http\Controllers;

use App\Http\Controllers\Controller;
use File;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class LogController extends Controller
{
    // apply permissions middleware
    public function __construct()
    {
        $view_permission = config('laravel-logs.view_permission');
        $clear_permission =  config('laravel-logs.clear_permission');

        if (version_compare(app()->version(), '12.0', '>=')) {
            $this->middleware("can:$view_permission")->only(['index']);
            $this->middleware("can:$clear_permission")->only(['destroy']);
        }else{
            return [
                new Middleware("can:$view_permission", only: ['index']),
                new Middleware("can:$clear_permission", only: ['destroy']),
            ];
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // abort_if(Gate::denies(config('laravel-logs.view_permission')), 403);
        $logPath = storage_path('logs/laravel.log');
        $logContent = file_exists($logPath) ? file_get_contents($logPath) : '';

        // Format JSON in log content
        $logContent = preg_replace_callback('/({.*})/U', function ($match) {
            $json = json_decode($match[0], true);
            return $json ? json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $match[0];
        }, $logContent);

        return view('laravel-logs::details', compact('logContent'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $logFile = storage_path('logs/laravel.log');

        // Clear the log file
        File::put($logFile, '');

        return redirect()->back()->with('success', 'Log file cleared successfully.');
    }
}
