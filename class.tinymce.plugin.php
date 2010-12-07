<?php if(!defined('APPLICATION')) die();

// Define the plugin:
$PluginInfo['TinyMCE'] = array(
   'Name' => 'TinyMCE Visual Editor',
   'Description' => 'Adds TinyMCE visual (WYSIWYG) editor to comment forms on discussion pages.',
   'Version' => '0.1',
   'RequiredApplications' => array('Vanilla' => '>=2'),
   'MobileFriendly' => FALSE,
   'HasLocale' => FALSE,
   'Author' => "Igor Tarasov",
   'AuthorEmail' => 'tarasov.igor@gmail.com',
   'AuthorUrl' => 'http://www.polosatus.ru',
);

class VanillaTinymce extends Gdn_Plugin {

    protected $plugins = array(
        'inlinepopups'  => 1,
        'contextmenu'   => 1,
        'noneditable'   => 0,
        'autoresize'    => 1
    );

    // Inject TinyMCE
    public function DiscussionController_Render_Before(&$Sender) {
        $this->_injectTinyMCE($Sender); 
    }

    public function PostController_Render_Before(&$Sender) {
        $this->_injectTinyMCE($Sender);
    }

    // Clean extra newlines
    public function CommentModel_BeforeSaveComment_Handler(&$Sender) {
        $this->_cleanPostedData($Sender->EventArguments['FormPostValues']['Body']);
    }

    public function DiscussionModel_BeforeSaveDiscussion_Handler(&$Sender) {
        $this->_cleanPostedData($Sender->EventArguments['FormPostValues']['Body']);
    }

    public function Setup(){}

    protected function _injectTinyMCE(&$Sender) {
        $suffix = !Gdn::PluginManager()->CheckPlugin('Minify') ? "_src" : "";
        $enabledPlugins = array();
        $lang = Gdn::Locale()->Current() 
            ? substr(Gdn::Locale()->Current(), 0, 2)
            : "en";
        $Sender->AddJSFile("plugins/TinyMCE/js/tiny_mce_jquery$suffix.js");
        $Sender->AddJSFile("plugins/TinyMCE/js/langs/$lang.js");
        $Sender->AddJSFile("plugins/TinyMCE/js/themes/advanced/editor_template$suffix.js");
        $Sender->AddJSFile("plugins/TinyMCE/js/themes/advanced/langs/$lang.js");
        foreach ($this->plugins as $plugin => $enabled) {
            if ($enabled) {
                $Sender->AddJSFile("plugins/TinyMCE/js/plugins/$plugin/editor_plugin$suffix.js");
                $enabledPlugins[] = "-" . $plugin;
            }
        }
        $Sender->AddJSFile("plugins/TinyMCE/js/jquery.tinymce.js");
        $Sender->AddJSFile("plugins/TinyMCE/js/injectTinyMCE.js");

        // add some options for initalization script
        $Sender->AddDefinition('tinymcePath', $this->GetWebResource('js/tiny_mce_jquery.js'));
        $Sender->AddDefinition('tinymcePlugins', implode(',', $enabledPlugins));
        $Sender->AddDefinition('tinymceLang', $lang);
        $Sender->AddDefinition('tinymceButtons1', "bold,italic,underline,strikethrough,|,link,unlink,image,|,undo,redo,|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,outdent,indent,blockquote,|,sub,sup,|,removeformat");
    }

    /**
     * Removes newlines after such blocks as <p>, <blockquote>, <ol>, etc.
     *   This is required since Vanilla converts newlines into <br />, thus 
     *   adding extra line space after every element in the post.
     * Also, this function removes <p> at the very start of the post and </p> at
     *   the very end of it.
     *   This is required since Vanilla wraps commnts in <p> tag. So, comments 
     *   look like this: <p><p>Comment text</p></p>.
     *
     * @param string $body text body of a comment or a discussion.
     *
     */
    protected function _cleanPostedData(&$body) {
        $body = preg_replace("~</(p|blockquote|pre|li|ul|ol)>\r?\n~i", "</\$1>", $body);
        if (substr($body, 0, 3) == '<p>') {
            $body = substr($body, 3);
        }
        if (substr($body, -4) == '</p>') {
            $body = substr($body, 0, -4);
        }
    }
}