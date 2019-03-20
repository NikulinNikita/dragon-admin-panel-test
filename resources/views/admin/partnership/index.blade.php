@if (\Session::has('success'))

    <div class="alert alert-success alert-message">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <span aria-hidden="true">×</span>
        </button>
        <i class="fa fa-check fa-lg"></i>
        {{\Session::get('success')}}

    </div>

@endif
@if ($errors->any())

    <div class="alert alert-danger alert-message">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <span aria-hidden="true">×</span>
        </button>
        <i class="fa fa-close fa-lg"></i>
        Incorrect values

    </div>

@endif

{{ Form::open(['url' => 'admin_panel/partnership_settings', 'class' => 'form-horizontal'])}}
    <div class="panel panel-default form-elements">
        <div class="panel-body">
            <div class="form-elements col-sm-6">
                <div>
                    <div class="form-group">
                        {{
                            Form::label(
                                'partnership_network_depth',
                                trans("admin/partnership.DepthLevels"),
                                ['class' => 'col-sm-3']
                            )
                        }}
    
                        <div class="col-sm-5">
                            {{
                                Form::text(
                                    'partnership_network_depth',
                                    $partnership_settings['partnership_network_depth'],
                                    ['class' => 'form-control']
                                )
                            }}
                            @if ($errors->get('partnership_network_depth'))
                                <ul class="form-element-errors">
                                    @foreach ($errors->get('partnership_network_depth') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                @for ($i = 0; $i < $partnership_settings['partnership_network_depth']; $i++)
                    <div>
                        <div class="form-group">
                            {{
                                Form::label(
                                    'partnership_level_percent_' . $i,
                                    trans("admin/partnership.Level"). ' ' . ($i + 1),
                                    ['class' => 'col-sm-3']
                                )
                            }}

                            <div class="col-sm-5">
                                {{
                                    Form::text(
                                        'partnership_level_percent['.$i.']',
                                        $partnership_settings['partnership_level_percent'][$i],
                                        ['class' => 'form-control']
                                    )
                                }}
                                
                                @if (
                                        isset($errors->getMessages()['partnership_level_percent'])
                                        &&
                                        array_key_exists($i, $errors->getMessages()['partnership_level_percent'][0])
                                    )
                                    <div class="col-sm-12">
                                        <ul class="form-element-errors">
                                            <li>{{$errors->getMessages()['partnership_level_percent'][0][$i]}}</li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endfor

                <div>
                    <div class="form-group">
                        {{
                            Form::label(
                                'partnership_reward_run_days',
                                trans("admin/partnership.RunDays"),
                                ['class' => 'col-sm-3']
                            )
                        }}
    
                        <div class="col-sm-5">
                            {{
                                Form::text(
                                    'partnership_reward_run_days',
                                    $partnership_settings['partnership_reward_run_days'],
                                    ['class' => 'form-control']
                                )
                            }}

                            @if ($errors->get('partnership_reward_run_days'))
                                <ul class="form-element-errors">
                                    @foreach ($errors->get('partnership_reward_run_days') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        {{
                            Form::label(
                                'partnership_reward_run_hour',
                                trans("admin/partnership.RunHour"),
                                ['class' => 'col-sm-3']
                            )
                        }}

                        <div class="col-sm-5">
                            {{
                                Form::time(
                                    'partnership_reward_run_hour',
                                    $partnership_settings['partnership_reward_run_hour'],
                                    ['class' => 'form-control']
                                )
                            }}
                            @if ($errors->get('partnership_reward_run_hour'))
                                <ul class="form-element-errors">
                                    @foreach ($errors->get('partnership_reward_run_hour') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>

    <h3>{{ trans("admin/partnership.AgencyAccruals") }}</h3>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-elements col-sm-3">
                <h4>{{ trans("admin/partnership.MinimumThreshold") }}</h4>
                @foreach ($agent_reward_limits as $reward_limit)
                    <div>
                        <div class="form-group">
                            {{
                                Form::label(
                                    'agent_reward_limits' . $reward_limit->currency->id,
                                    $reward_limit->currency->code,
                                    ['class' => 'col-sm-3']
                                )
                            }}
                            <div class="col-sm-5">
                                {{
                                    Form::text(
                                        'agent_reward_limits['.$reward_limit->currency->id.']',
                                        $reward_limit->value,
                                        ['class' => 'form-control']
                                    )
                                }}

                                @if (
                                        isset($errors->getMessages()['agent_reward_limits'])
                                        &&
                                        array_key_exists($reward_limit->currency->id, $errors->getMessages()['agent_reward_limits'][0])
                                    )
                                    <div class="col-sm-12">
                                        <ul class="form-element-errors">
                                                <li>{{$errors->getMessages()['agent_reward_limits'][0][$reward_limit->currency->id]}}</li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-elements col-sm-3">
                <h4>{{ trans("admin/partnership.AutomaticAssignment") }}</h4>
                @foreach ($max_automatic_processing_limit as $processing_limit)
                    <div>
                        <div class="form-group mb15">
                            {{
                                Form::label(
                                    'max_automatic_processing_limit_' . $processing_limit->currency->id,
                                    $processing_limit->currency->code,
                                    ['class' => 'col-sm-3']
                                )
                            }}
                            <div class="col-sm-5">
                                {{
                                    Form::text(
                                        'max_automatic_processing_limit['.$processing_limit->currency->id.']',
                                        $processing_limit->value,
                                        ['class' => 'form-control']
                                    )
                                }}

                                @if (
                                        isset($errors->getMessages()['max_automatic_processing_limit'])
                                        &&
                                        array_key_exists($processing_limit->currency->id, $errors->getMessages()['max_automatic_processing_limit'][0])
                                    )
                                    <div class="col-sm-12">
                                        <ul class="form-element-errors">
                                            <li>{{$errors->getMessages()['max_automatic_processing_limit'][0][$processing_limit->currency->id]}}</li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="form-buttons panel-footer">
        <div role="group" class="btn-group">
            {{ 
                Form::button(
                    '<i class="fa fa-check"></i> Save',
                    ['class' => 'btn btn-success', 'type' => 'submit']
                )
            }}
            <a href="{{route('home')}}" class="btn btn-warning">
                <i class="fa fa-ban"></i> Cancel
            </a>
        </div>
    </div>

{{ Form::close() }}