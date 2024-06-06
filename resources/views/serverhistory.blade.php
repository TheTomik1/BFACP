@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::open()->route('serverhistory.listing')->method('GET') !!}

                    <div class="form-group">
                        <label class="control-label col-lg-2 col-sm-4">Server</label>
                        <div class="col-lg-10 col-sm-8">
                            <select class="form-control" name="server" id="server">
                                <option value="-1" {{ request('server') == -1 ? 'selected' : '' }}>
                                    Select Server...
                                </option>
                                @foreach($games as $game)
                                    @if($game->servers->count() > 0)
                                        <optgroup label="{{ $game->Name }}">
                                            @foreach($game->servers as $server)
                                                <option value="{{ $server->ServerID }}" {{ request('server') == $server->ServerID ? 'selected' : '' }}>
                                                    {{ $server->ServerName }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {!! Former::actions()->success_submit('Search') !!}
                    {!! Former::close() !!}
                </div>
            </div>
        </div>

        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">{{ $page_title }}</h3>
                </div>
                <div class="box-body">
                    @if(isset($serverData))
                        <input type="hidden" id="server_id" value="{{ $serverData->ServerID }}">

                        <section ng-controller="ServerController">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="box box-solid">
                                        <div class="box-header">
                                            <h3 class="box-title">&nbsp;</h3>
                                            <div ng-if="loading" class="box-tools pull-right animate-if" ng-cloak>
                                                <i class="fa fa-cog fa-lg fa-spin"></i><strong>&nbsp;{{ trans('common.loading') }}</strong>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-condensed" ng-table="maps.table" show-filter="true">
                                                            <tbody>
                                                            <tr ng-repeat="(key, map) in $data">
                                                                <td ng-bind="moment(map.map_load).format('lll')" sortable="'map_load'"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col1') }}'"></td>
                                                                <td ng-bind="moment(map.round_start).format('lll')"
                                                                    sortable="'round_start'"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col2') }}'"></td>
                                                                <td ng-bind="moment(map.round_end).format('lll')" sortable="'round_end'"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col3') }}'"></td>
                                                                <td ng-bind="map.map_name"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col4') }}'"></td>
                                                                <td ng-bind="map.gamemode"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col5') }}'"></td>
                                                                <td ng-bind="map.rounds"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col6') }}'"></td>
                                                                <td ng-bind="map.players.min"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col7') }}'"></td>
                                                                <td ng-bind="map.players.avg"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col8') }}'"></td>
                                                                <td ng-bind="map.players.max"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col9') }}'"></td>
                                                                <td ng-bind="map.players.join"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col10') }}'"></td>
                                                                <td ng-bind="map.players.left"
                                                                    data-title="'{{ trans('tables.servers.show.columns.col11') }}'"></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12" id="population-history"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12" id="popular-maps"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @else
                        <div class="alert alert-warning">
                            <strong>Warning!</strong> No server selected.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
