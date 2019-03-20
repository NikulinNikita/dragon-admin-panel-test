@if ($user)
	@if($user->hasRole('superadmin') || $user->hasRole('general') || $user->hasRole('manager'))
		<li>
			<a class="b-resourceDown" href='#' data-link="{{ route('admin.resource.down') }}"
			   data-down-until="{{ session()->get('admin.settings.ui_unblocking_time') }}">
				<i class="fa fa-btn fa-stop"></i> @lang("admin/header.ResourceDown")
			</a>
		</li>
		<li>
			<a class="b-resourceUp" href='#' data-link="{{ route('admin.resource.up') }}">
				<i class="fa fa-btn fa-play"></i> @lang("admin/header.ResourceUp")
			</a>
		</li>
	@endif
	@if($user->hasRole('superadmin') || $user->hasRole('general') || $user->hasRole('manager'))
		<li>
			<a href="{{ route('manager.tables') }}" target="_blank">
				<i class="fa fa-btn fa-address-book"></i> @lang("admin/header.Displays")
			</a>
		</li>
		<li>
			<a href="{{ route('manager.broadcast') }}" target="_blank">
				<i class="fa fa-btn fa-users"></i> @lang("admin/header.Models")
			</a>
		</li>
	@endif
	<li class="dropdown user user-menu" style="margin-right: 20px;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
			<span class="hidden-xs">{{ app()->getLocale() }}</span>
		</a>
		<ul class="dropdown-menu">
			<li class="@if(app()->getLocale() == 'en') active @endif"><a href="{{ route('admin.locale.switch', ['newLocale' => 'en']) }}">EN</a></li>
			<li class="@if(app()->getLocale() == 'ru') active @endif"><a href="{{ route('admin.locale.switch', ['newLocale' => 'ru']) }}">RU</a></li>
			<li class="@if(app()->getLocale() == 'zh-CN') active @endif"><a href="{{ route('admin.locale.switch', ['newLocale' => 'zh-CN']) }}">zh-CN</a></li>
		</ul>
	</li>
	<li class="dropdown user user-menu" style="margin-right: 20px;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
			<span class="hidden-xs">{{ BaseModel::getAllCurrencies()[session()->get('admin.currency')]['code'] }}</span>
		</a>
		<ul class="dropdown-menu">
			@foreach(BaseModel::getAllCurrencies() as $currency)
				<li class="@if(session()->get('admin.currency') == array_get($currency, 'id')) active @endif">
					<a href="{{ route('admin.currency.switch', ['currency_id' => array_get($currency, 'id')]) }}">{{ array_get($currency, 'code') }}</a>
				</li>
			@endforeach
		</ul>
	</li>
	<li class="dropdown user user-menu" style="margin-right: 20px;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
			<img src="{{ $user->avatar_url_or_blank }}" class="user-image"/>
			<span class="hidden-xs">{{ $user->name }}</span>
		</a>
	</li>
	<li class="logout" style="margin-right: 20px;">
		<a class="btn btn-danger" id="admin-logout" href="{{ route('adminLogout') }}">
			<i class="fa fa-btn fa-sign-out"></i> @lang('sleeping_owl::lang.auth.logout')
		</a>
	</li>
@endif