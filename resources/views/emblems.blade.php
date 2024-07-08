@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::open()->route('emblems.search')->method('GET') !!}

                    {!! Former::text('players')->label('Players')->help('Separate multiple players with a comma (,). Partial names accepted.') !!}

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
                                    <th>Emblem</th>
                                    <th>Player</th>
                                    <th>Wearing since</th>
                                </tr>
                            </thead>

                            <tbody>
                            @forelse ($playerEmblems as $playerEmblem)
                                <tr>
                                    <td>
                                        <img src={{ str_replace($configEmblemsPath, $configEmblemsBaseUrl, $playerEmblem->emblem_path) }} alt="Emblem" class="img-thumbnail">
                                    </td>
                                    <td>
                                        @if(is_null($playerEmblem->playername))
                                            {{ $playerEmblem->playername }}
                                        @else
                                            {!! link_to_route('player.show', $playerEmblem->playername, [$playerEmblem->player_id, $playerEmblem->playername], ['target' => '_self']) !!}
                                        @endif
                                    </td>
                                    <td ng-bind="moment('{{ $playerEmblem->created_at }}').format('LLL')"></td>
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
                        {!! $playerEmblems->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

