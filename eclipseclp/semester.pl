semester(Term, Year, Min, Max, Courses).
semester(spring, 2013, 12, 18, [cs105, hum101, pep111, pe100, ma115]).
semester(fall, 2013, 12, 18, []).
semester(spring, 2014, 12, 15, []).

term(spring).
term(fall).
term(summerA).
term(summerB).

%course(Number, Prereqs, Coreqs, Terms, Credits).
course(cs105,[],[],[fall,spring],3).
course(hum101,[],[],[fall],3).
course(pep111, [], [], [fall], 3).
course(pe100,[],[],[fall,spring], 1).
course(ma115,[],[],[fall,spring],4).

	
classes_in_semester(semester(_,_,_,_,[A|B]), ClassList):-
	course(A,_,_,_,_),
	classes_in_semester2(semester(_,_,_,_,B),[A|ClassList]).

classes_in_semester(semester(_,_,_,_,[]),ClassList):-
	writeln("Classes in semester":ClassList).

credits_in_semester(semester(_,_,_,_,[A|B]), ClassList):-
	course(A,_,_,_,_),
	credits_in_semester(semester(_,_,_,_,B),[A|ClassList]).

credits_in_semester(semester(_,_,_,_,[]),ClassList):-
	writeln("Classes in first level":ClassList),
	get_cumul_credits(ClassList, 0, Total).	
	
get_cumul_credits([A|B],Accum, Total):-
	course(A,_,_,_,Credits),
	writeln(Credits),
	Total is Accum+Credits,
	get_cumul_credits(B, Total, Total).

get_cumul_credits([], _, Total):-
	writeln("Number of credits":Total).


	
