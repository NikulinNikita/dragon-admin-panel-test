@extends('layouts.app')

@section('content')
	<?php $errors = isset($errors) ? $errors : new \Illuminate\Support\Collection()  ?>
<div class="container h-100">
    <div class="row align-items-center h-100">
        <div class="col-8 offset-2">
            <div class="card">
                <div class="card-header">
                    Вход для администратора
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('adminAuth.postLogin') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} row">
                            <label for="email-input" class="col-4 col-form-label">Ваш E-Mail</label>

                            <div class="col-8">
                            <input id="email-input" type="email" class="form-control" name="email">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>@lang("admin/common.AuthFailed")</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} row">
                            <label for="password-input" class="col-4 col-form-label">Ваш пароль</label>

                            <div class="col-8">
                                <input id="password-input" type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>@lang("admin/common.AuthFailed")</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--<div class="form-group">--}}
                        {{--<div class="col-md-6 col-md-offset-4">--}}
                        {{--<div class="checkbox">--}}
                        {{--<label>--}}
                        {{--<input type="checkbox" name="remember"> Remember Me--}}
                        {{--</label>--}}
                        {{--</div>--}}
                        {{--</div>--}}
                        {{--</div>--}}

                        <div class="form-group row">
                            <div class="col-6 offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-sign-in"></i>Войти
                                </button>

                                {{--<a class="btn btn-link" href="{{ route('password.request') }}">Forgot Your Password?</a>--}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
