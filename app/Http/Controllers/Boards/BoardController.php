<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function index(Request $request): Response
    {
        $shared_boards = $request->user()->sharedBoards()->with('columns.tasks')->get();

        return Inertia::render('Dashboard/Boards/Index', [
            'shared_boards' => $shared_boards
        ]);
    }
}
