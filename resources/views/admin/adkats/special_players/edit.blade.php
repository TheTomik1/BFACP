@extends('layout.main')

@section('content')
    {!! Former::open()->route('admin.adkats.special_players.update', [$special_player->specialplayer_id]) !!}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i> Selecting multiple groups, creates multiple special player instances for the player! These new instances will copy the selected expiration.
            </div>

            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::text('player_name')->label('Player Name')->value($special_player->player_identifier)->disabled(true) !!}

                    <div id="special-player-range-container">
                        <label class="control-label col-lg-2 col-sm-4">Expiration</label>

                        <div class="col-lg-10 col-sm-8">
                            <div style="padding-top: 0.7rem" id="range">
                                <i class="fa fa-calendar fa-lg"></i>&nbsp;
                                <span></span> <strong class="caret"></strong>
                            </div>
                            {!! Form::hidden('EndDateTime', old("EndDateTime", $special_player->player_expiration)) !!}
                            <span class="help-block">Start time has no effect. The player group becomes effective right away.</span>
                        </div>
                    </div>

                    {!! Former::select('groups[]')->label('Groups')
                        ->options($groups)
                        ->select($special_player->player_group)
                        ->multiple()->size(count($groups, COUNT_RECURSIVE))
                        ->help('Hold CTRL to select multiple groups.')
                    !!}

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-sm-offset-4 col-lg-10 col-sm-8">
                            <button type="submit" class="btn bg-green">
                                <i class="fa fa-floppy-o"></i>&nbsp;<span>{{ trans('site.admin.special_players.edit.buttons.save') }}</span>
                            </button>
                            {!! link_to_route('admin.adkats.special_players.index', trans('site.admin.special_players.edit.buttons.cancel'), [], ['class' => 'btn bg-blue', 'target' => '_self']) !!}
                            <button class="btn bg-red" id="delete-special-player">
                                <i class="fa fa-trash"></i>&nbsp;<span>{{ trans('site.admin.special_players.edit.buttons.delete') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Former::close() !!}
@stop

@section('scripts')
    {!! Html::script('js/plugins/daterangepicker/daterangepicker.js') !!}
    <script type="text/javascript">
        $('#delete-special-player').click(function (e) {
            e.preventDefault();

            var btn = $(this);

            var csrf = "{{ csrf_token() }}";

            if (confirm('Are you sure you want to remove the {{ $special_player->player_group  }} group for special player {{ $special_player->player_identifier }}? This can\'t be undone.')) {
                btn.find('i').removeClass('fa-trash').addClass('fa-spinner fa-pulse');
                btn.parent().find('button').attr('disabled', true);
                $.ajax({
                    url: "{{ route('admin.adkats.special_players.destroy', $special_player->specialplayer_id) }}",
                    type: 'DELETE',
                    data: {
                        _token: csrf
                    }
                })
                    .done(function (data) {
                        toastr.success('Group has been removed!');
                        window.location.href = data.data.url;
                    })
                    .fail(function (e) {
                        toastr.error('Failed to remove the group!');
                    })
                    .always(function () {
                        btn.find('i').removeClass('fa-spinner fa-pulse').addClass('fa-trash');
                        btn.parent().find('button').attr('disabled', false);
                    });
            }
        });

        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });

            $("input[name='type']").on('ifChecked', function (event) {
                var type = $("input[name='type']:checked").val();

                if (type == 8) {
                    $('#special-player-range-container').hide('slow');
                } else {
                    $('#special-player-range-container').show('slow');
                }
            });

            if ($("input[name='type']:checked").val() == 8) {
                $('#special-player-range-container').hide();
            }

            function updateRangeDisplay(date1, date2) {
                $('#range span').html(moment(date1).format('LLL') + '&nbsp;&ndash;&nbsp;' + moment(date2).format('LLL'));
            }

            updateRangeDisplay(moment(), moment('<?php echo old("EndDateTime", $special_player->player_expiration); ?>'));

            $('#range').daterangepicker({
                ranges: {
                    '1 Hour': [moment(), moment().add(1, 'h')],
                    '2 Hours': [moment(), moment().add(2, 'h')],
                    '3 Hours': [moment(), moment().add(3, 'h')],
                    '1 Day': [moment(), moment().add(1, 'd')],
                    '2 Days': [moment(), moment().add(2, 'd')],
                    '3 Days': [moment(), moment().add(3, 'd')],
                    '1 Week': [moment(), moment().add(1, 'w')],
                    '2 Weeks': [moment(), moment().add(2, 'w')],
                    '3 Weeks': [moment(), moment().add(3, 'w')],
                    '1 Month': [moment(), moment().add(1, 'M')],
                    '2 Months': [moment(), moment().add(2, 'M')],
                    '3 Months': [moment(), moment().add(3, 'M')],
                    'Permanent': [moment(), moment().add(20, 'Y')]
                },
                startDate: moment(),
                endDate: moment('<?php echo old("EndDateTime", $special_player->player_expiration); ?>'),
                minDate: moment().subtract(1, 'd'),
                timePicker: true,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                timePickerSeconds: false
            }, function (startDate, endDate) {
                updateRangeDisplay(startDate, endDate);
                $("input[name='EndDateTime']").val(moment(endDate).format());
            });

            $('#form').submit(function (e) {
                var btn = $(this).find('button');
                btn.find("i").removeClass('fa-eraser').addClass('fa-spinner fa-pulse');
                btn.attr('disabled', true);
                btn.find('span').text("<?php echo trans('adkats.bans.edit.buttons.submit.text2');?>");
            });
        });
    </script>
@stop
