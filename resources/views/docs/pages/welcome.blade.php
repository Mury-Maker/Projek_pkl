{{-- resources/views/docs/pages/welcome.blade.php --}}
<div id="kontenView" class="ck-content">
    {!! $contentDocs->docsContent->content !!}
</div>

{{-- The editor part will be handled by the @auth and @if(Auth::user()->role === 'admin') --}}
@auth
    @if(Auth::user()->role === 'admin')
        {{-- You might remove the editor for the welcome page, or provide a way to edit this specific "no content" message --}}
        {{-- For now, it will just show the content, as this is a fallback page --}}
        {{-- If you need to edit this specific welcome message, you'd need a dedicated menu item for it. --}}
        {{-- The current setup only enables editing for actual menu items that have a menu_id. --}}
    @endif
@endauth