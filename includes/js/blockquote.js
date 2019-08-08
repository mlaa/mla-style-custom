(function($) {
    tinymce.PluginManager.add('blockquote', function(editor, url) {

        var toggleBlockquoteFormat = function(alignment) {


            var $selectedElement = editor.selection.getContent();
            editor.selection.setContent('<blockquote class="extract">' + $selectedElement + '</blockquote>');

            editor.nodeChanged(); // refresh the button state

        };

        editor.addButton('NestedExtract', {
            text: 'Nested Extract',
            icon: false,
            onclick: function() {
                toggleBlockquoteFormat('center');
            }
        });

    });

})(jQuery);
