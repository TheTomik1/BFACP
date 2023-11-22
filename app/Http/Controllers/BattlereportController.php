<?php

namespace BFACP\Http\Controllers;

use Illuminate\Support\Facades\View;
use BFACP\Battlefield\Battlereport;
use Carbon\Carbon;

/*
 * Class BattlereportController.
 */
class BattlereportController extends Controller
{
    /**
     * @param Battlereport $battlereport
     */
    public function __construct(Battlereport $battlereport)
    {
        parent::__construct();

        $this->middleware('permission:battlereports');

        $this->battlereport = $battlereport;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $battleReports = Battlereport::join('bfacp_settings_servers', 'battlereports.guid', '=', 'bfacp_settings_servers.battlelog_guid')
            ->join('tbl_server', 'ServerID', '=', 'bfacp_settings_servers.server_id')
            ->orderBy('battlereports.datetime', 'desc')
            ->paginate(25);

        foreach ($battleReports as $battleReport) {
            $battleReport->formattedDatetime = Carbon::parse($battleReport->datetime)->toIso8601String();
        }

        $page_title = trans('navigation.main.items.battlereports.title');

        return View::make('battlereports', compact('battleReports', 'page_title'));
    }
}