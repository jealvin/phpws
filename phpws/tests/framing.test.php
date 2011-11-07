<?php
require_once("../websocket.server.php");
require_once('simpletest/autorun.php');

class test extends UnitTestCase {
	function test_unmaskedTextMessage(){
		
		$bin = "\x81\x05\x48\x65\x6c\x6c\x6f";
		
		$str = "Hello";
		$f = WebSocketFrame::create(WebSocketOpcode::TextFrame, $str);
		
		$enc = $f->encode();
		
		$this->assertEqual($bin, $enc);
	}
	
	function test_maskedTextMessage(){
		$bin = "\x81\x85\x37\xfa\x21\x3d\x7f\x9f\x4d\x51\x58";
		$str = "Hello";
		
		$f = WebSocketFrame::decode($bin);
		
		$this->assertEqual($str, $f->getData());
	}
	
	function test_incompleteTextMessage(){
		$bf1 = "\x01\x03\x48\x65\x6c";
		$bf2 = "\x80\x02\x6c\x6f";
		
		$f1 = WebSocketFrame::decode($bf1);
		$f2 = WebSocketFrame::decode($bf2);
		
		$msg = WebSocketMessage::fromFrame($f1);
		
		$this->assertFalse($msg->isFinalised());
		
		// Should throw exception!
		$this->expectException(new WebSocketMessageNotFinalised($msg));
		$msg->getData();
	}
	
	function test_fragmentedTextMessage(){
		$bf1 = "\x01\x03\x48\x65\x6c";
		$bf2 = "\x80\x02\x6c\x6f";
		
		$f1 = WebSocketFrame::decode($bf1);
		$f2 = WebSocketFrame::decode($bf2);
		
		$this->assertEqual("Hel", $f1->getData());
		$this->assertEqual("lo", $f2->getData());
		
		$msg = WebSocketMessage::fromFrame($f1);		
		$msg->takeFrame($f2);
		
		$this->assertEqual("Hello", $msg->getData());
	}
}

