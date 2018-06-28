<a href="{{ route('users.show', $user->id) }}">
    @if(empty($user->avatar))
        <img src="{{ $user->gravatar('140') }}" alt="{{ $user->name }}" class="gravatar"/>
    @else
        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="gravatar"/>
    @endif
</a>
<h1>{{ $user->name }}</h1>