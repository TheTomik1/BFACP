<?php

namespace BFACP\Http\Controllers;

use BFACP\Battlefield\Emblem;
use BFACP\Battlefield\Player;
use BFACP\Facades\Main as MainHelper;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

/**
 * Class EmblemController.
 */
class EmblemsController extends Controller
{
    /**
     * @param Emblem $emblem
     * @param Player $player
     */
    public function __construct(Emblem $emblem, Player $player)
    {
        parent::__construct();

        $this->middleware('permission:chatlogs');

        $this->emblem = $emblem;
        $this->player = $player;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $playerEmblems = Emblem::orderBy('created_at', 'desc');

        $page_title = trans('navigation.main.items.emblems.title');

        if ($this->request->has('players')) {
            $players = array_map('trim', explode(',', $this->request->get('players')));
            $playerEmblems = $playerEmblems->whereIn('playername', $players);
        }

        $playerEmblems = $playerEmblems->paginate(50);

        foreach ($playerEmblems as $playerEmblem) {
            $playerEmblem->formattedDatetime = Carbon::parse($playerEmblem->created_at)->toIso8601String();
        }

        $configEmblemsBaseUrl = Config::get('bfacp.site.emblems.baseurl');
        $configEmblemsPath = Config::get('bfacp.site.emblems.path');

        return view('emblems', compact('playerEmblems', 'configEmblemsBaseUrl', 'configEmblemsPath', 'page_title'));
    }
}
