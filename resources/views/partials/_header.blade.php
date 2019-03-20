@if(url()->current() !== url('/admin_panel/login'))
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-3">
        <div class="d-flex align-items-center h-100" href="{{url()->current()}}">
            <span v-if="$store.state.User.name === null && $store.state.User.avatar === null">
                Панель дилера
            </span>
            <img v-if="$store.state.User.avatar !== null" :src="$store.state.User.avatar" :alt="$store.state.User.name" class="rounded-circle" style="max-height: 56px">
            <h5 v-if="$store.state.User.name !== null" class="ml-2 text-capitalize mb-0">@{{$store.state.User.name}}</h5>
        </div>
        <div v-if="command === 'success'" class="ml-5 text-success" role="alert">
            Менеджер уведомлен о проблеме.
        </div>
        <div v-else-if="command === 'failed'" class="ml-5 text-danger" role="alert">
            Уведомление не доставлено!
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <i v-if="logoutOn" class="fas fa-hourglass-start fa-3x"></i>
                {{--<li class="nav-item active"><a class="nav-link" href="{{route('table.show', ['id' => 1])}}">Домашняя страница</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/admin_panel') }}">Панель администратора</a></li>--}}
            </ul>
            <!-- <div class="navbar__lang dropdown">
                <a class="dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="{{ asset('img/flag/' . app()->getLocale() .'.svg') }}" alt="">
                    {{ LaravelLocalization::getCurrentLocaleNative() }}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    @foreach(LaravelLocalization::getSupportedLocales() as $code => $language)
                        <a href="{{ LaravelLocalization::getLocalizedURL($code) }}" class="dropdown-item @if(Lang::getLocale() == $code) active @endif" rel="nofollow">
                            <img src="{{ asset('img/flag/' . $code . '.svg') }}" alt="">
                            <span>{{ $language['native'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div> -->
        </div>
    </nav>
@endif