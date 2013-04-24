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

class ProtocolException extends Exception {}

/**
 * This class attempts to implement a subset of the EXDR remote protocol,
 * but it is at a very early stage of development.
 */
class EclipseRemoteProtocol {
	const PROTOCOL_VERSION = 1;

	const PSTATE_DOWN = 1;
	const PSTATE_UP = 2;

	private $host, $port, $passTerm, $timeout;
	private $ctrlSocket, $rpcSocket;
	private $logger;
	private $peerName;
	private $exdrCtrlStream, $exdrRpcStream, $exdrParser;
	private $protocolState;

	public function __construct($host, $port, $passTerm='', $timeout=10){
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
		$this->protocolState = self::PSTATE_DOWN;

		if( is_string($passTerm) )
			$this->passTerm = new EXDR_EXDRTerm( new EXDR_String($passTerm) );
		elseif($passTerm instanceof EXDR_String)
			$this->passTerm = new EXDR_EXDRTerm($passTerm);
		elseif($passTerm instanceof EXDR_EXDRTerm)
			$this->passTerm = $passTerm;
		else
			throw new Exception("invalid passterm");
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
		return $this->ctrlSocket != null && $this->rpcSocket != null && $this->protocolState==self::PSTATE_UP;
	}

	public function connect(){
		if(!$this->isConnected()){
			$this->ctrlSocket = stream_socket_client("tcp://$this->host:$this->port", $errno, $errstr, $this->timeout);
			if(!$this->ctrlSocket)
				throw new SocketErrorException($errno, $errstr);

			$this->log("CTRL_OPEN $this->host:$this->port with timeout $this->timeout secs.", PEAR_LOG_INFO);

			$this->exdrCtrlStream = new EXDRSocketStream($this->ctrlSocket, $this->getLogger(), "CTRLSCK");

			$this->handshake();
			$this->protocolState = self::PSTATE_UP;
		}
	}

	private function rpcConnect(){
		if(!$this->isConnected()){
			$this->rpcSocket = stream_socket_client("tcp://$this->host:$this->port", $errno, $errstr, $this->timeout);
			if(!$this->rpcSocket)
				throw new SocketErrorException($errno, $errstr);
			$this->log("RPC_OPEN $this->host:$this->port with timeout $this->timeout secs.", PEAR_LOG_INFO);
			$this->exdrRpcStream = new EXDRSocketStream($this->rpcSocket, $this->getLogger(), "RPCSCK");
		}
	}

	public function disconnect(){
		if($this->isConnected()){
			$this->ctrlSendEXDR( $this->createDisconnectTerm() );

			$t = $this->ctrlRecvEXDR();
			if($t == $this->createDisconnectYieldTerm()){
				$this->closeSockets();
			}else{
				throw new ProtocolException("'disconnect_yield' message expected from the ECLiPSe side.");
			}

			$this->protocolState = self::PSTATE_DOWN;
		}
	}

	private function closeSockets(){
		if($this->ctrlSocket != null){
			fclose($this->ctrlSocket);
			$this->log("[CTRLSCK_CLOSE]", PEAR_LOG_INFO);
		}

		if($this->rpcSocket != null){
			fclose($this->rpcSocket);
			$this->log("[RPCSCK_CLOSE]", PEAR_LOG_INFO);
		}
	}

	private function log($msg, $level){
		if($this->logger != null)
			$this->logger->log($msg, $level);
	}

	private function createRemoteProtocolTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('remote_protocol', array(
			new EXDR_Integer( new EXDR_byte(self::PROTOCOL_VERSION & 0xFF) )
		)));
	}

	private function createLanguageTerm(){
		return new EXDR_EXDRTerm( new EXDR_String('PHP '.PHP_VERSION) );
	}

	private function createYesTerm(){
		return new EXDR_EXDRTerm( new EXDR_String('yes'));
	}

	private function createYieldTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('yield'));
	}

	private function createRpcTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('rpc'));
	}

	private function createFailTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('fail'));
	}

	private function createDisconnectTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('disconnect'));
	}

	private function createDisconnectYieldTerm(){
		return new EXDR_EXDRTerm( new EXDR_Struct('disconnect_yield'));
	}

	private function ctrlSendEXDR(EXDR_EXDRTerm $t){
		$written = stream_socket_sendto($this->ctrlSocket, $t);
		if($written === FALSE)
			throw new Exception("error occurred writing to control socket");
		$this->log("CTRL_SEND($written) $t", PEAR_LOG_DEBUG);
	}

	private function rpcSendEXDR(EXDR_EXDRTerm $t){
		$written = stream_socket_sendto($this->rpcSocket, $t);
		if($written === FALSE)
			throw new Exception("error occurred writing to rpc socket");
		$this->log("RPC_SEND($written) $t", PEAR_LOG_DEBUG);
	}

	private function ctrlRecvEXDR(){
		$parser = new EXDRParser($this->exdrCtrlStream);
		$term = $parser->parseEXDRTerm();
		return $term;
	}

	private function rpcRecvEXDR(){
		$parser = new EXDRParser($this->exdrRpcStream);
		$term = $parser->parseEXDRTerm();
		return $term;
	}

	private function handshake(){
		// step 3
		$this->ctrlSendEXDR($this->createRemoteProtocolTerm());

		// step 4
		$t = $this->ctrlRecvEXDR();
		if($t != $this->createYesTerm()) throw new ProtocolException();

		// step 6
		$this->ctrlSendEXDR($this->passTerm);

		// step 7
		$t = $this->ctrlRecvEXDR();
		$this->peerName = $t->getTerm()->getObject();

		// step 8
		$this->ctrlSendEXDR( $this->createLanguageTerm() );

		// step 10
		$this->rpcConnect();
		$t = $this->rpcRecvEXDR();
		if($this->peerName != $t->getTerm()->getObject())
			throw new ProtocolException("peerName received from rpc connection does not match the one received from control connection.");
	}

	private function waitYield(){
		$t = $this->ctrlRecvEXDR();
		if($t != $this->createYieldTerm())
			throw new ProtocolException("yield message expected from the ECLiPSe side.");
		return true;
	}

	/**
	 *	Issues a goal to the ECLiPSe side
	 * @param EXDR_EXDRTerm $goal
	 * @return EXDR_EXDRTerm
	 */
	public function rpcGoal(EXDR_EXDRTerm $goal){
		if(!$this->isConnected()) $this->connect();
		$this->ctrlSendEXDR( $this->createRpcTerm() );
		$this->rpcSendEXDR($goal);
		$this->waitYield();
		$t = $this->rpcRecvEXDR();
		if($t == $this->createFailTerm()) return false;
		return $t;
	}
}
?>
