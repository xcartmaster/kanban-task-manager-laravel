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
        // $this->authorize('create', Board::class); // 403 Forbidden
        if (!Gate::allows('create', Board::class)) {
            return back()->withErrors([
                'name' => 'You have reached the limit of 3 free boards. Please upgrade to a Premium subscription!'
            ]);
        }

        // Delegate the data persistence task to the isolated domain service
        $this->boardService->createBoardForUser($request->user(), $request->validated());

        return redirect()->route('boards.index');
    }
}
