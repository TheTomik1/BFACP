<?php

namespace BFACP\Http\Controllers\Api;

use BFACP\Battlefield\Chat;
use BFACP\Battlefield\Server\Server;
use BFACP\Exceptions\PlayerNotFoundException;
use BFACP\Exceptions\RconException;
use BFACP\Facades\Battlefield;
use BFACP\Facades\Main as MainHelper;
use BFACP\Repositories\Scoreboard\LiveServerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config as Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ServersController.
 */
class ServersController extends Controller
{
    /**
     * Gathers the population for all servers.
     *
     * @param $id
     *
     * @return array
     */
    public function chat($id)
    {
        $chat = Chat::with('player')->where('ServerID', $id);

        if (Input::has('nospam') && Input::get('nospam') == 1) {
            $chat = $chat->excludeSpam();
        }

        if (Input::has('sb') && Input::get('sb') == 1) {
            $chat = $chat->orderBy('logDate', 'desc')->take(100)->get();
        } else {
            $chat = $chat->simplePaginate(30);
        }

        return MainHelper::response($chat, null, null, null, false, true);
    }

    /**
     * Live Scoreboard.
     *
     * @return array
     * @internal param int $id Server ID
     */
    public function population()
    {
        // Get active servers only
        $servers = Server::active()->get();

        // Sum the used slots
        $usedSlots = $servers->sum('usedSlots');

        // Sum the max slots
        $totalSlots = $servers->sum('maxSlots');

        // Init array
        $newCollection = [];

        foreach ($servers as $server) {
            // Convert the game name to lowercase
            $gameKey = strtolower($server->game->Name);

            // Add the server to the collection
            $newCollection[$gameKey]['servers'][] = $server;
        }

        foreach ($newCollection as $key => $collection) {
            $online = 0;
            $total = 0;

            foreach ($collection['servers'] as $server) {
                $online += $server->usedSlots;
                $total += $server->maxSlots;
            }

            $newCollection[$key]['stats'] = [
                'online'     => $online,
                'totalSlots' => $total,
                'percentage' => MainHelper::percent($online, $total),
            ];
        }

        return MainHelper::response([
                'online'     => $usedSlots,
                'totalSlots' => $totalSlots,
                'percentage' => MainHelper::percent($usedSlots, $totalSlots),
                'games'      => $newCollection,
            ] + trans('dashboard.population'), null, null, null, false, true);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws Exception
     */
    public function scoreboard($id)
    {
        try {
            $scoreboard = new LiveServerRepository(Server::findOrFail($id));

            if ($scoreboard->attempt()->check()) {
                if (Config::get('app.debug') && Input::has('verbose') && Input::get('verbose') == 1) {
                    $useVerbose = true;
                } else {
                    $useVerbose = false;
                }

                $data = $scoreboard->teams()->get($useVerbose);

                return MainHelper::response($data, null, null, null, false, true);
            }
        } catch (RconException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('No server found with id %s', $id));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function scoreboardExtra($id)
    {
        $sql = File::get(storage_path().DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'sbRoundStats.sql');

        $stats = [
            [
                'name'    => trans('scoreboard.factions')[1]['full_name'].' - Tickets',
                'data'    => [],
                'visible' => true,
            ],
            [
                'name'    => trans('scoreboard.factions')[2]['full_name'].' - Tickets',
                'data'    => [],
                'visible' => true,
            ],
            [
                'name'    => trans('scoreboard.factions')[1]['full_name'].' - Players',
                'data'    => [],
                'visible' => false,
            ],
            [
                'name'    => trans('scoreboard.factions')[2]['full_name'].' - Players',
                'data'    => [],
                'visible' => false,
            ],
            [
                'name'    => 'Players Online',
                'data'    => [],
                'visible' => false,
            ],
        ];

        $data['roundId'] = null;

        $results = DB::select($sql, [$id, $id]);

        foreach ($results as $result) {
            if (is_null($data['roundId'])) {
                $data['roundId'] = $result->round_id;
            }

            $stats[0]['data'][] = [
                strtotime($result->roundstat_time) * 1000,
                (int) $result->team1_tickets,
            ];

            $stats[1]['data'][] = [
                strtotime($result->roundstat_time) * 1000,
                (int) $result->team2_tickets,
            ];

            $stats[2]['data'][] = [
                strtotime($result->roundstat_time) * 1000,
                (int) $result->team1_count,
            ];

            $stats[3]['data'][] = [
                strtotime($result->roundstat_time) * 1000,
                (int) $result->team2_count,
            ];

            $stats[4]['data'][] = [
                strtotime($result->roundstat_time) * 1000,
                (int) ($result->team1_count + $result->team2_count),
            ];
        }

        $data['stats'] = $stats;

        return MainHelper::response($data, null, null, null, false, true);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function scoreboardAdmin()
    {
        $id = Input::get('server_id');

        try {
            if (! is_numeric($id) || $id <= 0) {
                throw new NotFoundHttpException('Invalid Server ID');
            }

            $allowedMethods = [
                'yell',
                'say',
                'kill',
                'move',
                'kick',
                'punish',
            ];

            $permissions = $this->cache->get('admin.perm.list');

            if (! Input::has('method') || ! in_array(Input::get('method'), $allowedMethods)) {
                throw new NotFoundHttpException();
            }

            if (! $this->isLoggedIn || ! $this->user->ability(null, $permissions['scoreboard'])) {
                throw new AccessDeniedHttpException();
            }

            $scoreboard = new LiveServerRepository(Server::findOrFail($id));

            if ($scoreboard->attempt()->check()) {
                $players = [];

                if (Input::has('players')) {
                    $players = explode(',', Input::get('players'));
                }

                switch (Input::get('method')) {
                    case 'yell':
                        $this->hasPermission('admin.scoreboard.yell');

                        if (Input::get('type') == 'Player' && Input::has('players')) {
                            foreach ($players as $player) {
                                $scoreboard->adminYell(Input::get('message', null), $player, null,
                                    Input::get('duration', 5), 'Player');
                            }
                        } else {
                            $scoreboard->adminYell(Input::get('message', null), Input::get('player', null),
                                Input::get('team', null), Input::get('duration', 5), Input::get('type', 'All'));
                        }
                        break;

                    case 'say':
                        $this->hasPermission('admin.scoreboard.say');

                        if (Input::get('type') == 'Player' && Input::has('players')) {
                            foreach ($players as $player) {
                                $scoreboard->adminSay(Input::get('message', null), $player, null, 'Player');
                            }
                        } else {
                            $scoreboard->adminSay(Input::get('message', null), Input::get('player', null),
                                Input::get('team', null), Input::get('type', 'All'));
                        }
                        break;

                    case 'kill':
                        $this->hasPermission('admin.scoreboard.kill');

                        if (Input::has('players')) {
                            $unkilled = [];

                            foreach ($players as $player) {
                                try {
                                    $scoreboard->adminKill($player, Input::get('message', null));
                                } catch (PlayerNotFoundException $e) {
                                    $unkilled[] = [
                                        'name'   => $player,
                                        'reason' => $e->getMessage(),
                                    ];
                                }
                            }

                            if (! empty($unkilled)) {
                                $data = $unkilled;
                            }
                        } else {
                            throw new RconException(400, 'No players selected.');
                        }
                        break;

                    case 'kick':
                        $this->hasPermission('admin.scoreboard.kick');

                        if (Input::has('players')) {
                            $unkicked = [];

                            foreach ($players as $player) {
                                try {
                                    $scoreboard->adminKick($player, Input::get('message', null));
                                } catch (PlayerNotFoundException $e) {
                                    $unkicked[] = [
                                        'name'   => $player,
                                        'reason' => $e->getMessage(),
                                    ];
                                }
                            }

                            if (! empty($unkicked)) {
                                $data = $unkicked;
                            }
                        } else {
                            throw new RconException(400, 'No player selected.');
                        }
                        break;

                    case 'move':
                        $this->hasPermission('admin.scoreboard.teamswitch');

                        if (Input::has('players')) {
                            $unmoved = [];

                            foreach ($players as $player) {
                                try {
                                    $scoreboard->adminMovePlayer($player, Input::get('team', null),
                                        Input::get('squad', null));
                                } catch (PlayerNotFoundException $e) {
                                    $unmoved[] = [
                                        'name'   => $player,
                                        'reason' => $e->getMessage(),
                                    ];
                                } catch (RconException $e) {
                                    $unmoved[] = [
                                        'name'   => $player,
                                        'reason' => $e->getMessage(),
                                    ];
                                }
                            }

                            if (! empty($unmoved)) {
                                $data = $unmoved;
                            }
                        } else {
                            throw new RconException(400, 'No player selected.');
                        }
                        break;

                    case 'punish':
                        $this->hasPermission('admin.scoreboard.punish');

                        if (Input::has('players')) {
                            foreach ($players as $player) {
                                $data[] = $scoreboard->adminPunish($player, Input::get('message'));
                            }
                        } else {
                            throw new RconException(400, 'No player selected.');
                        }
                        break;

                    case 'forgive':
                        $this->hasPermission('admin.scoreboard.forgive');

                        if (Input::has('players')) {
                            foreach ($players as $player) {
                                $scoreboard->adminForgive($player, Input::get('message'));
                            }
                        } else {
                            throw new RconException(400, 'No player selected.');
                        }
                        break;

                    default:
                        throw new NotFoundHttpException();
                }

                if (! isset($data)) {
                    $data = [];
                }

                return MainHelper::response($data, null, null, null, false, true);
            }
        } catch (PlayerNotFoundException $e) {
            return MainHelper::response(null, $e->getMessage(), 'error', null, false, true);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('No server found with id %s', $id));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Server $server
     *
     * @return mixed
     */
    public function extras(Server $server)
    {
        $cacheKeyMaps = sprintf('api.servers.%s.extra.maps', $server->ServerID);
        $cacheKeyPop = sprintf('api.servers.%s.extra.pop', $server->ServerID);

        $maps = $this->cache->remember($cacheKeyMaps, 60 * 24, function () use (&$server) {
            return $server->maps()->popular(Carbon::parse('-2 Week'))->get()->map(function ($map) use (&$server, &$max) {
                $mapname = Battlefield::mapName($map->MapName, $server->maps_file_path, $map->Gamemode);
                $gamemode = Battlefield::playmodeName($map->Gamemode, $server->modes_file_path);

                return [
                    'name' => sprintf('%s (%s)', $mapname, $gamemode),
                    'y'    => (int) $map->Total,
                ];
            });
        });

        $population = $this->cache->remember($cacheKeyPop, 60 * 24, function () use (&$server) {
            $sql = File::get(storage_path('sql/populationHistory.sql'));
            $results = collect($this->db->select($sql, [$server->ServerID]));

            return $results->map(function ($result) {
                $timestamp = Carbon::parse($result->SelectedDate)->getTimestamp();

                return [
                    $timestamp * 1000,
                    (int) $result->PlayerAvg,
                ];
            });
        });

        $data = [
            'maps'       => $maps,
            'population' => $population,
        ];

        return MainHelper::response($data, null, null, null, false, true);;
    }

    /**
     * Quick function for checking permissions for the scoreboard admin.
     *
     * @param string $permission Name of the permission
     * @param string $message    [description]
     *
     * @return bool [description]
     */
    private function hasPermission($permission, $message = 'You do have permission to issue this command')
    {
        if (! $this->user->ability(null, $permission)) {
            throw new AccessDeniedHttpException($message);
        }

        return true;
    }
}
