<?php

namespace BFACP\Http\Controllers\Admin\Adkats;

use BFACP\Adkats\Record;
use BFACP\Battlefield\Player as Player;
use BFACP\Battlefield\Server\Server as Server;
use BFACP\Facades\Main as MainHelper;
use BFACP\Http\Controllers\Controller;
use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\Event as Event;

/**
 * Class BansController.
 */
class MutesController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');

        $this->middleware('permission:admin.adkats.mutes.create', [
            'only' => [
                'create',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create()
    {
        $player = Player::findOrFail($this->request->get('player_id'));

        $servers = Server::where('GameID', $player->game->GameID)->active()->pluck('ServerName', 'ServerID');
        $admin = MainHelper::getAdminPlayer($this->user, $player->game->GameID);

        $page_title = 'Create New Mute';

        return view('admin.adkats.mutes.create', compact('player', 'servers', 'admin', 'page_title'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $player = Player::findOrfail($this->request->get('player_id'));
        $admin = MainHelper::getAdminPlayer($this->user, $player->game->GameID);

        $mute_message = trim($this->request->get('message', null));
        $mute_server = $this->request->get('server', null);
        $mute_start = $this->request->get('muteStartDateTime', null);
        $mute_end = $this->request->get('muteEndDateTime', null);
        $mute_type = $this->request->get('type', null);

        $admin_id = is_null($admin) ? null : $admin->PlayerID;
        $admin_name = is_null($admin) ? Auth::user()->username : $admin->SoldierName;

        $mute_duration = '10518984';
        $command_type = 149;
        if ($mute_type == 'temp') {
            if (!empty($mute_start) && !empty($mute_end)) {
                $startDate = Carbon::parse($mute_start)->setTimezone(new \DateTimeZone('UTC'));
                $endDate = Carbon::parse($mute_end)->setTimezone(new \DateTimeZone('UTC'));
                $mute_duration = $startDate->diffInMinutes($endDate);
            }
        } else if ($mute_type == 'round') {
            $mute_duration = '0';
            $command_type = 11;
        }

        $record = new Record();
        $record->server_id = $mute_server;
        $record->target_id = $player->PlayerID;
        $record->target_name = $player->SoldierName;
        $record->command_type = $command_type;
        $record->command_action = $command_type;
        $record->command_numeric = $mute_duration;
        $record->source_id = $admin_id;
        $record->source_name = $admin_name;
        $record->record_message = $mute_message;
        $record->record_time = Carbon::now();
        $record->adkats_web = true;
        $record->save();

        $this->messages[] = sprintf('Mute #%u has been created.', $record->record_id);

        $this->cache->forget(sprintf('api.player.%u', $player->PlayerID));
        $this->cache->forget(sprintf('player.%u', $player->PlayerID));

        return redirect()->route('player.show', [$record->target_id])->with('messages', $this->messages);
    }

    /**
     * Unmutes the player.
     *
     * @param int $id Player ID
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function destroy($id)
    {
        $player = Player::findOrfail($id);
        $admin = MainHelper::getAdminPlayer($this->user, $player->game->GameID);

        $admin_id = is_null($admin) ? null : $admin->PlayerID;
        $admin_name = is_null($admin) ? Auth::user()->username : $admin->SoldierName;

        $record = new Record();
        $record->server_id = 1;
        $record->target_id = $player->PlayerID;
        $record->target_name = $player->SoldierName;
        $record->command_type = 150;
        $record->command_action = 150;
        $record->command_numeric = 0;
        $record->source_id = $admin_id;
        $record->source_name = $admin_name;
        $record->record_message = $this->request->get('message', 'Unbanned');
        $record->record_time = Carbon::now();
        $record->adkats_web = true;
        $record->save();

        $this->messages[] = sprintf('Mute #%u has been created.', $record->record_id);

        $this->cache->forget(sprintf('api.player.%u', $player->PlayerID));
        $this->cache->forget(sprintf('player.%u', $player->PlayerID));

        return redirect()->route('player.show', [$record->target_id])->with('messages', $this->messages);
    }
}
