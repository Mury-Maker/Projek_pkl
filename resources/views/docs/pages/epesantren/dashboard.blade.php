@guest
<div class="ck-content">
    {!! $contentDocs->docsContent->content ?? "Konten Belum Tersedia" !!}
</div>
@endguest

@auth
<div class="menuid">
    </div>
    <div class="main-container">
        <div class="editor-container" id="editor-container">
            <form action="{{ route('docs.save', ['menu_id' => $menu_id]) }}" method="POST">
                @csrf
                <textarea name="content" id="editor" class="ckeditor">
                    {{ $contentDocs->docsContent->content ?? "Konten Belum Tersedia" }}
                </textarea>
                <div class="buttons">
                    <button type="submit" class="btn btn-simpan">Update</button>
                    <a href="{{ route('docs', ['category' => $currentCategory, 'page' => $currentPage]) }}" class="btn btn-batal">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endauth