@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Former::open()->id('mute-form')
                        ->route('admin.adkats.mutes.store')
                        ->rules([
                            'message' => 'required'
                        ]) !!}

                    {!! Form::hidden('player_id', $player->PlayerID) !!}

                    {!! Former::text('player')->value($player->SoldierName)->label(trans('adkats.mutes.edit.fields.field1'))->disabled() !!}

                    {!! Former::text('admin')->value($admin->SoldierName)->label(trans('adkats.mutes.edit.fields.field2'))->disabled() !!}

                    {!! Former::text('message')->label(trans('adkats.mutes.edit.fields.field3'))->maxlength(500) !!}

                    {!! Former::select('server')->options($servers)->label(trans('adkats.mutes.edit.fields.field4')) !!}

                    <div class="form-group" id="mute-range-container">
                        <label class="control-label col-lg-2 col-sm-4">{!! trans('adkats.mutes.edit.fields.field5') !!}</label>

                        <div class="col-lg-10 col-sm-8">
                            <div id="mute-range">
                                <i class="fa fa-calendar fa-lg"></i>&nbsp;
                                <span></span> <strong class="caret"></strong>
                            </div>
                            {!! Form::hidden('muteStartDateTime') !!}
                            {!! Form::hidden('muteEndDateTime') !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-inline">
                            <label for="type" class="col-sm-2 control-label">{{ trans('adkats.mutes.edit.fields.field7') }}</label>

                            <div class="col-sm-8">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="perm">
                                        {{ trans('player.profile.mutes.type.permanent.long') }}
                                    </label>
                                    &nbsp;
                                    <label>
                                        <input type="radio" name="type" value="temp" checked>
                                        {{ trans('player.profile.mutes.type.temporary.long') }}
                                    </label>
                                    &nbsp;
                                    <label>
                                        <input type="radio" name="type" value="round">
                                        {{ trans('player.profile.mutes.type.round.long') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-sm-offset-4 col-lg-10 col-sm-8">
                            <button type="submit" class="btn bg-green">
                                <i class="fa fa-floppy-o"></i>&nbsp;<span>Create Mute</span>
                            </button>
                            {!! link_to_route('player.show', trans('adkats.mutes.edit.buttons.profile'), [$player->PlayerID], ['class' => 'btn bg-blue', 'target' => '_self']) !!}
                        </div>
                    </div>
                    {!! Former::close() !!}
                </div>
            </div>
        </div>
    </div>
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

                if (type != 'perm') {
                    $('#mute-range-container').hide('slow');
                } else {
                    $('#mute-range-container').show('slow');
                }
            });

            if (!$("input[name='type']:checked").val().includes(['temp', 'round'])) {
                $('#mute-range-container').hide();
            }

            function updateBanRangeDisplay(date1, date2) {
                $('#mute-range span').html(moment(date1).format('LLL') + '&nbsp;&ndash;&nbsp;' + moment(date2).format('LLL'));
            }

            updateBanRangeDisplay(moment(), moment().add(1, 'h'));
            $("input[name='muteStartDateTime']").val(moment().format());
            $("input[name='muteEndDateTime']").val(moment().add(1, 'h').format());

            $('#mute-range').daterangepicker({
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
                updateBanRangeDisplay(startDate, endDate);
                $("input[name='muteStartDateTime']").val(moment(startDate).format());
                $("input[name='muteEndDateTime']").val(moment(endDate).format());
            });

            $('#mute-form').submit(function (e) {
                var btn = $(this).find('button');
                btn.find("i").removeClass('fa-eraser').addClass('fa-spinner fa-pulse');
                btn.attr('disabled', true);
                btn.find('span').text("<?php echo trans('adkats.mutes.edit.buttons.submit.text2');?>");
            });
        });
    </script>
@stop
