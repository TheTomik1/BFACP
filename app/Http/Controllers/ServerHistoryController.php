<?php

namespace BFACP\Http\Controllers;

use Illuminate\Support\Facades\View;
use BFACP\Battlefield\Game;
use BFACP\Battlefield\Server\Server;
use Carbon\Carbon;

class ServerHistoryController extends Controller
{
    public function __construct(Game $game)
    {
        parent::__construct();
        $this->middleware('permission:chatlogs');
        $this->game = $game;
    }

    public function index()
    {
        $games = $this->game->with(['servers' => function ($query) {
            $query->active();
        }])->get();

        $page_title = trans('navigation.main.items.serverpopulation.title');

        $serverData = null;
        $serverId = request('server', -1);
        if ($serverId != -1) {
            $serverData = Server::find($serverId);
        }

        return View::make('serverhistory', compact('games', 'page_title', 'serverData'));
    }
}
