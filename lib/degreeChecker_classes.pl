% load class predicates
:- ensure_loaded(courseGroup).

%Determines if a list of semesters is a valid degree given the degree requirements
okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq):-
	okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, []).	
okDegree([], [], _).
okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, CoursesTaken):-	
	possible_sem(Term, Year, Min, Max, CorCG, DefiniteCourses, DegreeReq, CoursesTaken),
	append(CorCG, DefiniteCourses, CorCG_FullList),
	getDefiniteClasses(CorCG_FullList, ClassesBeingTaken),
	check_preReqs_for_courseList(ClassesBeingTaken, CoursesTaken),	
	subtract_custom(DegreeReq, CorCG, CourseGroupsLeft),
	flatten([ClassesBeingTaken|CoursesTaken], Taken),
	okDegree(RestOfSemests, CourseGroupsLeft, Taken).

%Determines if a semester made up of courses & course groups is valid	
possible_sem(Term, Year, Min, Max, RestOfCorCG_List, DefiniteCourses, DegreeReq, CoursesTaken):-
	getDegreeReqsInTerm(DegreeReq, Term, DegreeReqInTerm),
	getDegreeReqsWithPreReqs(DegreeReqInTerm, CoursesTaken, PossibleDegreeReq),
	writeln("Possible Degree Reqs": PossibleDegreeReq),
	subset(RestOfCorCG_List, PossibleDegreeReq),
	append(RestOfCorCG_List, DefiniteCourses, CorCG_List), 
	length(CorCG_List, N),
	getDefiniteClasses(CorCG_List, ClassesInSemester),
	checkTerm(CorCG_List, Term, ClassesInSemester),
	get_credits(CorCG_List, Term, Credits, ClassesInSemester),
	Min =< Credits,
	Max >= Credits.	

%Returns the list of classes specified from a list of degree requirements 
getDefiniteClasses(CorCG_List, Classes):-
	getDefiniteClasses(CorCG_List, Classes, []).
getDefiniteClasses([degreeReq(A,B)|End], Classes, RunningList):-
	B \== none,
	getDefiniteClasses(End, Classes, [B|RunningList]),
	!.
getDefiniteClasses([degreeReq(A,B)|End], Classes, RunningList):-
	getDefiniteClasses(End, Classes, RunningList),
	!.
getDefiniteClasses([], Classes, Classes).	

%Checks that every course inputed has taken all of its pre-reqs
check_preReqs_for_courseList([A|B], CoursesTaken):-
	course(A, Prereqs, _, _, _),
	subtract(Prereqs, CoursesTaken, CoursesNotTaken),
	length(CoursesNotTaken,0),
	check_preReqs_for_courseList(B, CoursesTaken).	
check_preReqs_for_courseList([], CoursesTaken).	

%Used to subtract degree requirements from list of degree requirements
subtract_custom(Remainder, [], Remainder).
subtract_custom(List, [Current|Delete], X) :-
    select3(Current, List, Rest),
    subtract_custom(Rest, Delete, X).
select3( _, [], []).	
select3( H, [H|T], T ):- !.
select3( Element, [H|T0], [H|T1] ) :-
    select3( Element, T0, T1 ).
	
%Returns the total number of credits for a semester	
get_credits([degreeReq(A,X)|B], Term, Total, ClassesInSemester):-
	get_credits([degreeReq(A,X)|B], Term, 0 , Total, ClassesInSemester). 	
get_credits([degreeReq(A,none)|B], Term, Accum, Total, ClassesInSemester):-
	!,
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	list_of_classes_in_term(PossibleCGClasses, Term, Output),
	get_course_credits(Output, Sum),
	length(Output, N),
	N > 0,
	Avg is Sum/N,
	floor(Avg, Floor),
	New is Accum+Floor,
	get_credits(B, Term, New, Total, ClassesInSemester).
get_credits([degreeReq(A,X)|B],Term, Accum, Total, ClassesInSemester):-	
	!,
	course(X,_,_,_,Credits),
	New is Accum+Credits,
	get_credits(B,Term, New, Total, ClassesInSemester).
get_credits([],Term, Total, Total, _).	

%gets the number of credits for a list of courses
get_course_credits(CourseList, Total):-
	get_course_credits(CourseList, 0 , Total).	
get_course_credits([A|B],Accum, Total):-
	course(A,_,_,_,Credits),
	New is Accum+Credits,
	get_course_credits(B, New, Total).
get_course_credits([], Total, Total).

%Returns a list of all degree requirements that are able to be taken for a specific term
getDegreeReqsInTerm([], Term, []).
getDegreeReqsInTerm([degreeReq(A,X)|B],Term,Output):-
	getDegreeReqsInTerm([degreeReq(A,X)|B],Term,Output, []).
getDegreeReqsInTerm([degreeReq(A,none)|B],Term,Output, RunningList):-
	courseGroup(A, AllClasses),
	check1ClassInTerm(AllClasses, Term),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeReq(A, none)|RunningList]).
getDegreeReqsInTerm([degreeReq(A,X)|B],Term,Output, RunningList):-
	course(X,_,_,TermList,_),
	member(Term, TermList),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeReq(A,X)|RunningList]).
getDegreeReqsInTerm([degreeReq(A,X)|B],Term, Output, RunningList):-
	getDegreeReqsInTerm(B, Term, Output, RunningList).
getDegreeReqsInTerm([], _, Output, Output).	

%Given a list of degree req 
%returns a list of all degree req that have pre-reqs ignoring course groups
getDegreeReqsWithPreReqs([], _, []).
getDegreeReqsWithPreReqs([degreeReq(A,X)|B], CoursesTaken, Output):-
	getDegreeReqsWithPreReqs([degreeReq(A,X)|B], CoursesTaken, Output, []).
%NOT CHECKIN PREREQS FOR COURSE GROUPS YET
getDegreeReqsWithPreReqs([degreeReq(A,none)|B], CoursesTaken, Output, RunningList):-
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeReq(A,none)|RunningList]).
getDegreeReqsWithPreReqs([degreeReq(A,X)|B], CoursesTaken, Output, RunningList):-
	check_preReqs_for_courseList([X], CoursesTaken),
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeReq(A,X)|RunningList]).
getDegreeReqsWithPreReqs([degreeReq(_,_)|B], CoursesTaken, Output, RunningList):-	
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, RunningList).
getDegreeReqsWithPreReqs([], _, Output, Output).

%Checks that all courses & course groups are offers during a specific term
%check1ClassInTerm(PossibleCGClasses, Term)	
checkTerm([degreeReq(A,X)|B],Term, ClassesInSemester):-
	X == none,
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	list_of_classes_in_term(PossibleCGClasses, Term, Output),
	length(Output, N),
	N > 0, 
	checkTerm(B,Term, ClassesInSemester),
	!.	
checkTerm([degreeReq(A,X)|B],Term, ClassesInSemester):-
	course(X,_,_,TermList,_),
	member(Term, TermList),
	checkTerm(B,Term, ClassesInSemester),
	!.	
checkTerm([], _, _).	

%Given a list of classes returns a list of all classes in a specific term
list_of_classes_in_term([A|B], Term, Output):-
	list_of_classes_in_term([A|B], Term, Output, []).
list_of_classes_in_term([A|B], Term, Output, RunningList):-
	course(A,_,_,TermList,_),
	member(Term, TermList),
	!,
	list_of_classes_in_term(B, Term, Output, [A|RunningList]).
list_of_classes_in_term([A|B], Term, Output, RunningList):-
	list_of_classes_in_term(B, Term, Output, RunningList).
list_of_classes_in_term([], Term, Output, Output).		

%Determines if 1 class in a list is available during a specific term
check1ClassInTerm([A|B], Term):-
	course(A,_,_,TermList,_),
	member(Term, TermList).
check1ClassInTerm([A|B], Term):-
	check1ClassInTerm(B, Term).
check1ClassInTerm([], _):-
	fail.

