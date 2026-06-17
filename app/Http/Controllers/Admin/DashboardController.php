<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioFile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_files' => AudioFile::query()->count(),
            'total_size' => AudioFile::query()->sum('size'),
            'recent_uploads' => AudioFile::query()->latest()->limit(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
