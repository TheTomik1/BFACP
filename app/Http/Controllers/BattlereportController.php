<?php

namespace BFACP\Http\Controllers;

use Illuminate\Support\Facades\View;
use BFACP\Battlefield\Battlereport;
use BFACP\Battlefield\Game;
use Carbon\Carbon;

/*
 * Class BattlereportController.
 */
class BattlereportController extends Controller
{
    /**
     * @param Battlereport $battlereport
     */
    public function __construct(Battlereport $battlereport, Game $game)
    {
        parent::__construct();

        $this->middleware('permission:battlereports');

        $this->game = $game;
        $this->battlereport = $battlereport;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $games = $this->game->with([
            'servers' => function ($query) {
                $query->active();
            },
        ])->get();

        $battleReports = Battlereport::join('bfacp_settings_servers', 'bfacp_battlereports.guid', '=', 'bfacp_settings_servers.battlelog_guid')
            ->join('tbl_server', 'ServerID', '=', 'bfacp_settings_servers.server_id')
            ->orderBy('bfacp_battlereports.datetime', 'desc');

        if ($this->request->has('server') && is_numeric($this->request->get('server')) && $this->request->get('server') > 0) {
            $battleReports->where('bfacp_settings_servers.server_id', $this->request->get('server'));
        }

        if ($this->request->has('StartDateTime') && $this->request->has('EndDateTime')) {
            $startDate = Carbon::parse($this->request->get('StartDateTime'))->setTimezone(new \DateTimeZone('UTC'));
            $endDate = Carbon::parse($this->request->get('EndDateTime'))->setTimezone(new \DateTimeZone('UTC'));
            $battleReports = $battleReports->whereBetween('datetime', [$startDate, $endDate]);
        }

        $battleReports = $battleReports->paginate(25);

        foreach ($battleReports as $battleReport) {
            $battleReport->formattedDatetime = Carbon::parse($battleReport->datetime)->toIso8601String();
        }

        $page_title = trans('navigation.main.items.battlereports.title');

        return View::make('battlereports', compact('battleReports', 'games', 'page_title'));
    }
}