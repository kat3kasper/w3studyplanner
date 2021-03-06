% ----------------------------------------------------------------------
% BEGIN LICENSE BLOCK
% Version: CMPL 1.1
%
% The contents of this file are subject to the Cisco-style Mozilla Public
% License Version 1.1 (the "License"); you may not use this file except
% in compliance with the License.  You may obtain a copy of the License
% at www.eclipse-clp.org/license.
% 
% Software distributed under the License is distributed on an "AS IS"
% basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.  See
% the License for the specific language governing rights and limitations
% under the License. 
% 
% The Original Code is  The ECLiPSe Constraint Logic Programming System. 
% The Initial Developer of the Original Code is  Cisco Systems, Inc. 
% Portions created by the Initial Developer are
% Copyright (C) 1989-2006 Cisco Systems, Inc.  All Rights Reserved.
% 
% Contributor(s): ECRC GmbH
% Contributor(s): IC-Parc, Imperal College London
% 
% END LICENSE BLOCK
%
% System:	ECLiPSe Constraint Logic Programming System
% Version:	$Id: iso.pl,v 1.5.2.7 2011/04/07 09:18:10 jschimpf Exp $
% ----------------------------------------------------------------------

%
% ECLiPSe PROLOG LIBRARY MODULE
%
% $Id: iso.pl,v 1.5.2.7 2011/04/07 09:18:10 jschimpf Exp $
%
% IDENTIFICATION:	iso.pl
%
% AUTHOR:		Joachim Schimpf
%
% CONTENTS:		see export directive
%
% DESCRIPTION:		ISO Prolog compatibility package (incomplete)
%			It follows standard draft ISO/IEC DIS 13211-1:1995(E)
%

:- module(iso).

% suppress deprecation warnings for reexported builtins
:- pragma(deprecated_warnings(not_reexports)).

:- reexport eclipse_language except

	floor/2,			% these have different behaviour
	ceiling/2,
	round/2,
	truncate/2,
	abolish/1,
	get_char/1,
	get_char/2.

:- export
	op(0,   xfx, (of)),		% remove some eclipse extensions
	op(0,   xfx, (with)),
	op(0,   xfy, (do)),
	op(0,   xfx, (@)),
	op(0,   fx, (-?->)),
	macro((with)/2, (=)/2, []),
	macro((of)/2, (=)/2, []).

:- local
	op(650, xfx, (@)),		% allow it locally
	op(1100, xfy, (do)).

:- export
	syntax_option(not(nl_in_quotes)),
	syntax_option(iso_escapes),
	syntax_option(iso_base_prefix),
	syntax_option(doubled_quote_is_quote),
	syntax_option(no_array_subscripts),
	syntax_option(bar_is_no_atom),
	syntax_option(no_attributes),
	syntax_option(no_curly_arguments),
	syntax_option(blanks_after_sign),
	syntax_option(limit_arg_precedence).

:- export
	chtab(0'`, string_quote),
	chtab(0'", list_quote).

:- comment(categories, [`Compatibility`]).
:- comment(summary, `ISO Prolog compatibility library`).
:- comment(author, `Joachim Schimpf, ECRC and IC-Parc`).
:- comment(copyright, `Cisco Systems, Inc`).
:- comment(date, `$Date: 2011/04/07 09:18:10 $`).
:- comment(see_also, [library(multifile)]).
:- comment(desc, html(`\
    This library provides a reasonable degree of compatibility with\n\
    the definition of Standard Prolog as defined in ISO/IEC 13211-1\n\
    (Information Technology, Programming Languages, Prolog, Part 1: \n\
    General Core, 1995).  The areas where the library is not fully\n\
    compiant are I/O and exception handling.  However it should be\n\
    sufficient for most applications.  The library is provided in\n\
    source form.\n\
    <P>\n\
    The effect of the compatibility library is local to the module where\n\
    it is loaded. For maximal ISO-compatibility, an ISO-program should\n\
    be contained in a  separate module starting with a directive like\n\
    <PRE>\n\
    :- module(myisomodule, [], iso).\n\
    </PRE>\n\
    In this case, Eclipse-specific language constructs will not be available.\n\
    <P>\n\
    If the compatibility package is loaded into a standard module, e.g. like\n\
    <PRE>\n\
    :- module(mymixedmdule).\n\
    :- use_module(library(iso)).\n\
    </PRE>\n\
    then ISO and Eclipse language features can be used together. However,\n\
    ambiguities must be resolved explicitly and confusion may arise from\n\
    the different meaning of quotes in Eclipse vs ISO.\n\
    <P>\n\
    The recommended way is therefore the former one, ie to put code written\n\
    in different language dialects into different modules.`)).

:- export
	op(200, xfx, (**)).

:- export
	(**)/3,
	abolish/1,
	at_end_of_stream/0,
	at_end_of_stream/1,
	atom_concat/3,
	atom_codes/2,
	atom_chars/2,
	catch/3,
	catch/4,
	ceiling/2,
	char_conversion/2,
	close/2,
	current_char_conversion/2,
	current_input/1,
	current_output/1,
	current_prolog_flag/2,
	float_integer_part/2,
	float_fractional_part/2,
	floor/2,
	flush_output/0,
	flush_output/1,
	get_byte/1,
	get_byte/2,
	get_char/1,
	get_char/2,
	get_code/1,
	get_code/2,
	halt/1,
	initialization/1,
	iso_recover/4,
	log/2,
	number_chars/2,
	number_codes/2,
	peek_byte/1,
	peek_byte/2,
	peek_char/1,
	peek_char/2,
	peek_code/1,
	peek_code/2,
	put_byte/1,
	put_byte/2,
	put_code/1,
	put_code/2,
	round/2,
	set_input/1,
	set_output/1,
	set_prolog_flag/2,
	set_stream_position/2,
	sign/2,
	stream_property/2,
	sub_atom/5,
	throw/1,
	truncate/2,
	unify_with_occurs_check/2.

:- tool(catch/3, catch/4).
:- tool(initialization/1, initialization/2).
:- tool(current_prolog_flag/2, current_prolog_flag_/3).
:- tool(set_prolog_flag/2, set_prolog_flag_/3).

:- pragma(nodebug).
:- pragma(system).

:- import bip_error/1, set_bip_error/1 from sepia_kernel.

%-----------------------------------------------------------------------
% 7.4 Directives
%-----------------------------------------------------------------------

:- reexport multifile.

initialization(Goal, Module) :-
	local(initialization(Goal))@Module.

%-----------------------------------------------------------------------
% 7.8 Control constructs (ok)
%-----------------------------------------------------------------------

:- local variable(ball).

catch(Goal, Catcher, Recovery, Module) :-
	block(Goal, Tag, iso:iso_recover(Tag, Catcher, Recovery, Module))@Module.


    iso_recover(iso_ball_thrown, Catcher, Recovery, Module) :- !,
	getval(ball, Ball),
	( Catcher = Ball ->
	    setval(ball, _),
	    call(Recovery)@Module
	;
	    exit_block(iso_ball_thrown)
	).
    iso_recover(Tag, Catcher, Recovery, Module) :-
	( Catcher = Tag ->
	    call(Recovery)@Module
	;
	    exit_block(Tag)
	).
	

throw(Ball) :-
	atomic(Ball),
	exit_block(Ball).
throw(Ball) :-
	nonvar(Ball),
	setval(ball, Ball),
	exit_block(iso_ball_thrown).
throw(Ball) :-
	var(Ball),
	error(4, throw(Ball)).


    throw_handler(N, exit_block(iso_ball_thrown)) :-
	getval(ball, Ball),
	setval(ball, _),
	error(N, throw(Ball)).
    throw_handler(N, Goal) :-
	error(default(N), Goal).
	
:- set_event_handler(230, throw_handler/2).


%-----------------------------------------------------------------------
% 8.2 Term Unification (ok)
%-----------------------------------------------------------------------

unify_with_occurs_check(X, X) :-		% 8.2.2
	acyclic_term(X).


%-----------------------------------------------------------------------
% 8.6 Arithmetic evaluation
%-----------------------------------------------------------------------

% allow expressions built at runtime without an eval wrapper to be evaluated

:- set_event_handler(24, eval_expr/2).

eval_expr(N, ArithGoal) :-
        functor(ArithGoal, Op, A),
        NewA is A - 1,
        functor(Expr, Op, NewA),
	( sepia_kernel:arith_builtin(Expr) ->
	    ( foreacharg(X,Expr,I), param(ArithGoal) do
		arg(I, ArithGoal, X)
	    ),
	    arg(A, ArithGoal, Res),
	    Res is Expr
	;
	    error(default(N), ArithGoal)
	).


%-----------------------------------------------------------------------
% 8.9 Clause creation and destruction (ok)
%-----------------------------------------------------------------------

% don't retract all on a subsequent dynamic/1 declaration
:- set_event_handler(64, true/0).

:- tool(abolish/1, abolish_/2).			% 8.9.4
abolish_(Pred, Module) :-
	( current_predicate(Pred)@Module ->
	    ( is_dynamic(Pred)@Module ->
		eclipse_language:abolish(Pred)@Module
	    ;
	    	error(63, abolish(Pred), Module)
	    )
	;
	    true
	).

%-----------------------------------------------------------------------
% 8.11 Stream selection and control (complete except stream properties)
%-----------------------------------------------------------------------

current_input(Stream) :-			% 8.11.1
	get_stream(input, Stream).

current_output(Stream) :-			% 8.11.2
	get_stream(output, Stream).

set_input(Stream) :-				% 8.11.3
	set_stream(input, Stream).

set_output(Stream) :-				% 8.11.4
	set_stream(output, Stream).

close(Stream, _) :-				% 8.11.6
	close(Stream).

flush_output :- flush(output).			% 8.11.7

flush_output(Stream) :- flush(Stream).

stream_property(Stream, Property) :-
	current_stream(Stream),
	stream_property1(Stream, Property).

stream_property1(Stream, file_name(F)) :-	% 8.11.8 and 7.10.2.13
	get_stream_info(Stream, name, F).
stream_property1(Stream, mode(M)) :-
	get_stream_info(Stream, mode, M).	% don't know if append
stream_property1(Stream, InOut) :-
	get_stream_info(Stream, mode, Mode),
	in_out(Mode, InOut).
stream_property1(Stream, alias(Alias)) :-
	current_atom(Alias),
	current_stream(Alias),
	get_stream(Alias, Stream).
stream_property1(Stream, position(P)) :-
	at(Stream, P).
stream_property1(Stream, end_of_stream(P)) :-
	(at_eof(Stream) -> P = at ; P = not).	% 'past' not available
stream_property1(_Stream, eof_action(default)).
stream_property1(Stream, reposition(B)) :-
	get_stream_info(Stream, device, D),
	repositionable(D, B).
stream_property1(_Stream, type(binary)).
	
    repositionable(file, true).
    repositionable(null, true).
    repositionable(pipe, false).
    repositionable(queue, false).
    repositionable(socket, false).
    repositionable(string, true).
    repositionable(tty, false).

    in_out(read, input).
    in_out(write, output).
    in_out(append, output).
    in_out(update, input).
    in_out(update, output).


at_end_of_stream :-
	at_eof(input).

at_end_of_stream(Stream) :-
	at_eof(Stream).

set_stream_position(Stream, P) :-		% 8.11.9
	seek(Stream, P).

get_byte(Code) :- get(Code).			% 8.13 and 8.14
get_byte(Stream, Code) :- get(Stream, Code).
get_char(Char) :- get_char(input, Char).
get_char(Stream, Char) :- get(Stream, Code),
	( Code = -1 -> Char = end_of_file ; char_code(Char, Code) ).
get_code(Code) :- get(Code).
get_code(Stream, Code) :- get(Stream, Code).

put_byte(Code) :- put(Code).
put_byte(Stream, Code) :- put(Stream, Code).
put_code(Code) :- put(Code).
put_code(Stream, Code) :- put(Stream, Code).

peek_byte(Stream, Byte) :- get(Stream, Next), unget(Stream), Next=Byte.
peek_byte(Byte) :- peek_byte(input, Byte).
peek_char(Stream, Byte) :- get_char(Stream, Next), unget(Stream), Next=Byte.
peek_char(Byte) :- peek_char(input, Byte).
peek_code(Stream, Byte) :- get(Stream, Next), unget(Stream), Next=Byte.
peek_code(Byte) :- peek_code(input, Byte).


%-----------------------------------------------------------------------
% 8.14 Term input/output (incomplete)
%-----------------------------------------------------------------------

char_conversion(_C1, _C2) :-			% 8.14.5
	writeln(warning_output,
	    'WARNING: char_conversion/2 not implemented, ignored.').

current_char_conversion(C, C).			% 8.14.6


%-----------------------------------------------------------------------
% 8.16 Constant Processing (ok)
%-----------------------------------------------------------------------

atom_concat(A, B, C) :-				% 8.16.2
	var(C), !,
	concat_atoms(A, B, C).
atom_concat(A, B, C) :-
	nonvar(C), nonvar(A), nonvar(B), !,
	concat_atoms(A, B, C).
atom_concat(A, B, C) :-
	nonvar(C),
	atom_string(C, SC),
	append_strings(SA, SB, SC),
	atom_string(A, SA),
	atom_string(B, SB).

sub_atom(Atom, Before, Length, After, SubAtom) :-	% 8.16.3
	var(SubAtom),
	atom_string(Atom, String),
	substring(String, Before, Length, After, SubString),
	atom_string(SubAtom, SubString).
sub_atom(Atom, Before, Length, After, SubAtom) :-
	nonvar(SubAtom),
	atom_string(Atom, String),
	atom_string(SubAtom, SubString),
	substring(String, Before, Length, After, SubString).

atom_chars(Atom, Chars) :-			% 8.16.4
	var(Atom),
	concat_atom(Chars, Atom).
atom_chars(Atom, Chars) :-
	nonvar(Atom),
	atom_codes(Atom, Codes),
	chars_codes(Chars, Codes).

atom_codes(Atom, List) :-			% 8.16.5
	var(Atom),
	string_list(String, List),
	atom_string(Atom, String).
atom_codes(Atom, List) :-
	nonvar(Atom),
	atom_string(Atom, String),
	string_list(String, List).


% number_chars/2 and number_codes/2 are a pain wrt exceptions...

number_chars(Number, Chars) :-			% 8.16.7
        ( var(Number) ->
            ( valid_chars(Chars, Chars1) ->
                concat_string(Chars1, String),
                valid_numstring(String, String1),
                number_string(Number, String1)  % read
            ;
                bip_error(number_chars(Number, Chars))
            )
        ; number(Number) ->
            number_string(Number, String),      % write
            string_list(String, Codes),
            ( chars_codes(Chars, Codes) ->
                true
            ; valid_output_chars(Chars) ->
                ground(Chars),
                concat_string(Chars, String0),
                valid_numstring(String0, String1),
                number_string(Number, String1)  % read
            ;
                bip_error(number_chars(Number, Chars))
            )
        ;
            error(5, number_chars(Number, Chars))
        ).

number_codes(Number, Codes) :-			% 8.16.8
        ( var(Number) ->
            string_list(String, Codes),
            valid_numstring(String, String1),
            number_string(Number, String1)  % read
        ; number(Number) ->
            number_string(Number, NumString),      % write
            ( string_list(NumString, Codes) ->
                true
            ; valid_output_codes(Codes) ->
                ground(Codes),
                string_list(String, Codes),
                valid_numstring(String, String1),
                number_string(Number, String1)  % read
            ;
                bip_error(number_codes(Number, Codes))
            )
        ;
            error(5, number_codes(Number, Codes))
        ).

    chars_codes([], []).
    chars_codes([Char|Chars], [Code|Codes]) :-
	char_code(Char, Code),
	chars_codes(Chars, Codes).

    valid_chars([], Ds) ?- !, Ds=[].
    valid_chars([C|Cs], Ds) ?- !,
        ( var(C) -> set_bip_error(4)
        ; atom(C), atom_length(C, 1) ->
            Ds = [C|Ds1],
            valid_chars(Cs, Ds1)
        ;
            set_bip_error(5)
        ).
    valid_chars(X, _) :- var(X), !,
        set_bip_error(4).
    valid_chars(_, _) :-
        set_bip_error(5).

    valid_output_chars(X) :- var(X), !.
    valid_output_chars([]) ?- !.
    valid_output_chars([C|Cs]) ?-
        (var(C) ; atom(C), atom_length(C, 1)),
        !,
        valid_output_chars(Cs).
    valid_output_chars(_) :-
        set_bip_error(5).

    valid_output_codes(X) :- var(X), !.
    valid_output_codes([]) ?- !.
    valid_output_codes([C|Cs]) ?-
        (var(C) ; integer(C)),
        !,
        valid_output_codes(Cs).
    valid_output_codes(_) :-
        set_bip_error(5).

    valid_numstring(String0, String) :-
	split_string(String0, `\n\r\t `, ``, Strings0),
	valid_numstring1(Strings0, String).

    valid_numstring1([``|Ss0], String) ?- !,
    % leading white spaces is ok...
	valid_numstring1(Ss0, String).
    valid_numstring1([String0], String) ?- 
    % no trailing white spaces 
	String0 = String.

%-----------------------------------------------------------------------
% 8.17 Implementation defined hooks (incomplete)
%-----------------------------------------------------------------------

set_prolog_flag_(debug, Value, M) :- !,
	( Value == on -> set_flag(debugging, creep)
	; Value == off -> set_flag(debugging, nodebug)
	; error(6, set_prolog_flag(debug, Value), M)).
set_prolog_flag_(double_quotes, Value, M) :- !,
	( Value == atom -> set_chtab(0'", atom_quote)@M
	; Value == string -> set_chtab(0'", string_quote)@M
	; Value == codes -> set_chtab(0'", list_quote)@M
	; Value == chars -> error(141, set_prolog_flag(double_quotes, Value), M)
	; error(6, set_prolog_flag(double_quotes, Value), M)).
set_prolog_flag_(unknown, Value, M) :- !,
	( Value == error -> reset_event_handler(68)
	; Value == fail -> set_event_handler(68, fail/0)
	; Value == warning -> set_event_handler(68, warn_and_fail/3)
	; error(6, set_prolog_flag(unknown, Value), M)).
set_prolog_flag_(Flag, Value, M) :-
	readonly(Flag),
	!,
	error(30, set_prolog_flag(Flag, Value), M).
set_prolog_flag_(Flag, Value, M) :-			% 8.17.1
	set_flag(Flag, Value)@M.

    warn_and_fail(_, Goal, Module) :-
    	printf(warning_output,
	    'WARNING: calling an undefined procedure %w in module %w%n',
	    [Goal,Module]),
	fail.

    readonly(bounded).
    readonly(dialect).
    readonly(char_conversion).
    readonly(integer_rounding_function).
    readonly(min_integer).
    readonly(max_integer).
    readonly(max_arity).

current_prolog_flag_(bounded, false, _M).
current_prolog_flag_(char_conversion, off, _M).
current_prolog_flag_(debug, Value, _M) :-
	get_flag(debugging, D),
	( D = creep -> Value = on
	; D = leap -> Value = on
	; Value = off ).
current_prolog_flag_(dialect, eclipse, _M).
current_prolog_flag_(double_quotes, Value, M) :-
	( get_chtab(0'", atom_quote)@M -> Value = atom
	; get_chtab(0'", string_quote)@M -> Value = string
	; get_chtab(0'", list_quote)@M -> Value = codes
	; Value = unknown ).
current_prolog_flag_(integer_rounding_function, toward_zero, _M).
%current_prolog_flag_(min_integer, _, _M) :- fail.
%current_prolog_flag_(max_integer, _, _M) :- fail.
current_prolog_flag_(max_arity, unbounded, _M).
current_prolog_flag_(unknown, Value, _M) :-
	( get_event_handler(68, fail/0, _) -> Value = fail
	; get_event_handler(68, warn_and_fail/3, _) -> Value = warning
	; Value = error
	).
current_prolog_flag_(Flag, Value, M) :- get_flag(Flag, Value)@M.

halt(X) :- exit(X).				% 8.17.4

%-----------------------------------------------------------------------
% 9. Evaluable functors (incomplete)
% Note: the redefinitions of floor,ceiling,round,truncate don't work currently,
% the arithmetic transformation in the ECLiPSe compiler always uses the
% sepia_kernel definitions for the predefined arithmetic functions!
%-----------------------------------------------------------------------

**(X,Y,Z) :- Z is float(eval(X)^eval(Y)).
sign(X,Y) :- X1 is eval(X), Yi is sgn(X1),
	( float(X1) -> Y is float(Yi)		% gives sign(-0.0,0.0)
	; rational(X1) -> Y is rational(Yi)
	; breal(X1) -> Y is breal(Yi)
	; Y = Yi
	).
log(X,Y) :- Y is ln(eval(X)).
floor(X,Y) :- X1 is X, Y is integer(sepia_kernel:floor(X1)).
ceiling(X,Y) :- X1 is X, Y is integer(sepia_kernel:ceiling(X1)).
round(X,Y) :- X1 is X, Y is integer(sepia_kernel:round(X1)).
truncate(X,Y) :- X1 is X, Y is integer(sepia_kernel:truncate(X1)).
float_integer_part(X,Y) :- X1 is X, Y is sepia_kernel:truncate(X1).
float_fractional_part(X,Y) :- X1 is X, Y is X1-truncate(X1).
