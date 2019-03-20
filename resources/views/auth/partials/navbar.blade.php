<!-- Authentication Links -->
@if (Auth::guest())
    <li><a href="{{ route('login') }}">Login</a></li>
    <li><a href="{{ route('register') }}">Register</a></li>
@else
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
            {{ Auth::user()->name }} <span class="caret"></span>
        </a>

        <ul class="dropdown-menu" role="menu">
            <li>
	            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
		            <i class="fa fa-btn fa-sign-out"></i>Logout
	            </a>
            </li>
	        {!! Form::open(['route' => ['logout'], 'id' => 'logout-form', 'style' => 'display: none']) !!} {!! Form::close() !!}
        </ul>
    </li>
@endif
