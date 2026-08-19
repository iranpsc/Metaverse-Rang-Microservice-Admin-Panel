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

import 'ckeditor5/ckeditor5.css'
import 'ckeditor5/build/translations/fa.js'

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
}

export default MetaverseClassicEditor
