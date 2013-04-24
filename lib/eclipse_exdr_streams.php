<?php
/*-
 * Copyright (c) 2009
 * Andrea Montemaggio.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *		1. Redistributions of source code must retain the above copyright
 *			notice, this list of conditions and the following disclaimer.
 *		2. Redistributions in binary form must reproduce the above copyright
 *			notice, this list of conditions and the following disclaimer in the
 *			documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */

//require_once('Log.php');

/**
 * A simple (perhaps a too basic one) abstarction of an EXDR stream to provide
 * pluggable stream implementations.
 *
 * TODO standardize the interface along the lines of native PHP stream support
 */
interface EXDRStream {
	public function peekNextByte();
	public function getNextByte();
	public function getNextBytes($n);
	public function getPosition();
	public function getBuffer();
	public function refreshBuffer();
}

class EXDRSocketStream implements EXDRStream {
	const BUFFER_SIZE = 2048;

	private $buffer, $p, $absp;
	private $stream;
	private $logger;
	private $sockType;

	public function __construct($stream, $logger=null, $sockType=''){
		$this->stream = $stream;
		$this->p = $this->absp = 0;
		//$this->logger = $logger;
		$this->sockType = $sockType;
	}

	private function log($msg, $level){
		if($this->logger != null)
      return;
			//$this->logger->log($msg, $level);
	}

	public function peekNextByte(){
		$c = $this->getNextByte();
		if($this->p > 0) $this->p--;
		if($this->absp > 0) $this->absp--;
		return $c;
	}

	public function getNextByte(){
		if($this->p >= strlen($this->buffer)){
			$read = fread($this->stream, self::BUFFER_SIZE);			
			if($read === false){
				$msg = "fread() error.\nBuffer dump (".strlen($this->buffer)."):\n".$this->buffer;
				//$this->log($msg, PEAR_LOG_DEBUG);
				throw new Exception($msg);
			}
			$this->buffer = $read;
			$this->p=0;
			////$this->log("[".$this->sockType."_RECV(".strlen($read).")] $read", PEAR_LOG_DEBUG);
		}

		$this->absp++;
		return $this->buffer[$this->p++];
	}

	public function getNextBytes($n){
		$out = "";
		for($i=0; $i<$n; $i++)
			$out .= $this->getNextByte();
		return $out;
	}

	public function getPosition(){ return $this->absp; }

	public function getBuffer() {
		return $this->buffer;
	}

	public function refreshBuffer() {
		$read = stream_socket_recvfrom($this->stream, self::BUFFER_SIZE);
		if($read===FALSE) throw new Exception("fread error");
		$this->buffer = $read;
		$this->p=0;
		//$this->log("[".$this->sockType."_RECV(".strlen($read).")] $read", PEAR_LOG_DEBUG);
		return $this->buffer;
	}
}

class EXDRFileStream implements EXDRStream {
	const BUFFER_SIZE = 50000;

	private $buffer, $p, $absp;
	private $stream;
	private $logger;
	private $sockType;

	public function __construct($file, $logger=null, $sockType=''){
		$this->stream = fopen($file, 'r');
		$this->p = $this->absp = 0;
		//$this->logger = $logger;
		$this->sockType = $sockType;
	}

	private function log($msg, $level){
		if($this->logger != null)
      return;
			//$this->logger->log($msg, $level);
	}

	public function peekNextByte(){
		$c = $this->getNextByte();
		if($this->p > 0) $this->p--;
		if($this->absp > 0) $this->absp--;
		return $c;
	}

	public function getNextByte(){
		if($this->p >= strlen($this->buffer)){
			$read = fread($this->stream, self::BUFFER_SIZE);
			if($read===FALSE) throw new Exception("fread error");
			$this->buffer = $read;
			$this->p=0;
			//$this->log("[".$this->sockType."_RECV(".strlen($read).")] $read", PEAR_LOG_DEBUG);
		}

		$this->absp++;
		return $this->buffer[$this->p++];
	}

	public function getNextBytes($n){
		for($i=0; $i<$n; $i++)
			$out .= $this->getNextByte();
		return $out;
	}

	public function getPosition(){ return $this->absp; }

	public function getBuffer() {
		return $this->buffer;
	}

	public function refreshBuffer() {
		$read = fread($this->stream, self::BUFFER_SIZE);
		if($read===FALSE) throw new Exception("fread error");
		$this->buffer = $read;
		$this->p=0;
		//$this->log("[".$this->sockType."_RECV(".strlen($read).")] $read", PEAR_LOG_DEBUG);
		return $this->buffer;
	}
}

class EXDRStringStream implements EXDRStream {
	private $str, $i;

	public function __construct($str){
		$this->str = $str;
		$this->i = 0;
	}

	public function peekNextByte(){
		return $this->str[$this->i];
	}

	public function getNextByte(){
		return $this->str[$this->i++];
	}

	public function getNextBytes($n){
		$out = substr($this->str, $this->i, $n);
		$this->i += $n;
		return $out;
	}

	public function getPosition(){ return $this->i; }

	public function getBuffer() {
		return $this->str;
	}

	public function refreshBuffer() {
		return $this->str;
	}
}
?>
