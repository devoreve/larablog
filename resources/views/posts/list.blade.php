@foreach($posts as $post)
    <article>
        <header>
            <h3><a href="{{ route('posts.show', ['slug' => $post->slug]) }}">{{ $post->title }}</a></h3>
            <small>Rédigé par {{ $post->user->name }} le {{ $post->created_at->format('d/m/Y H:i') }}</small>
        </header>
        {!! nl2br(e($post->content)) !!}
    </article>
@endforeach