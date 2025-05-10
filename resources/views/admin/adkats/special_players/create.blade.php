@extends('layout.main')

@section('content')
    {!! Former::open()->route('admin.adkats.special_players.store') !!}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i> Selecting multiple groups, creates multiple special player instances for the player!
            </div>
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::text('player_name')->label('Player Name') !!}

                    <div id="special-player-range-container">
                        <label class="control-label col-lg-2 col-sm-4">Expiration</label>

                        <div class="col-lg-10 col-sm-8">
                            <div style="padding-top: 0.7rem" id="range">
                                <i class="fa fa-calendar fa-lg"></i>&nbsp;
                                <span></span> <strong class="caret"></strong>
                            </div>
                            {!! Form::hidden('EndDateTime') !!}
                            <span class="help-block">Start time has no effect. The player group becomes effective right away.</span>
                        </div>
                    </div>

                    {!! Former::select('groups[]')->label('Groups')
                        ->options($groups)
                        ->multiple()->size(count($groups, COUNT_RECURSIVE))
                        ->help('Hold CTRL to select multiple groups.')
                    !!}

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-sm-offset-4 col-lg-10 col-sm-8">
                            <button type="submit" class="btn bg-green">
                                <i class="fa fa-floppy-o"></i>&nbsp;<span>{{ trans('site.admin.special_players.create.buttons.save') }}</span>
                            </button>
                            {!! link_to_route('admin.adkats.special_players.index', trans('site.admin.special_players.create.buttons.cancel'), [], ['class' => 'btn bg-blue', 'target' => '_self']) !!}
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

            updateRangeDisplay(moment(), moment().add(1, 'h'));
            $("input[name='EndDateTime']").val(moment().add(1, 'h').format());

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
                    '3 Months': [moment(), moment().add(3, 'M')]
                },
                startDate: moment(),
                endDate: moment(),
                minDate: moment().subtract(1, 'd'),
                timePicker: true,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                timePickerSeconds: false
            }, function (startDate, endDate) {
                updateRangeDisplay(startDate, endDate);
                $("input[name='StartDateTime']").val(moment(startDate).format());
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
