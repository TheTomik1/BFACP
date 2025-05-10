<?php

namespace BFACP\Http\Controllers\Admin\Adkats;

use BFACP\Adkats\Special;
use BFACP\Battlefield\Player;
use BFACP\Facades\Main as MainHelper;
use BFACP\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class SpecialPlayersController.
 */
class SpecialPlayersController extends Controller
{
    /**
     * GuzzleHttp\Client.
     */
    protected $guzzle;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');

        $this->middleware('permission:admin.adkats.special.view', [
            'only' => [
                'index',
            ],
        ]);

        $this->middleware('permission:admin.adkats.special.edit', [
            'except' => [
                'index',
            ],
        ]);

        $this->guzzle = app('Guzzle');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $players = Special::with('player', 'game', 'server')->get();

        $groups = MainHelper::specialGroups();

        $page_title = trans('navigation.admin.adkats.items.special_players.title');

        return view('admin.adkats.special_players.index', compact('players', 'groups', 'page_title'));
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $groups = MainHelper::specialGroups();

        $groups = collect($groups)->pluck('group_name', 'group_id')->toArray();

        return view('admin.adkats.special_players.create', compact('groups'))->with('page_title',
            trans('navigation.admin.adkats.items.special_players.items.create.title'));
    }

    /**
     * @return mixed
     */
    public function store()
    {
        $v = Validator::make($this->request->all(), [
            'groups'  => 'required',
            'EndDateTime' => 'required',
            'player_name'     => 'unique:adkats_specialplayers,player_identifier',
        ]);

        if ($v->fails()) {
            return redirect()->route('admin.adkats.special_players.create')->withErrors($v)->withInput();
        }

        $player = Player::where('SoldierName', $this->request->get('player_name'))->first();
        $end_time = $this->request->get('EndDateTime');
        $groups = new Collection($this->request->get('groups', []));

        if ($this->request->has('groups')) {
            $groups = $groups->filter(function ($id) {
                if (is_numeric($id)) {
                    return true;
                }
            })->map(function ($id) {
                return (int) $id;
            });
        }

        $special_groups = MainHelper::specialGroups();
        $count = 0;

        foreach ($groups as $group_id) {
            $matched = $special_groups->filter(function ($item) use ($group_id) {
                return $item['group_id'] === $group_id;
            })->first();

            if ($matched) {
                $special = new Special();
                $special->player_group = $matched['group_key'];
                $special->player_id = $player->PlayerID;
                $special->player_identifier = $player->SoldierName;
                $special->player_effective = Carbon::now();
                $special->player_expiration = Carbon::parse($end_time)->setTimezone(new \DateTimeZone('UTC'));
                $special->save();

                ++$count;
                $this->log->info(sprintf('%s created special player "%s".', $this->user->username, $player->SoldierName));
            }
        }

        $this->messages[] = trans('alerts.special_player.created', compact('count'));

        return redirect()->route('admin.adkats.special_players.index')->withMessages($this->messages);
    }

    /**
     * Show the editing page.
     *
     * @param int $id Special Player ID
     *
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id) {
        $groups = MainHelper::specialGroups();

        $groups = collect($groups)->pluck('group_name', 'group_key', 'group_id')->toArray();
        $special_player = Special::findOrFail($id);

        return view('admin.adkats.special_players.edit', compact('special_player', 'groups'))->with('page_title',
            trans('navigation.admin.adkats.items.special_players.items.edit.title', ['id' => $id]));
    }

    /**
     * Update special player.
     *
     * @param int $id Special Player ID
     *
     * @return $this
     */
    public function update($id) {
        try {
            $special_player_edit = Special::findOrFail($id);
            $all_special_players = Special::where('player_identifier', $special_player_edit->player_identifier)->get();

            $player = Player::findOrFail($special_player_edit->player_id);
            $end_time = $this->request->get('EndDateTime');
            $groups = new Collection($this->request->get('groups', []));

            $v = Validator::make($this->request->all(), [
                'groups'  => 'required',
                'EndDateTime' => 'required',
            ]);

            if ($v->fails()) {
                return redirect()->route('admin.adkats.special_players.create')->withErrors($v)->withInput();
            }

            $already_assigned_groups = [];
            $count = 0;

            foreach ($all_special_players as $special_player) {
                $already_assigned_groups[] = $special_player->player_group;
            }

            foreach ($groups as $group) {
                if (!in_array($group, $already_assigned_groups, true)) {
                    $special = new Special();
                    $special->player_group = $group;
                    $special->player_id = $player->PlayerID;
                    $special->player_identifier = $player->SoldierName;
                    $special->player_effective = Carbon::now();
                    $special->player_expiration = Carbon::parse($end_time)->setTimezone(new \DateTimeZone('UTC'));
                    $special->save();

                    ++$count;
                    $this->log->info(sprintf('%s created (through update) special player "%s".', $this->user->username, $player->SoldierName));
                }
            }

            $special_player_edit->player_effective = Carbon::now();
            $special_player_edit->player_expiration = Carbon::parse($end_time)->setTimezone(new \DateTimeZone('UTC'));
            $special_player_edit->save();

            $this->log->info(sprintf('%s updated special player group "%s".', $this->user->username, $id));

            $this->messages[] = trans('alerts.special_player.created-e', compact('count'));
            $this->messages[] = trans('alerts.special_player.edited', ['id' => $id]);

            return redirect()->route('admin.adkats.special_players.index')->withMessages($this->messages);
        } catch (ModelNotFoundException $e) {
            $this->errors[] = trans('alerts.special_player.invalid', ['id' => $id]);

            return redirect()->route('admin.adkats.special_players.index')->withErrors($this->errors);
        }
    }

    /**
     * Remove special player.
     *
     * @param int $id Special Player ID
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function destroy($id)
    {
        try {
            $special_player = Special::findOrFail($id);
            $nickname = $special_player->player_identifier;
            $special_player->delete();

            $this->messages[] = trans('alerts.special_player.deleted', compact('nickname'));

            $this->log->info(sprintf('%s deleted special player %s.', $this->user->username, $nickname));

            return MainHelper::response([
                'url'      => route('admin.adkats.special_players.index'),
                'messages' => $this->messages,
            ]);
        } catch (ModelNotFoundException $e) {
            $this->errors[] = trans('alerts.special_player.invalid', ['id' => $id]);

            return redirect()->route('admin.adkats.special_players.index')->withErrors($this->errors);
        }
    }
}
