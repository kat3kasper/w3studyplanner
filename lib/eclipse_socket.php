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
require_once('eclipse_exdr_streams.php');

class SocketErrorException extends Exception {
	public function __construct($errno, $errstr){
		$msg = "Error has occurred opening socket: [$errno] $errstr.";
		parent::__construct($msg);
	}
}

/**
 * Provides an high level interface to a running logic server commuicating
 * over TCP for sending goals to execute and receiving back a ground instance.
 */
class EclipseSocket {
	private $host, $port, $timeout;
	private $socket;
	private $logger;
	private $exdrStream;

	public function __construct($host, $port, $timeout=10){
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	public function __destruct(){
		$this->disconnect();
	}

	public function setLogger(Log $logger){
		$this->logger = $logger;
	}

	public function getLogger(){
		return $this->logger;
	}

	public function getEXDRParser($string){
		if($this->exdrParser == null)
			$this->exdrParser = new EXDRParser($string);
		else $this->exdrParser->reset($string);
		return $this->exdrParser;
	}

	public function isConnected(){
		return $this->socket != null;
	}

	public function connect(){
		if(!$this->isConnected()){
			$this->socket = stream_socket_client("tcp://$this->host:$this->port", $errno, $errstr, $this->timeout);
			if(!$this->socket)
				throw new SocketErrorException($errno, $errstr);
			stream_set_blocking($this->socket, 1);
			
			////$this->log("OPEN $this->host:$this->port with timeout $this->timeout secs.", PEAR_LOG_INFO);
			$this->exdrStream = new EXDRSocketStream($this->socket, $this->getLogger());
		}
	}

	public function disconnect(){
		if($this->isConnected())
			$this->closeSocket();
	}

	private function closeSocket(){
		if($this->socket != null){
			fclose($this->socket);
			$this->socket = null;
			////$this->log("CLOSE", PEAR_LOG_INFO);
		}
	}

	private function log($msg, $level){
		if($this->logger != null)
      return;
			//$this->logger->log($msg, $level);
	}

	private function sendEXDR(EXDR_EXDRTerm $t){
		$written = stream_socket_sendto($this->socket, $t);
		if($written === FALSE)
			throw new Exception("error occurred writing to control socket");
		////$this->log("SEND($written) $t", PEAR_LOG_DEBUG);
	}

	private function recvEXDR(){
		$parser = new EXDRParser($this->exdrStream);
		$term = $parser->parseEXDRTerm();
		return $term;
	}

	public function rpcGoal(EXDR_EXDRTerm $t){
		if(!$this->isConnected()) $this->connect();
		$this->sendEXDR($t);
		return $this->recvEXDR();
	}
}
?>
