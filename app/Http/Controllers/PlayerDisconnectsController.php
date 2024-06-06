<?php

namespace BFACP\Http\Controllers;

use BFACP\Battlefield\Disconnect;
use BFACP\Battlefield\Game;
use BFACP\Battlefield\Player;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

/*
 * Class BattlereportController.
 */
class PlayerDisconnectsController extends Controller
{

    /**
     * @param Disconnect $disconnect
     * @param Player     $player
     */
    public function __construct(Disconnect $disconnect, Player $player, Game $game)
    {
        parent::__construct();

        $this->middleware('permission:chatlogs');

        $this->disconnect = $disconnect;
        $this->player = $player;
        $this->game = $game;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $playerDisconnects = Disconnect::
            orderBy('timestamp', 'desc');

        $games = $this->game->with([
            'servers' => function ($query) {
                $query->active();
            },
        ])->get();

        if ($this->request->has('server') && is_numeric($this->request->get('server')) && $this->request->get('server') > 0) {
            $playerDisconnects->where("gameserver", $this->request->get('server'));
        }

        if ($this->request->has('players')) {
            $players = array_map('trim', explode(',', $this->request->get('players')));

            $playerDisconnects = $playerDisconnects->whereIn('playername', $players);
        }

        if ($this->request->has('StartDateTime') && $this->request->has('EndDateTime')) {
            $startDate = Carbon::parse($this->request->get('StartDateTime'))->setTimezone(new \DateTimeZone('UTC'));
            $endDate = Carbon::parse($this->request->get('EndDateTime'))->setTimezone(new \DateTimeZone('UTC'));
            $playerDisconnects = $playerDisconnects->whereBetween('timestamp', [$startDate, $endDate]);
        }

        $playerDisconnects = $playerDisconnects->paginate(25);

        foreach ($playerDisconnects as $playerDisconnect) {
            $playerDisconnect->formattedDatetime = Carbon::parse($playerDisconnect->datetime)->toIso8601String();
        }

        $page_title = trans('navigation.main.items.playerdisconnects.title');

        return View::make('disconnects', compact('playerDisconnects', 'games', 'page_title'));
    }
}