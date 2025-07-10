{{-- Selalu tampilkan konten dokumentasi --}}
<div id="kontenView" class="ck-content" >
    {!! $contentDocs->docsContent->content ?? "Konten Belum Tersedia" !!}
</div>

{{-- Tampilkan editor hanya jika user terautentikasi DAN memiliki role 'admin' --}}
@auth
    @if(Auth::user()->role === 'admin')
        <div class="menuid">
        </div>
        <div class="main-container">
            <div class="editor-container hidden" id="editor-container">
                <form action="{{ route('docs.save', ['menu_id' => $menu_id]) }}" method="POST">
                    @csrf
                    <textarea name="content" id="editor" class="ckeditor">
                        {{ $contentDocs->docsContent->content ?? "Konten Belum Tersedia" }}
                    </textarea>
                    <div class="buttons">
                        <button type="submit" class="btn btn-simpan">Update</button>
                        <a href="{{ route('docs', ['category' => $currentCategory, 'page' => $currentPage]) }}" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth