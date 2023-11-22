@extends('layout.main')

@section('content')
    <div class="row">
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
