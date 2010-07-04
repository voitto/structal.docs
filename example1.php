<?php

// Structal Example #1
// Authenticate to Google Buzz, Facebook and Twitter

// Structal routes, helpers, config
require 'structal/helper.php';
require 'structal/mapper.php';
require 'structal/route.php';
require 'structal/config.php';

// make a "request object" to map URLs
$request = new Mapper();

// make a route to the oauth_login action
$request->connect('oauth_login');

// oauth_login (Twitter) action
function oauth_login( &$vars ){
	require 'structal/twitter.php';
	require 'structal/OAuth.php';
	extract($vars);
	$t = new Twitter( TW_KEY, TW_SEC );
	if (!isset($request->oauth_token)) {
		$token = $t->request_token();
		$_SESSION['token_secret'] = $token->secret;
		redirect_to( $token->authorize_url() );
	}
  list($atoken,$asecret) = $t->authorize_from_request($request->oauth_token,$_SESSION['token_secret']);
  if (empty($atoken) || empty($asecret))
    trigger_error('error: could not get token or secret from Twitter',E_USER_ERROR);
  $_SESSION['twit_token'] = $atoken;
  $_SESSION['twit_secret'] = $asecret;
  redirect_to($request->base);
}

// make a route to the facebook_login action
$request->connect('facebook_login');

// facebook_login (Facebook) action
function facebook_login( &$vars ){
	require 'structal/facebook.php';
	add_include_path('structal');
  require 'structal/Services/Facebook.php';
	extract($vars);
	$f = new Facebook( FB_KEY, FB_SEC, FB_AID, FB_NAM, false, url('facebook_login') );
	if (!isset($request->auth_token)) {
		$token = $f->request_token();
		redirect_to( $token->authorize_url() );
	}
 	list($userid,$sesskey) = $f->authorize_from_access();
  if (empty($userid) || empty($sesskey))
    trigger_error('error: could not get session or userid from Facebook',E_USER_ERROR);
	$_SESSION['face_userid'] = $userid;
	$_SESSION['face_session'] = $sesskey;
  redirect_to($request->base);
}

// make a route to the authsub action
$request->connect('authsub');

// authsub (Google) action
function authsub( &$vars ){
	require 'structal/buzz.php';
	require 'structal/OAuth.php';
	extract($vars);
	$b = new Buzz( GG_KEY, GG_SEC, url('authsub') );
	if (!isset($request->oauth_token)) {
		$token = $b->request_token();
		$_SESSION['token_secret'] = $token->secret;
		redirect_to( $token->authorize_url() );
	}
  list($atoken,$asecret) = $b->authorize_from_request($request->oauth_token,$_SESSION['token_secret'],$request->oauth_verifier);
  if (empty($atoken) || empty($asecret))
    trigger_error('error: could not get token or secret from Google Buzz',E_USER_ERROR);
  $_SESSION['goog_token'] = $atoken;
  $_SESSION['goog_secret'] = $asecret;
  redirect_to($request->base);
}

// make a route to the index action
$request->connect('',array('action'=>'index'));

// index action. shows _index.html inside index.html and sets a named var: $msg
function _index() {
  $msg = 'Welcome, friend.';
  return render(array( &$msg ),get_defined_vars());
}

// execute action and/or render template
require 'structal/view.php';
$response = new View();
$response->render( $request );




