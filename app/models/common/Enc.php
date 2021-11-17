<?php

/**
 * 
 */
class Enc 
{

		function safeuseren($code){

		return base64_encode(openssl_encrypt($code, 'AES-256-CBC', 'btfo',0,'safeapifreonuser'));
		}

		function safeuserde($code){
			$code=base64_decode($code);
			return openssl_decrypt($code, 'AES-256-CBC', 'btfo',0,'safeapifreonuser');
		}

		function enc_mob($mymob){
		return base64_encode(openssl_encrypt($mymob, 'AES-256-CBC', 'mobo',0,'safesigealogisti'));
		}

		function dec_mob($code){
			$code=base64_decode($code);
		return openssl_decrypt($code, 'AES-256-CBC', 'mobo',0,'safesigealogisti');
		}

		function enc_mail($mymob){
		return base64_encode(openssl_encrypt($mymob, 'AES-256-CBC', 'mail',0,'safesigealogisti'));
		}

		function dec_mail($code){
			$code=base64_decode($code);
		return openssl_decrypt($code, 'AES-256-CBC', 'mail',0,'safesigealogisti');
		}

		function safeurlen($codea){
		$i='securvar';
		return base64_encode(openssl_encrypt($codea, 'BF-CBC', 'encurl',0,$i));

		}
		function safeurlde($codea){
		$codea=base64_decode($codea);
		$i='securvar';
		return openssl_decrypt($codea, 'BF-CBC', 'encurl',0,$i);
		}
/*
		function enc_mob_conts($mymob){
		return openssl_encrypt($mymob, 'AES-256-CBC', 'mobo',0,'jobsmobirainsafe');
		}


		function dec_mob_conts($code){
		  return openssl_decrypt($code, 'AES-256-CBC', 'mobo',0,'jobsmobirainsafe');
		}

		function enc_mail_conts($mymob){
		  return openssl_encrypt($mymob, 'AES-256-CBC', 'mail',0,'jobsmailrainsafe');
		}


		function dec_mail_conts($code){
		  return openssl_decrypt($code, 'AES-256-CBC', 'mail',0,'jobsmailrainsafe');
		}


			function safeurlen($codea){
			$i='securvar';
			return base64_encode(openssl_encrypt($codea, 'BF-CBC', 'encurl',0,$i));

			}
			function safeurlde($codea){
			$codea=base64_decode($codea);
			$i='securvar';
			return openssl_decrypt($codea, 'BF-CBC', 'encurl',0,$i);
			}*/



}

?>