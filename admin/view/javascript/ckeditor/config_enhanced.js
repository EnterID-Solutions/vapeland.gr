
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	
	config.extraPlugins = 'lineutils,widget,youtube,oembed,autocorrect,fontawesome,leaflet,qrc,fakeobjects,slideshow,widgetbootstrap,widgettemplatemenu,ckeditor-gwf-plugin,codemirror,image2';

	originpath = location.href.split( '/index' );
	config.contentsCss = originpath[0] + '/view/javascript/ckeditor/plugins/fontawesome/font-awesome/css/font-awesome-ck.min.css';
	
	config.allowedContent = true; 
	//console.log('config ' + config.contentsCss);

	config.toolbar = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
		'/',
		{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'others', items: [ '-' ] },
		{ name: 'extra', items: [ 'WidgetTemplateMenu','Youtube', 'oembed', 'leaflet','Slideshow','FontAwesome','qrc' ] },
		{ name: 'about', items: [ 'About' ] }
	];

	// Toolbar groups configuration.
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'forms' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'links' },
		{ name: 'insert' },
		'/',
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'tools' },
		{ name: 'others' },
		{ name: 'extra' },
		{ name: 'about' }
	];

	config.font_names = 'GoogleWebFonts;' + config.font_names;
	
	// ALLOW <i></i>
	config.protectedSource.push(/<i[^>]*><\/i>/g);

	// extra entities
	config.forceSimpleAmpersand = true;
	config.basicEntities = false;
	config.entities_additional = '#1049';
	config.entities_greek = false;
	config.entities_latin = false;

	config.font_defaultLabel = 'Arial';

	CKEDITOR.addCss(".cke_editable{font-family: Arial,Helvetica,sans-serif;}");

	// YOUR GOOGLE API KEY
	config.leaflet_maps_google_api_key = 'AIzaSyA9ySM6msnGm0qQB1L1cLTMBdKEUKPySmQ';

	};

