@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::open()->route('battlereport.search')->method('GET') !!}

                    <div class="form-group">
                        <label class="control-label col-lg-2 col-sm-4">Server</label>

                        <div class="col-lg-10 col-sm-8">
                            <select class="form-control" name="server" id="server">
                                <option value="-1" {{ \Illuminate\Support\Facades\Input::has('server') && \Illuminate\Support\Facades\Input::get('server') == -1 ? 'selected' : '' }}>
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
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed">
                            <thead>
                            <tr>
                                <th>Game</th>
                                <th>Server</th>
                                <th>Link</th>
                                <th>Map</th>
                                <th>Duration</th>
                                <th>Total players</th>
                                <th>Round end players</th>
                                <th>Date</th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse ($battleReports as $battleReport)
                                <tr>
                                    <td>
                                        <span class="{{ $battleReport->server->game->class_css }}">{{ $battleReport->server->game->Name }}</span>
                                    </td>
                                    <td>{{ $battleReport->ServerName }}</td>
                                    <td>
                                        <a href="{{ $battleReport->battlereport_url }}" target="_blank">
                                            {{ $battleReport->battlereport_url }}
                                        </a>
                                    </td>
                                    <td>{{ $battleReport->map }}</td>
                                    <td>{{ round($battleReport->duration / 60, 2) }} minutes</td>
                                    <td>{{ $battleReport->total_players  }}</td>
                                    <td>{{ $battleReport->round_end_players }}</td>
                                    <td ng-bind="moment('{{ $battleReport->datetime }}').format('LLL')"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No battlereports found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box-footer">
                    <div class="pull-right">
                        {!! $battleReports->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    </div>
                </div>
            </div>
        </div>
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
