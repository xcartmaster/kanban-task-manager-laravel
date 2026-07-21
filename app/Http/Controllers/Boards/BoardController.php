<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\StoreBoardRequest;
use App\Models\Board;
use App\Services\Board\BoardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    /**
     * Inject the board domain service layer.
     */
    public function __construct(
        protected BoardService $boardService
    ) {}

    public function index(Request $request): Response
    {
        $shared_boards = $request->user()->sharedBoards()->with('columns.tasks')->get();

        return Inertia::render('Dashboard/Boards/Index', [
            'shared_boards' => $shared_boards
        ]);
    }

    /**
     * Store a newly created board in storage with SaaS limit validation.
     */
    public function store(StoreBoardRequest $request): RedirectResponse
    {
        // Gate::authorize('create', Board::class); // 403 Forbidden
        if (!Gate::allows('create', Board::class)) {
            return back()->withErrors([
                'name' => 'You have reached the limit of 3 free boards. Please upgrade to a Premium subscription!'
            ]);
        }

        // Delegate the data persistence task to the isolated domain service
        $this->boardService->createBoardForUser($request->user(), $request->validated());

        return redirect()->route('boards.index');
    }

    /**
     * Display the specified board with its columns and tasks if authorized.
     */
    public function show(Board $board): Response
    {
        // 1. Strictly authorize access (BoardPolicy@before will auto-pass admins/managers,
        // and BoardPolicy@view will check regular members via pivot table)
        Gate::authorize('view', $board);

        // 2. Eager load nested columns and tasks to prevent N+1 database queries
        $board->load('columns.tasks');

        // 3. Render the dedicated Kanban screen view
        return Inertia::render('Dashboard/Boards/BoardShow', [
            'board' => $board
        ]);
    }
}
