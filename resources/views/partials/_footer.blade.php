<footer>
	@if(auth()->check())
        <!-- Logout form -->
        <form id="logout-form" action="{{ url()->current().'/logout' }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    @endif
</footer>