/*
Template Name: Vuesy - Admin & Dashboard Template
Author: Themesdesign
Website: https://Themesdesign.in/
Contact: themesdesign.in@gmail.com
File: Form editor Js File
*/

// ckeditor

ClassicEditor
.create( document.querySelector( '#ckeditor-classic' ) )
.then( function(editor) {
    editor.ui.view.editable.element.style.height = '200px';
} )
.catch( function(error) {
    console.error( error );
} );


// Quilljs

    var quill = new Quill('#snow-editor', {
        theme: 'snow',
    });

    var quillBubble = new Quill('#bubble-editor', {
        theme: 'bubble'
    });
