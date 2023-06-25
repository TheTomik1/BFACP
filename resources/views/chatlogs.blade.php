@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-xs-12 col-lg-4">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::open()->route('chatlog.search')->method('GET') !!}

                    <div class="form-group">
                        <label class="control-label col-lg-2 col-sm-4">Server</label>

                        <div class="col-lg-10 col-sm-8">
                            <select class="form-control" name="server" id="server">
                                <option value="-1" <?php echo \Illuminate\Support\Facades\Input::has('server') && \Illuminate\Support\Facades\Input::get('server') == -1 ? 'selected' : ''?>>
                                    Select Server...
                                </option>
                                @foreach($games as $game)
                                    <?php if($game->servers->count() == 0) { continue; } ?>
                                    <optgroup label="{{ $game->Name }}">
                                        @foreach($game->servers as $server)
                                            <option value="{{ $server->ServerID }}"<?php echo \Illuminate\Support\Facades\Input::has('server') && \Illuminate\Support\Facades\Input::get('server') == $server->ServerID ? 'selected' : ''?>>
                                                {{ $server->ServerName }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {!! Former::text('players')->label('Players')->help('Separate multiple players with a comma (,). Partial names accepted.') !!}
                    {!! Former::text('keywords')->label('Keywords')->help('Separate multiple keywords with a comma (,).') !!}

                    <div class="form-group" id="date-range-container">
                        <label class="control-label col-lg-2 col-sm-4">Date</label>

                        <div class="col-lg-10 col-sm-8">
                            <div id="date-range">
                                <i class="fa fa-calendar fa-lg"></i>&nbsp;
                                <span></span> <strong class="caret"></strong>
                            </div>

                            {!! Former::hidden('StartDateTime', '') !!}
                            {!! Former::hidden('EndDateTime', '') !!}
                        </div>
                    </div>

                    {!! Former::checkbox('showspam')->label('&nbsp;')->text('View Spam Messages') !!}

                    @if(\Illuminate\Support\Facades\Input::has('players') == '')
                    {!! Former::hidden('pid', \Illuminate\Support\Facades\Input::get('pid', '')) !!}
                    @endif

                    {!! Former::actions()->success_submit('Search') !!}

                    {!! Former::close() !!}
                </div>
            </div>
        </div>

        @if(isset($chat))
            <div class="col-xs-12 col-lg-8">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="box-title">
                            <h3 class="box-title">&nbsp;</h3>
                        </div>
                        <div class="box-tools">
                            {!! $chat->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed">
                                <thead>
                                @if(\Illuminate\Support\Facades\Input::get('server', -1) <= 0)
                                    <th>Game</th>
                                    <th>Server</th>
                                    <th>Player</th>
                                    <th>Subset</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                @else
                                    <th>Player</th>
                                    <th>Subset</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                @endif
                                </thead>

                                <tbody>
                                @forelse($chat as $message)
                                    <tr>
                                        @if(\Illuminate\Support\Facades\Input::get('server', -1) <= 0)
                                            <td>
                                                <span class="{{ $message->server->game->class_css }}">{{ $message->server->game->Name }}</span>
                                            </td>
                                            <td>
                                                <span tooltip="{{ $message->server->ServerName }}">
                                                    {{ $message->server->server_name_short or str_limit($message->server->ServerName, 30) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(is_null($message->logPlayerID))
                                                    {{ $message->logSoldierName }}
                                                @else
                                                    {!! link_to_route('player.show', $message->player->SoldierName, [$message->player->PlayerID, $message->player->SoldierName], ['target' => '_self']) !!}
                                                    @if($message->player->SoldierName != $message->logSoldierName)
                                                        &nbsp;<i class="fa fa-info-circle" tooltip="{{ $message->logSoldierName }}"></i>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($message->logSubset != 'Global')
                                                    <span class="{{ $message->class_css }}">{{ $message->logSubset }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span tooltip="{{ $message->logMessage }}">{{ $message->logMessage }}</span>
                                            </td>
                                            <td ng-bind="moment('{{ $message->stamp }}').format('LLL')"></td>
                                        @else
                                            <td>
                                                @if(is_null($message->logPlayerID))
                                                    {{ $message->logSoldierName }}
                                                @else
                                                    {!! link_to_route('player.show', $message->player->SoldierName, [$message->player->PlayerID, $message->player->SoldierName], ['target' => '_self']) !!}
                                                @endif
                                            </td>
                                            <td>
                                                @if($message->logSubset != 'Global')
                                                    <span class="{{ $message->class_css }}">{{ $message->logSubset }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span tooltip="{{ $message->logMessage }}">{{ $message->logMessage }}</span>
                                            </td>
                                            <td ng-bind="moment('{{ $message->stamp }}').format('LLL')"></td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td <?php echo \Illuminate\Support\Facades\Input::get('server', -1) <= 0 ? 'colspan="6"' : 'colspan="4"';?>>
                                            <alert type="info">{!! Macros::faicon('fa-info-circle') !!}&nbsp;No results
                                                returned
                                            </alert>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            {!! $chat->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('scripts')
    {!! Html::script('js/plugins/daterangepicker/daterangepicker.js') !!}
    <script type="text/javascript">
        function updateDateRangeDisplay(date1, date2) {
            $('#date-range span').html(moment(date1).format('LLL') + '&nbsp;&ndash;&nbsp;' + moment(date2).format('LLL'));
            $("input[name='StartDateTime']").val(moment(date1).format());
            $("input[name='EndDateTime']").val(moment(date2).format());
        }

        $(function () {
            @if(\Illuminate\Support\Facades\Input::has('StartDateTime'))
            var startDate = moment('{{ \Illuminate\Support\Facades\Input::get("StartDateTime") }}');
            @else
            var startDate = moment().startOf('month');
            @endif

            @if(\Illuminate\Support\Facades\Input::has('EndDateTime'))
            var endDate = moment('{{ \Illuminate\Support\Facades\Input::get("EndDateTime") }}').endOf('day');
            @else
            var endDate = moment().endOf('month');
            @endif

            updateDateRangeDisplay(startDate, endDate);

            $('#date-range').daterangepicker({
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 Days': [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: startDate,
                endDate: endDate,
                timePicker: true,
                timePickerIncrement: 5,
                timePicker12Hour: true,
                timePickerSeconds: false,
                showDropdowns: true
            }, function (startDate, endDate) {
                updateDateRangeDisplay(startDate, endDate);
            });
        });
    </script>
@stop
