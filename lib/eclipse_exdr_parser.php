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

class EXDRParseException extends Exception {
	public function __construct($pos, $expectedToken){
		$msg = "Syntax error at $pos. $expectedToken expected.";
		parent::__construct($msg);
	}
}

class EXDRUnexpectedTypeException extends Exception {
	public function __construct($arg, $method, $reqType=NULL){
		$type = get_class($arg);
		if(!$type) $type = gettype($x);
		$msg = "Unexpected type [$type] supplied as argument to $method.";
		if($reqType != NULL) $msg .= " Type was expected to be in {$reqType}.";
		parent::__construct($message);
	}
}

class EXDRNoSuchArgumentException extends Exception {
	public function __construct($i, $a){
		$message = "You required argument indexed by $i but the upper bound for this predicate is ".($a-1).".";
		parent::__construct($message);
	}
}

/**
 * This is the main class used to parse strings belonging to the language
 * generate by the EXDR grammar version 2 as described in §9.2 of
 * ECLiPSe Embedding and Interfacing manual.
 *
 * Class constructor takes an object implementing EXDRStream interface
 * which is consumed by the methods that hook specific EXDR grammar productions.
 *
 * @author Andrea Montemaggio
 * @package EXDRParsing
 */
class EXDRParser {
	private $exdrStream;
	private $parsedStrings;

	public function __construct(EXDRStream $stream){
		$this->reset($stream);
	}

	public function reset(EXDRStream $stream){
		$this->exdrStream = $stream;
		$this->parsedStrings = array();
	}

	/**
	 *	Parses 'Integer' syntactic category defined as:
	 * Integer ::= ('B' <byte> | 'I' XDR_int | 'J' XDR_long)
	 *
	 * @todo handling of XDR_long
	 * @throws EXDRParseException
	 * @return EXDR_Integer
	 */
	public function parseInteger(){
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();
		switch($c){
			case 'I': return new EXDR_Integer($this->parseEXDRint()); break;
			case 'B': return new EXDR_Integer($this->parseByte()); break;
			default:
				throw new EXDRParseException($p, "EXDR_Integer");
		}
	}

	/**
	 * Parses 'Integer' syntactic category defined as:
	 * Double ::= 'D' XDR_double
	 *
	 * @throws EXDRParseException
	 * @return EXDR_Double
	 */
	public function parseDouble(){
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();
		if($c=='D')
			return new EXDR_Double($this->parseEXDRdouble());
		else
			throw new EXDRParseException($p, "EXDR_Double");
	}

	/**
	 * Parses 'Term' syntactic category defined as:
	 * Term ::= (Integer | Double | String | List | Nil | Struct | Variable)
	 *
	 * @throws EXDRParseException
	 * @return EXDR_Term
	 */
	public function parseTerm(){
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();
		switch($c){
			case 'B': return new EXDR_Integer($this->parseByte());
			case 'I': return new EXDR_Integer($this->parseEXDRint());
			case 'D': return new EXDR_Double($this->parseEXDRdouble());
			case 'S': return new EXDR_String($this->parseString());
			case 'R': return new EXDR_String($this->parseReferencedString());
			case 'F': return $this->parseStruct();
			case '[': return new EXDR_List($this->parseListRecursive());
			case ']': return new EXDR_List(array());
			case '_': return new EXDR_Variable();
			default:
				throw new EXDRParseException($p, "EXDR_Term");
		}
	}

	private function parseListNil(){
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();
		switch($c){
			case '[': return $this->parseListRecursive();
			case ']': return array();
			default:
				throw new EXDRParseException($p, "EXDR_List | NIL");
		}
	}

	private function parseListRecursive(){
		$terms = array( $this->parseTerm() );
		$tail = $this->parseListNil();
		$terms = array_merge($terms, $tail);
		return $terms;
	}

	private function parseList(){
		$list = new EXDR_List(array());
		do{
			$t = $this->parseTerm();
			$list->append($t);
		}while($t instanceof EXDRList && !$t->isEmpty());
		return $list;
	}

	/**
	 * With 'String' syntactic category defined as
	 * String ::= ('S' Length <byte>* | 'R' Index),
	 * handles 'S' Length <byte>* case.
	 *
	 * @return string
	 */
	public function parseString(){
		$lenght = $this->parseEXDRnat();
		$str = $this->exdrStream->getNextBytes($lenght->getValue());
		$this->parsedStrings[] = $str;
		return $str;
	}

	/**
	 * With 'String' syntactic category defined as
	 * String ::= ('S' Length <byte>* | 'R' Index),
	 * handles 'R' Index case.
	 *
	 * @return string
	 */
	public function parseReferencedString(){
		$exdrNat = $this->parseEXDRnat();
		$str = $this->parsedStrings[$exdrNat->getValue()];
		return $str;
	}

	public function parseByte(){
		$c = $this->exdrStream->getNextByte();
		return new EXDR_byte($c);
	}

	/**
	 * Parses 'XDR_double' syntactic category defined as:
	 * XDR_double ::= <8 bytes, IEEE double, exp first, little endian>
	 *
	 * @return EXDR_XDRdouble
	 */
	public function parseEXDRdouble(){
		$d = $this->exdrStream->getNextBytes(8);
		$n = unpack('d', strrev($d));
		return new EXDR_XDRdouble($n[1]);
	}

	/**
	 * Parses 'XDR_int' syntactic category defined as:
	 * XDR_int ::= <4 bytes, msb first>
	 *
	 * @return EXDR_XDR_int
	 */
	public function parseEXDRint(){
		$int = $this->exdrStream->getNextBytes(4);
		$n = unpack('N', $int);
		return new EXDR_XDRint($n[1]);
	}

	/**
	 * Parses 'XDR_nat' syntactic category defined as:
	 * XDR_nat ::= (<8 bits: 1 + seven bits unsigned value> | XDR_int)
	 *
	 * @throws EXDRParseException
	 * @return EXDR_XDR_nat
	 */
	public function parseEXDRnat(){
		$p = $this->exdrStream->getPosition();
		$n = ord($this->exdrStream->getNextByte());
		if($n >> 7 == 1)
			return new EXDR_short($n & 0x7F);
		else{
			$nlow = unpack('C', $this->exdrStream->getNextBytes(3));
			return new EXDR_XDRint(($n<<24) & $nlow[1]);
		}
		throw new EXDRParseException($p, "EXDR_XDRnat");
	}

	/**
	 * Parses 'Struct' syntactic category defined as:
	 * Struct ::= 'F' Arity String Term*
	 *
	 * @throws EXDRParseException
	 * @return EXDR_Struct
	 */
	public function parseStruct(){
		$arity = $this->parseEXDRnat();
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();

		if($c == 'S')
			$name = $this->parseString();
		else if($c == 'R')
			$name = $this->parseReferencedString();
		else throw new EXDRParseException($p, "EXDR_String");

		$args = array();
		for($i=0; $i<$arity->getValue(); $i++)
			$args[] = $this->parseTerm();
		return new EXDR_Struct($name, $args);
	}

	/**
	 * Parses 'EXDRTerm' syntactic category defined as:
	 * EXDRTerm ::= 'V' Version CompactFlag? Term
	 *
	 * @throws EXDRParseException
	 * @return EXDR_EXDRTerm
	 */
	public function parseEXDRTerm(){
		$p = $this->exdrStream->getPosition();
		$c = $this->exdrStream->getNextByte();
		if($c == 'V'){
			$version = ord($this->exdrStream->getNextByte());
			$compact = $this->exdrStream->peekNextByte() == 'C';
			if($compact) $this->exdrStream->getNextByte();
			return new EXDR_EXDRTerm($this->parseTerm(), $compact, $version);
		}else{
			throw new EXDRParseException($p, "Version");
		}
	}
}

abstract class EXDR_nat {
	abstract public function getValue();
}

abstract class EXDR_Term {
	abstract public function getObject();
}

/**
 * Handles mapping between PHP int type and EXDR byte representation
 */
class EXDR_byte extends EXDR_nat {
	private $byte;

	public function __construct($byte){
		if(is_integer($byte)){
			$this->byte = $byte & 0xFF;
		}else if(is_string($byte)){
			$this->byte = ord($byte[0]);
		}else
		throw new EXDRUnexpectedTypeException($byte, __METHOD__, "integer | char");
	}

	public function __toString(){
		return chr($this->byte);
	}

	public function getValue(){
		return $this->byte;
	}
}

/**
 * Handles floating point representation conversion from PHP to EXDR
 */
class EXDR_XDRdouble {
	private $double;

	public function __construct($double){
		if(is_double($double)) $this->double = $double;
		else throw new EXDRUnexpectedTypeException($double, __METHOD__, "double");
	}

	public function __toString(){
		$le = pack('d', $this->double);
		return strrev($le);
	}

	public function getValue(){
		return $this->double;
	}
}

/**
 * Class that wraps the EXDR term representing a floating point number
 */
class EXDR_Double extends EXDR_Term {
	private $xdrDouble;

	public function __construct($x){
		if($x instanceof EXDR_XDRdouble) $this->xdrDouble = $x;
		else if(is_double($x)) $this->xdrDouble = new EXDR_XDRdouble($x);
		else
			throw new EXDRUnexpectedTypeException($x, __METHOD__, "double | EXDR_XDRdouble");
	}

	public function __toString(){
		return 'D'.$this->xdrDouble;
	}

	public function getObject(){
		return $this->xdrDouble->getValue();
	}
}


/**
 * Class that wraps the EXDR term representing an integer number
 */
class EXDR_Integer extends EXDR_Term {
	private $xdrInt;

	public function __construct($x){
		if($x instanceof EXDR_XDRint) $this->xdrInt = $x;
		else if($x instanceof EXDR_byte) $this->xdrInt = $x;
		else if(is_integer($x)) $this->xdrInt = new EXDR_XDRint($x);
		else
			throw new EXDRUnexpectedTypeException($x, __METHOD__, "integer | EXDR_XDRint");
	}

	public function __toString(){
		if($this->xdrInt instanceof EXDR_XDRint) $out = 'I';
		else if($this->xdrInt instanceof EXDR_byte) $out = 'B';
		return $out.$this->xdrInt;
	}

	public function getObject(){
		return $this->xdrInt->getValue();
	}
}

class EXDR_XDRint extends EXDR_nat {
	private $int;

	public function __construct($int){
		if(is_integer($int)) $this->int = $int;
		else throw new EXDRUnexpectedTypeException($int, __METHOD__, "integer");
	}

	public function __toString(){
		return pack('N', $this->int);
	}

	public function getValue(){
		return $this->int;
	}
}

class EXDR_short extends EXDR_nat {
	private $int;

	public function __construct($int){
		if(is_integer($int) && $int>=0 && $int <128) $this->int = $int;
		else throw new EXDRUnexpectedTypeException($int, __METHOD__, "integer in [0,127]");
	}

	public function __toString(){
		return pack('C', ($this->int & 0x7F) | 0x80);
	}

	public function getValue(){
		return $this->int;
	}
}

class EXDR_String extends EXDR_Term {
	private $str;
	
	public function __construct($str){
		if(is_string($str)) $this->str = $str;
		else throw new EXDRUnexpectedTypeException($str, __METHOD__, "string");
	}
	
	public function __toString(){
		$len = strlen($this->str);
		if($len > 127) $xdrNat = new EXDR_XDRint($len);
		else $xdrNat = new EXDR_short($len);
		return 'S'.$xdrNat.$this->str;
	}
	
	public function getObject(){
		return $this->str;
	}
}

/**
 * Class that represents a n-ary predicate in PHP
 */
class EXDR_Struct extends EXDR_Term {
	private $name, $arity, $args;

	public function __construct($name, $args=array()){
		if($name instanceof EXDR_String) $this->name = $name;
		else $this->name = new EXDR_String($name);

		if(is_array($args)){
			$arity = count($args);
			if($arity > 127) $this->arity = new EXDR_XDRint($arity);
			else $this->arity = new EXDR_short($arity);

			for($i=0; $i<$arity; $i++)
				if(!($args[$i] instanceof EXDR_Term))
					throw new EXDRUnexpectedTypeException($args[$i], __METHOD__, "EXDR_Term");
			$this->args = $args;
		}else{
			throw new EXDRUnexpectedTypeException($args, __METHOD__, "Array(EXDR_Term)");
		}
	}

	public function __toString(){
		$out = 'F'.$this->arity.$this->name;
		foreach($this->args as $arg) $out .= $arg;
		return $out;
	}

	public function getObject(){
		$argObj = array();
		foreach($this->args as $arg)
			$argObj[] = $arg->getObject();
		return new Predicate($this->name->getObject(), $argObj);
	}
}

/**
 * Class that represents a list wrapping the native PHP array type
 */
class EXDR_List extends EXDR_Term {
	private $terms;

	public function __construct($terms=null){
		if($terms === null) $terms = array();
		if(is_array($terms)){
			foreach($terms as $t)
				if(!($t instanceof EXDR_Term))
					throw new EXDRUnexpectedTypeException($t, __METHOD__, "EXDR_Term");
			$this->terms = $terms;
		}else{
			throw new EXDRUnexpectedTypeException($terms, __METHOD__, "Array(EXDR_Term)");
		}
	}

	public function append(EXDR_List $l){
		$this->terms = array_merge($this->terms, $l->getObject());
		return $this;
	}

	public function __toString(){
		$out = "";
		foreach($this->terms as $t) $out .= '['.$t;
		$out .= ']';
		return $out;
	}

	public function getObject(){
		$termsObj = array();
		foreach($this->terms as $t)
			$termsObj[] = $t->getObject();
		return $termsObj;
	}
}

class EXDR_Variable extends EXDR_Term {
	public function __construct(){}
	public function __toString(){ return '_'; }
	public function getObject(){ return null; }
}

/**
 * Class that represents an EXDR protocol envelope carrying a term as its payload
 */
class EXDR_EXDRTerm {
	const VERSION = 2;
	private $version, $compact, $term;

	public function __construct(EXDR_Term $term, $compact=true, $version=null){
		if($version==null) $version = self::VERSION;
		$this->term = $term;
		$this->compact = $compact;
		$this->version = $version;
	}

	public function __toString(){
		$versionByte = new EXDR_byte($this->version);
		$out = 'V'.$versionByte;
		if($this->compact) $out .= 'C';
		$out .= $this->term;
		return $out;
	}

	/**
	 *	Returns Term component of the EXDRTerm
	 * @return EXDR_Term
	 */
	public function getTerm(){ return $this->term; }
}

/**
 * PHP class to represent a Prolog predicate that is higher level than EXDRStruct.
 * Provides basic methods to manipulate predicates, handles conversions and EXDR serialization.
 */
class Predicate {
	private $name, $args;

	public function __construct($name, Array $args=array()){
		$this->name = $name;
		$this->args = $args;
	}

	public function getName(){
		return $this->name;
	}

	public function getArity(){
		return count($this->args);
	}

	public function getArg($i){
		$arity = count($this->args);
		if($i > $arity-1) throw new EXDRNoSuchArgumentException($i, $arity);
		else return $this->args[$i];
	}

	public function getArgs(){
		return $this->args;
	}

	/**
	 * Serializes Predicate with PHP objects as arguments to an EXDRTerm.
	 * @return EXDR_EXDRTerm
	 */
	public function toEXDRTerm(){
		return new EXDR_EXDRTerm( $this->objToTerm($this) );
	}

	/**
	 *	Takes care of converting native PHP types in the
	 * respective EXDR representations
	 *
	 * @param object $o
	 * @return EXDR_Term
	 */
	private function objToTerm($o){
		if(is_null($o)) $t = new EXDR_Variable();
		else if($o instanceof Predicate)
			$t = new EXDR_Struct($o->getName(), $this->objectsToTerms($o->getArgs()));
		else if(is_array($o)) $t = new EXDR_List($this->objectsToTerms($o));
		else if(is_integer($o)) $t = new EXDR_Integer($o);
		else if(is_double($o)) $t = new EXDR_Double($o);
		else if(is_string($o)) $t = new EXDR_String($o);
		return $t;
	}
	
	/**
	 *	Helper function to handle he conversion of an array of PHP objects
	 * representing terms in an array of EXDR_Term objects
	 * 
	 * @param array $arr
	 * @return array
	 */
	private function objectsToTerms(Array $arr){
		$terms = array();
		foreach($arr as $e) $terms[] = $this->objToTerm($e);
		return $terms;
	}

	private function arrayToString($arr){
		$out = '[';
		for($i=0; $i<count($arr); $i++){
			if($i>0) $out .= ', ';
			if(is_array($arr[$i])) $out .= $this->arrayToString($arr[$i]);
			else $out .= $arr[$i];
		}
		return $out.']';
	}

	public function __toString(){
		$out = $this->getName().'(';
		for($i=0; $i<$this->getArity(); $i++){
			if($i>0) $out.=', ';
			$o = $this->getArg($i);
			if(is_null($o)) $out .= '_';
			else if(is_array($o)) $out .= $this->arrayToString($o);
			else $out .= $o;
		}
		return $out.')';
	}
}
?>
