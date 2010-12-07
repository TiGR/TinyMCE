$().ready(function() {
    $("#Form_Comment #Form_Body, #DiscussionForm #Form_Body").livequery(function() {
        var CommentForm = $(this).parents("div.CommentForm");
        u = gdn.definition('tinymcePath');
        tinymce.PluginManager.urls['inlinepopups'] = u.substring(0, u.lastIndexOf('/')) +
            '/plugins/inlinepopups/';
        editor = jQuery(this).tinymce({
            script_url: gdn.definition('tinymcePath'),
            plugins:    gdn.definition('tinymcePlugins'),
            language:   gdn.definition('tinymceLang'),

            theme:                              "advanced",
            theme_advanced_buttons1:            gdn.definition('tinymceButtons1'),
            theme_advanced_buttons2:            false,
            theme_advanced_buttons3:            false,
            theme_advanced_toolbar_location:    "top",
            theme_advanced_toolbar_align:       "left",
            theme_advanced_statusbar_location:  "none",
            theme_advanced_resizing:            false,

            /*  As Vanilla preferes newlines to format the output, let's do
                everything to make formatting consistent */
            convert_newlines_to_brs:            true,
            apply_source_formatting:            false,
            remove_linebreaks:                  true,
            remove_redundant_brs:               true,
            inline_styles:                      false, // Vanilla strips all styles
            formats:                            {
                bold:          { inline: 'b'                },
                italic:        { inline: 'i'                },
                underline:     { inline: 'u',   exact:true  },
                strikethrough: { inline: 'del', exact: true },
            },

            // Enable auto-save
            onchange_callback: function(inst) {
                tinyMCE.triggerSave();
                jQuery("#Form_Body").keydown();
            }
        });
        jQuery(CommentForm).bind("clearCommentForm", {editor: editor}, function(e) {
            CommentForm.find("textarea").hide();
            e.data.editor.clearFields();
        });

        // This event is not yet implemented in Vanilla
        jQuery(CommentForm).bind("resetCommentForm", {editor: editor}, function(e) {
            CommentForm.find("textarea").hide();
        });

        jQuery(CommentForm).bind("PreviewLoaded", {editor: editor}, function(e) {
            CommentForm.find(".mceEditor").hide();
        });

        jQuery(CommentForm).bind("WriteButtonClick", {editor: editor}, function(e) {
            CommentForm.find(".mceEditor").show();

            // below code is utterly wrong, but currently it's the only way to 
            // do what we want.
            window.setTimeout(function() {
                $("textarea#Form_Body").hide();
            }, 100);
        });
    });
});
