/**
 * This configuration was generated using the CKEditor 5 Builder. You can modify it anytime using this link:
 * https://ckeditor.com/ckeditor-5/builder/?redirect=portal#installation/NoNgNARATAdCNQpAjAdgCxRKgHDgDMiMgMz7rLqoggCcU1Gt+GArERTqqrSVPkggBTAHZJ8YYMjASJ0+QF1IQkqwDGNVhAVA
 */

const {
    ClassicEditor,
    Alignment,
    Autoformat,
    AutoImage,
    Autosave,
    BlockQuote,
    Bold,
    Code,
    CodeBlock,
    Emoji,
    Essentials,
    FindAndReplace,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    Fullscreen,
    GeneralHtmlSupport,
    Heading,
    Highlight,
    HorizontalLine,
    ImageBlock,
    ImageCaption,
    ImageInline,
    ImageInsert,
    ImageInsertViaUrl,
    ImageResize,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    ListProperties,
    MediaEmbed,
    Mention,
    Paragraph,
    PasteFromOffice,
    RemoveFormat,
    SimpleUploadAdapter,
    SpecialCharacters,
    SpecialCharactersArrows,
    SpecialCharactersCurrency,
    SpecialCharactersEssentials,
    SpecialCharactersLatin,
    SpecialCharactersMathematical,
    SpecialCharactersText,
    Strikethrough,
    Style,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    TextTransformation,
    TodoList,
    Underline
} = window.CKEDITOR; // Assuming CKEditor 5 UMD build exposes these.

const LICENSE_KEY =
    'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3ODA4NzY3OTksImp0aSI6IjUzNDRhZTBhLWM1NmYtNGE0Ny05MjYzLWY0ZDA3YTM0MmI5OCIsImxpY2Vuc2VkSG9zdHMiOlsiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJ1c2FnZUVuZHBvaW50IjoiaHR0cHM6Ly9wcm94eS1ldmVudC5ja2VkaXRvci5jb20iLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIl0sImxpY2Vuc2VUeXBlIjoiZGV2ZWxvcG1lbnQiLCJmZWF0dXJlcyI6WyJEUlVQIiwiQk9YIiwiRTJQIiwiRTJXIl0sInZjIjoiYWM0MWY3NjEifQ.SQ5J2RpFjAbb_YSPPL1qeLdzBENzWVRsfwj2-WtALgy171gL1hlayoOsdWPFs2d-uEYuL__Ng67oiz5y1Bx64w';

// Define the editor configuration. Export it to the global scope
// so it can be accessed by the Blade file.
window.CKEDITOR_CONFIG = { // Changed from `editorConfig` to `window.CKEDITOR_CONFIG`
    toolbar: {
        items: [
            'undo',
            'redo',
            '|',
            'findAndReplace',
            'fullscreen',
            '|',
            'heading',
            'style',
            '|',
            'fontSize',
            'fontFamily',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            'code',
            'removeFormat',
            '|',
            'emoji',
            'specialCharacters',
            'horizontalLine',
            'link',
            'insertImage',
            'insertImageViaUrl',
            'mediaEmbed',
            'insertTable',
            'highlight',
            'blockQuote',
            'codeBlock',
            '|',
            'alignment',
            '|',
            'bulletedList',
            'numberedList',
            'todoList',
            'outdent',
            'indent'
        ],
        shouldNotGroupWhenFull: false
    },
    plugins: [
        Alignment,
        Autoformat,
        AutoImage,
        Autosave,
        BlockQuote,
        Bold,
        Code,
        CodeBlock,
        Emoji,
        Essentials,
        FindAndReplace,
        FontBackgroundColor,
        FontColor,
        FontFamily,
        FontSize,
        Fullscreen,
        GeneralHtmlSupport,
        Heading,
        Highlight,
        HorizontalLine,
        ImageBlock,
        ImageCaption,
        ImageInline,
        ImageInsert,
        ImageInsertViaUrl,
        ImageResize,
        ImageStyle,
        ImageTextAlternative,
        ImageToolbar,
        ImageUpload, // This plugin uses SimpleUploadAdapter internally
        Indent,
        IndentBlock,
        Italic,
        Link,
        LinkImage,
        List,
        ListProperties,
        MediaEmbed,
        Mention,
        Paragraph,
        PasteFromOffice,
        RemoveFormat,
        // SimpleUploadAdapter is configured via `config.simpleUpload` or `config.ckfinder`
        SpecialCharacters,
        SpecialCharactersArrows,
        SpecialCharactersCurrency,
        SpecialCharactersEssentials,
        SpecialCharactersLatin,
        SpecialCharactersMathematical,
        SpecialCharactersText,
        Strikethrough,
        Style,
        Subscript,
        Superscript,
        Table,
        TableCaption,
        TableCellProperties,
        TableColumnResize,
        TableProperties,
        TableToolbar,
        TextTransformation,
        TodoList,
        Underline
    ],
    fontFamily: {
        supportAllValues: true
    },
    fontSize: {
        options: [10, 12, 14, 'default', 18, 20, 22],
        supportAllValues: true
    },
    fullscreen: {
        onEnterCallback: container =>
            container.classList.add(
                'editor-container',
                'editor-container_classic-editor',
                'editor-container_include-style',
                'editor-container_include-fullscreen',
                'main-container'
            )
    },
    heading: {
        options: [
            {
                model: 'paragraph',
                title: 'Paragraph',
                class: 'ck-heading_paragraph'
            },
            {
                model: 'heading1',
                view: 'h1',
                title: 'Heading 1',
                class: 'ck-heading_heading1'
            },
            {
                model: 'heading2',
                view: 'h2',
                title: 'Heading 2',
                class: 'ck-heading_heading2'
            },
            {
                model: 'heading3',
                view: 'h3',
                title: 'Heading 3',
                class: 'ck-heading_heading3'
            },
            {
                model: 'heading4',
                view: 'h4',
                title: 'Heading 4',
                class: 'ck-heading_heading4'
            },
            {
                model: 'heading5',
                view: 'h5',
                title: 'Heading 5',
                class: 'ck-heading_heading5'
            },
            {
                model: 'heading6',
                view: 'h6',
                title: 'Heading 6',
                class: 'ck-heading_heading6'
            }
        ]
    },
    htmlSupport: {
        allow: [
            {
                name: /^.*$/,
                styles: true,
                attributes: true,
                classes: true
            }
        ]
    },
    image: {
        toolbar: [
            'toggleImageCaption',
            'imageTextAlternative',
            '|',
            'imageStyle:inline',
            'imageStyle:wrapText',
            'imageStyle:breakText',
            '|',
            'resizeImage'
        ]
    },
    licenseKey: LICENSE_KEY,
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
        decorators: {
            toggleDownloadable: {
                mode: 'manual',
                label: 'Downloadable',
                attributes: {
                    download: 'file'
                }
            }
        }
    },
    list: {
        properties: {
            styles: true,
            startIndex: true,
            reversed: true
        }
    },
    mention: {
        feeds: [
            {
                marker: '@',
                feed: [
                    /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
                ]
            }
        ]
    },
    menuBar: {
        isVisible: true
    },
    placeholder: 'Tulis Sesuatu...',
    style: {
        definitions: [
            {
                name: 'Article category',
                element: 'h3',
                classes: ['category']
            },
            {
                name: 'Title',
                element: 'h2',
                classes: ['document-title']
            },
            {
                name: 'Subtitle',
                element: 'h3',
                classes: ['document-subtitle']
            },
            {
                name: 'Info box',
                element: 'p',
                classes: ['info-box']
            },
            {
                name: 'CTA Link Primary',
                element: 'a',
                classes: ['button', 'button--green']
            },
            {
                name: 'CTA Link Secondary',
                element: 'a',
                classes: ['button', 'button--black']
            },
            {
                name: 'Marker',
                element: 'span',
                classes: ['marker']
            },
            {
                name: 'Spoiler',
                element: 'span',
                classes: ['spoiler']
            }
        ]
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
    },
    
    simpleUpload: {
        // The URL the image file is uploaded to.
        uploadUrl: '/upload?_token=' + document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        // Headers sent along with the XMLHttpRequest to the upload server.
        // For Laravel, the CSRF token is essential.
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    },
    
    mediaEmbed: {
        previewsInData: true
    },
};

// If you want to initialize an editor immediately, you'd do it here.
// For this application, the initialization is handled in `index.blade.php` based on user role.
// ClassicEditor.create(document.querySelector('#editor'), editorConfig);