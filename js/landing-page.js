var $ = ijQuery;
var jQuery = ijQuery;
window.__page_id = 9375587;
window.__version = 3;
window.__variant = 'A';
window.__variant_custom_name = '' || 'A';
window.__is_tablet = false;
window.__page_domain = '//drafts.clairegradysmith.com';
window.__instapage_services = '//app.instapage.com';
window.__instapage_proxy_services = 'PROXY_SERVICES';
window.__preview = false;
window.__facebook = false;
window.__page_type = 2;
window.__mobile_version = false;
window.__variant_hash = "b71dd682d84e79ea60bdc73c7b373eaa7d821bfe";
window.__predator_throttle = 10;
window.__predator_blacklist = [];


var page_version = 3;

var _Translate = new Translate();

if( ijQuery === 'undefined' )
{
	var ijQuery = jQuery;
}

window.__recaptchaError = function()
{
	console.error( 'ReCaptcha invalid site key' );
	window.__reCaptchaCorrupted = true;
};

window.__reCaptchaTrigger = function( token )
{
	_form_controller.onRecaptchaFormSubmit( token );
};

ijQuery(document).ready(function()
{
	window._Mobile_helper = new MobileHelper();
	window._Mobile_helper.initViewport( 960, true );

	try
	{
		ijQuery('input, textarea').placeholder();
	}
	catch( e )
	{
	}
});

ijQuery( window ).load( function()
{
	var notification_loader;

					ijQuery( 'body' ).hide().show();

					notification_loader = ijQuery( '.notification-loader' );
	notification_loader.attr( 'src', notification_loader.attr( 'rel' ) );
});

_Translate.set( "Problem loading google map", "Problem loading google map" );

is_new_mobile_visible = function()
{
	if( !window.matchMedia )
	{
		return false;
	}
	return window.matchMedia('screen and (max-width: 620px), screen and (max-width: 999px) and (-webkit-min-device-pixel-ratio: 1.5)').matches;
}

setTimeout(function()
{
	"use strict";
	try
	{
		var body = document.body;
		var html = document.documentElement;
		var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

		html.style.setProperty( 'height', height + 'px' );
	}
	catch(e)
	{
	}
}, 1 );