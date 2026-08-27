import {
    Autoformat,
    BlockQuote,
    Bold,
    ClassicEditor,
    CloudServices,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Italic,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    PictureEditing,
    Table,
    TableToolbar,
    TextTransformation,
} from 'ckeditor5'
import translations from 'ckeditor5/translations/fa.js'

import 'ckeditor5/ckeditor5.css'

class MetaverseClassicEditor extends ClassicEditor {}

MetaverseClassicEditor.builtinPlugins = [
    Essentials,
    Autoformat,
    Bold,
    Italic,
    BlockQuote,
    CloudServices,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    PictureEditing,
    Table,
    TableToolbar,
    TextTransformation,
]

MetaverseClassicEditor.defaultConfig = {
    // ckeditor5@47+ on npm is LTS-only and rejects GPL (forces read-only).
    // Stay on the dual-licensed OSS line (46.x) and keep the GPL key.
    licenseKey: 'GPL',
    toolbar: {
        items: [
            'undo',
            'redo',
            '|',
            'heading',
            '|',
            'bold',
            'italic',
            '|',
            'link',
            'uploadImage',
            'insertTable',
            'blockQuote',
            'mediaEmbed',
            '|',
            'bulletedList',
            'numberedList',
            'outdent',
            'indent',
        ],
    },
    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
        ],
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
    language: 'fa',
    translations: [translations],
}

export default MetaverseClassicEditor
