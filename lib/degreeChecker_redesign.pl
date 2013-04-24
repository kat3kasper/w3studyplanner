% load coursegroup information (includes course info)
:- ensure_loaded(courseGroup).

%Determines if a list of semesters is a valid degree given the degree requirements
okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq):-
	okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, []).	
okDegree([], [], _).
okDegree([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, CoursesTaken):-	
	validateInput([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests]),
	getDefiniteClasses(DegreeReq, Classes1),
	getAllPerferredCourses([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], PerferredCourses),
	flatten([Classes1|PerferredCourses],AllCoursesBeingTaken), 
	constructSchedule([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, CoursesTaken, AllCoursesBeingTaken,[]).

getAllPerferredCourses([semesterNew(_, _, _, _, _, DefiniteCourses)|RestOfSemests], PerferredCourses):-
	getAllPerferredCourses([semesterNew(_, _, _, _, _, DefiniteCourses)|RestOfSemests], PerferredCourses, []).
getAllPerferredCourses([], PerferredCourses, PerferredCourses).
getAllPerferredCourses([semesterNew(_, _, _, _, _, DefiniteCourses)|RestOfSemests], PerferredCourses, Accum):-
	getDefiniteClasses(DefiniteCourses, Courses),
	flatten([Courses|Accum], RunningCourseList),
	getAllPerferredCourses(RestOfSemests, PerferredCourses, RunningCourseList).
	
%Validates that courses specified for a specific semester are offered during that term
%Should be valid already via website, but double checks	
validateInput([]).
validateInput([semesterNew(Term, _, _, _, _, DefiniteCourses)|RestOfSemests]):-
	checkTerm2(DefiniteCourses, Term, []),
	validateInput(RestOfSemests).

%Function that attempts build subsequent semesters	
constructSchedule([], [], _, _,_).
constructSchedule([semesterNew(Term, Year, Min, Max, CorCG, DefiniteCourses)|RestOfSemests], DegreeReq, CoursesTaken, AllCoursesBeingTaken, DegreeReqsTaken):-	
	possible_sem(Term, Year, Min, Max, CorCG, DefiniteCourses, DegreeReq, CoursesTaken),
	append(CorCG, DefiniteCourses, CorCG_FullList),
	getDefiniteClasses(CorCG_FullList, ClassesBeingTaken),
	check_preReqs_for_courseList2(ClassesBeingTaken, CoursesTaken),
	append(ClassesBeingTaken, CoursesTaken, AllCoReqPossibilities),
	check_coReqs_for_semester(ClassesBeingTaken, AllCoReqPossibilities),
	determinePossibleClassesForCourseGroup(CorCG, Term, AllCoursesBeingTaken),
	subtract_custom(DegreeReq, CorCG, CourseGroupsLeft),
	flatten([ClassesBeingTaken|CoursesTaken], Taken),
	flatten([CorCG|DegreeReqsTaken], DegreeReqsBeingTaken),
	writeln("Degree Reqs": DegreeReqsBeingTaken),
	verifySameCGHaveEnoughCourses(CorCG, DegreeReqsBeingTaken),
	constructSchedule(RestOfSemests, CourseGroupsLeft, Taken, AllCoursesBeingTaken, DegreeReqsBeingTaken).	

determinePossibleClassesForCourseGroup([degreeReq(A, none, Courses)|Rest], Term, AllCoursesBeingTaken):-
	courseGroup(A, AllClasses),
	subtract(AllClasses, AllCoursesBeingTaken, ClassesSub1),
	list_of_classes_in_term(ClassesSub1, Term, Courses),
	!,
	determinePossibleClassesForCourseGroup(Rest, Term, AllCoursesBeingTaken).
determinePossibleClassesForCourseGroup([degreeReq(_,_,_)|Rest], Term, AllCoursesBeingTaken):-	
	determinePossibleClassesForCourseGroup(Rest, Term, AllCoursesBeingTaken).
determinePossibleClassesForCourseGroup([], _, _).	

verifySameCGHaveEnoughCourses([],_).
verifySameCGHaveEnoughCourses([degreeReq(A, none, Courses)|Rest], AllDegreeReqs):-
	findall(degreeReq(A, none, X), member(degreeReq(A, none, X), AllDegreeReqs), MultipleDegreeReqList),
	findall(X, member(degreeReq(A, none, X), MultipleDegreeReqList), CoursesForCourseGroupWDups),
	!,
	flatten(CoursesForCourseGroupWDups, FlattenedCourseList),
	remove_duplicates(FlattenedCourseList,CoursesForCourseGroup), 
	length(MultipleDegreeReqList, NumberOfSameDegreeReqs),
	length(CoursesForCourseGroup, NumberOfAvailableCourses),
	NumberOfSameDegreeReqs =< NumberOfAvailableCourses,
	verifySameCGHaveEnoughCourses(Rest, AllDegreeReqs).	
verifySameCGHaveEnoughCourses([degreeReq(_,_,_)|Rest], AllDegreeReqs):-
	verifySameCGHaveEnoughCourses(Rest, AllDegreeReqs).

	
%Determines if a semester made up of courses & course groups is valid	
possible_sem(Term, Year, Min, Max, RestOfCorCG_List, DefiniteCourses, DegreeReq, CoursesTaken):-
	checkTerm2(DefiniteCourses, Term, []),
	!,
	getDegreeReqsInTerm(DegreeReq, Term, DegreeReqInTerm),
	getDegreeReqsWithPreReqs(DegreeReqInTerm, CoursesTaken, PossibleDegreeReq),
	
	subset(RestOfCorCG_List, PossibleDegreeReq),
	append(RestOfCorCG_List, DefiniteCourses, CorCG_List), 
	getDefiniteClasses(CorCG_List, ClassesInSemester),
	get_credits2(CorCG_List, Max, Term, Credits, ClassesInSemester),
	Min =< Credits,
	Max >= Credits.	

%Returns the list of classes specified from a list of degree requirements 
getDefiniteClasses(CorCG_List, Classes):-
	getDefiniteClasses(CorCG_List, Classes, []).
getDefiniteClasses([degreeReq(A,none,_)|End], Classes, RunningList):-
	getDefiniteClasses(End, Classes, RunningList),
	!.
getDefiniteClasses([degreeReq(A,B,_)|End], Classes, RunningList):-
	getDefiniteClasses(End, Classes, [B|RunningList]),
	!.
getDefiniteClasses([], Classes, Classes).	

%Checks that every course inputed has taken all of its pre-reqs
check_preReqs_for_courseList([A|B], CoursesTaken):-
	course(A, Prereqs, _, _, _),
	subtract(Prereqs, CoursesTaken, CoursesNotTaken),
	length(CoursesNotTaken,0),
	check_preReqs_for_courseList(B, CoursesTaken).	
check_preReqs_for_courseList([], CoursesTaken).	

check_preReqs_for_courseList2([A|B], CoursesTaken):-
	course(A, Prereqs, _, _, _),
	eval2(Prereqs, CoursesTaken),
	check_preReqs_for_courseList2(B, CoursesTaken).	
check_preReqs_for_courseList2([], CoursesTaken).	


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
get_credits([degreeReq(A,X, _)|B], Term, Total, ClassesInSemester):-
	get_credits([degreeReq(A,X, _)|B], Term, 0 , Total, ClassesInSemester). 	
get_credits([degreeReq(A,none, _)|B], Term, Accum, Total, ClassesInSemester):-
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
get_credits([degreeReq(A,X, _)|B],Term, Accum, Total, ClassesInSemester):-	
	!,
	course(X,_,_,_,Credits),
	New is Accum+Credits,
	get_credits(B,Term, New, Total, ClassesInSemester).
get_credits([],Term, Total, Total, _).	

get_credits2([degreeReq(A,X, _)|B], Max, Term, Total, ClassesInSemester):-
	get_credits2([degreeReq(A,X, _)|B], Max, Term, 0 , Total, ClassesInSemester). 	
get_credits2([degreeReq(A,none, _)|B], Max, Term, Accum, Total, ClassesInSemester):-
	!,
	Accum =< Max,
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	list_of_classes_in_term(PossibleCGClasses, Term, Output),
	get_course_credits(Output, Sum),
	length(Output, N),
	N > 0,
	Avg is Sum/N,
	floor(Avg, Floor),
	New is Accum+Floor,
	get_credits2(B, Max, Term, New, Total, ClassesInSemester).
get_credits2([degreeReq(A,X, _)|B], Max, Term, Accum, Total, ClassesInSemester):-	
	!,
	Accum =< Max,
	course(X,_,_,_,Credits),
	New is Accum+Credits,
	get_credits2(B, Max, Term, New, Total, ClassesInSemester).
get_credits2([], Max, Term, Total, Total, _).	

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
getDegreeReqsInTerm([degreeReq(A,X, _)|B],Term,Output):-
	getDegreeReqsInTerm([degreeReq(A,X, _)|B],Term,Output, []).
getDegreeReqsInTerm([degreeReq(A,none, _)|B],Term,Output, RunningList):-
	courseGroup(A, AllClasses),
	check1ClassInTerm(AllClasses, Term),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeReq(A, none, _)|RunningList]).
getDegreeReqsInTerm([degreeReq(A,X, _)|B],Term,Output, RunningList):-
	course(X,_,_,TermList,_),
	member(Term, TermList),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeReq(A,X, _)|RunningList]).
getDegreeReqsInTerm([degreeReq(A,X,_)|B],Term, Output, RunningList):-
	getDegreeReqsInTerm(B, Term, Output, RunningList).
getDegreeReqsInTerm([], _, Output, Output).	

%Given a list of degree req 
%returns a list of all degree req that have pre-reqs ignoring course groups
getDegreeReqsWithPreReqs([], _, []).
getDegreeReqsWithPreReqs([degreeReq(A,X,_)|B], CoursesTaken, Output):-
	getDegreeReqsWithPreReqs([degreeReq(A,X,_)|B], CoursesTaken, Output, []).
%NOT CHECKIN PREREQS FOR COURSE GROUPS YET
getDegreeReqsWithPreReqs([degreeReq(A,none,_)|B], CoursesTaken, Output, RunningList):-
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeReq(A,none,_)|RunningList]).
getDegreeReqsWithPreReqs([degreeReq(A,X,_)|B], CoursesTaken, Output, RunningList):-
	check_preReqs_for_courseList2([X], CoursesTaken),
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeReq(A,X,_)|RunningList]).
getDegreeReqsWithPreReqs([degreeReq(_,_,_)|B], CoursesTaken, Output, RunningList):-	
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, RunningList).
getDegreeReqsWithPreReqs([], _, Output, Output).

%Checks that all courses & course groups are offers during a specific term
%check1ClassInTerm(PossibleCGClasses, Term)	
checkTerm([degreeReq(A,none,_)|B],Term, ClassesInSemester):-
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	list_of_classes_in_term(PossibleCGClasses, Term, Output),
	length(Output, N),
	N > 0, 
	checkTerm(B,Term, ClassesInSemester),
	!.	
checkTerm([degreeReq(A,X,_)|B],Term, ClassesInSemester):-
	course(X,_,_,TermList,_),
	!,
	member(Term, TermList),
	checkTerm(B,Term, ClassesInSemester),
	!.	
checkTerm([], _, _).

checkTerm2([degreeReq(A,none,_)|B],Term, ClassesInSemester):-
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	check1ClassInTerm(PossibleCGClasses, Term),	 
	checkTerm2(B,Term, ClassesInSemester),
	!.	
checkTerm2([degreeReq(A,X,_)|B],Term, ClassesInSemester):-
	course(X,_,_,TermList,_),
	!,
	member(Term, TermList),
	checkTerm2(B,Term, ClassesInSemester),
	!.	
checkTerm2([], _, _).	

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
	member(Term, TermList),
	true,
	!.
check1ClassInTerm([A|B], Term):-
	check1ClassInTerm(B, Term).
check1ClassInTerm([], _):-
	fail.
	
okPrereq([], Taken).
okPrereq([Course|Rest], Taken):-
	ok(Course, Taken),
	okPrereq(Rest, Taken).
ok(Course, Taken):-
	course(Course, PreReqs, _,_,_),
	eval2(PreReqs, Taken).
eval2(and(P,Q), Taken):-
	eval2(P, Taken),
	eval2(Q, Taken),
	!.
eval2(or(P,Q), Taken):-
	eval2(P, Taken),
	!.
eval2(or(P,Q),Taken):-
	eval2(Q, Taken),
	!.
eval2(C, Taken):-
	member(C, Taken).
eval2(none,_).	
	
check_coReqs_for_semester([], CoursesInSemester).
check_coReqs_for_semester([Course|Rest], CoursesInSemester):-
	okCoreq(Course, CoursesInSemester),
	check_coReqs_for_semester(Rest, CoursesInSemester).
okCoreq(Course, CoursesInSemester):-
	course(Course, _, CoReqs, _, _),
	eval2(CoReqs, CoursesInSemester).
	   	
remove_duplicates([],[]):- !.
remove_duplicates([H|T],R):- 
	member(H,T),
	remove_duplicates(T,R),
	!.
remove_duplicates([H|T],[H|Rest]):- 
	remove_duplicates(T,Rest).
	

%Sem1 : [degreeReq(sci, ch115), degreeReq(math, ma115), degreeReq(mngt, mgt111), degreeReq(csReq, cs115), degreeReq(csReq, cs146)]
%Sem2 : [degreeReq(sci, ch281), degreeReq(math, ma116), degreeReq(csReq, cs135), degreeReq(csReq, cs284), degreeReq(techElect, cs105)]
%Sem3 : [degreeReq(csReq, cs334), degreeReq(csReq, cs383), degreeReq(csReq, cs385), degreeReq(csReq, cs506), degreeReq(techElect, none)]
%Sem4 : [degreeReq(math, ma222), degreeReq(csReq, cs347), degreeReq(csReq, cs392), degreeReq(csReq, cs496), degreeReq(csReq, cs492), degreeReq(softwareDevElective, none)]
%Sem5 : [degreeReq(math, ma331), degreeReq(csReq, cs442), degreeReq(csReq, cs511), degreeReq(csReq, cs423), degreeReq(mathScienceElective, none), degreeReq(mathScienceElective, none)]
%Sem6 : [degreeReq(csReq, cs488), degreeReq(csReq, cs424), degreeReq(freeElective, none), degreeReq(freeElective, none)]
%Sem7 : [degreeReq(humGroupA, none), degreeReq(humGroupA, none), degreeReq(humGroupB, none), degreeReq(humRequiredClass, hss371)]
%Sem8 : [degreeReq(humGroupB, none), degreeReq(hum300400, none), degreeReq(hum300400, none), degreeReq(hum300400, none)]
testBlankSchedule:-
	okDegree([
		semesterNew(fall, 2009, 12, 18, Sem1, []),
		semesterNew(spring, 2010, 12, 18, Sem2, []),
		semesterNew(fall, 2010, 12, 18, Sem3, []),
		semesterNew(spring, 2011, 12, 18, Sem4, []),
		semesterNew(fall, 2011, 12, 18, Sem5, []),
		semesterNew(spring, 2012, 12, 18, Sem6, []),
		semesterNew(fall, 2012, 12, 18, Sem7, []),
		semesterNew(spring, 2013, 12, 18, Sem8, [])
		],[
		degreeReq(sci, ch115,[]),
		degreeReq(sci, ch281, []),

		degreeReq(math, ma115, []),
		degreeReq(math, ma116, []),
		degreeReq(math, ma222, []),
		degreeReq(math, ma331, []),

		degreeReq(mngt, mgt111, []),

		degreeReq(csReq, cs115, []),
		degreeReq(csReq, cs146, []),
		degreeReq(csReq, cs135, []),
		degreeReq(csReq, cs284, []),
		degreeReq(csReq, cs334, []),
		degreeReq(csReq, cs383, []),
		degreeReq(csReq, cs385, []),
		degreeReq(csReq, cs347, []),
		degreeReq(csReq, cs392, []),
		degreeReq(csReq, cs496, []),
		degreeReq(csReq, cs442, []),
		degreeReq(csReq, cs511, []),
		degreeReq(csReq, cs488, []),
		degreeReq(csReq, cs492, []),
		degreeReq(csReq, cs506, []),
		degreeReq(csReq, cs423, []),
		degreeReq(csReq, cs424, []),

		degreeReq(techElect, cs105, []),
		degreeReq(techElect, none, A),

		degreeReq(softwareDevElective, none, B),

		degreeReq(mathScienceElective, none, C),
		degreeReq(mathScienceElective, none, D),

		degreeReq(freeElective, none, E),
		degreeReq(freeElective, none, F),

		degreeReq(humGroupA, none, G),
		degreeReq(humGroupA, none, H),

		degreeReq(humGroupB, none, I),
		degreeReq(humGroupB, none, J),

		degreeReq(humRequiredClass, hss371, []),

		degreeReq(hum300400, none, K),
		degreeReq(hum300400, none, L),
		degreeReq(hum300400, none, M)
		], 
		[]),	
	writeln("Sem1":Sem1),
	writeln("Sem2":Sem2),
	writeln("Sem3":Sem3),
	writeln("Sem4":Sem4),
	writeln("Sem5":Sem5),
	writeln("Sem6":Sem6),
	writeln("Sem7":Sem7),
	writeln("Sem8":Sem8).

%Sem1 : [degreeReq(sci, ch115), degreeReq(math, ma115), degreeReq(mngt, mgt111), degreeReq(csReq, cs146)]
%Sem2 : [degreeReq(sci, ch281), degreeReq(math, ma116), degreeReq(csReq, cs115), degreeReq(csReq, cs135)]
%Sem3 : [degreeReq(csReq, cs284), degreeReq(csReq, cs334), degreeReq(csReq, cs383), degreeReq(csReq, cs506), degreeReq(techElect, none)]
%Sem4 : [degreeReq(csReq, cs385), degreeReq(csReq, cs347), degreeReq(csReq, cs496), degreeReq(softwareDevElective, none)]
%Sem5 : [degreeReq(math, ma331), degreeReq(csReq, cs392), degreeReq(csReq, cs442), degreeReq(csReq, cs511), degreeReq(mathScienceElective, none), degreeReq(mathScienceElective, none)]
%Sem6 : [degreeReq(csReq, cs488), degreeReq(csReq, cs492), degreeReq(freeElective, none), degreeReq(freeElective, none)]
%Sem7 : [degreeReq(humGroupA, none), degreeReq(humGroupB, none), degreeReq(humRequiredClass, hss371)]
%Sem8 : [degreeReq(hum300400, none), degreeReq(hum300400, none), degreeReq(hum300400, none)]

testWithCoursePreference:-
	okDegree([
		semesterNew(fall, 2009, 12, 18, Sem1, [degreeReq(humGroupA, none, A),degreeReq(techElect, cs105, [])]),
		semesterNew(spring, 2010, 12, 18, Sem2, [degreeReq(humGroupB, none, B)]),
		semesterNew(fall, 2010, 12, 18, Sem3, []),
		semesterNew(spring, 2011, 12, 18, Sem4, [degreeReq(math, ma222, [])]),
		semesterNew(fall, 2011, 12, 18, Sem5, []),
		semesterNew(spring, 2012, 12, 18, Sem6, []),
		semesterNew(fall, 2012, 12, 18, Sem7, [degreeReq(csReq, cs423, [])]),
		semesterNew(spring, 2013, 12, 18, Sem8, [degreeReq(csReq, cs424, [])])
		],[
		degreeReq(sci, ch115, []),
		degreeReq(sci, ch281, []),

		degreeReq(math, ma115, []),
		degreeReq(math, ma116, []),

		degreeReq(math, ma331, []),

		degreeReq(mngt, mgt111, []),

		degreeReq(csReq, cs115, []),
		degreeReq(csReq, cs146, []),
		degreeReq(csReq, cs135, []),
		degreeReq(csReq, cs284, []),
		degreeReq(csReq, cs334, []),
		degreeReq(csReq, cs383, []),
		degreeReq(csReq, cs385, []),
		degreeReq(csReq, cs347, []),
		degreeReq(csReq, cs392, []),
		degreeReq(csReq, cs496, []),
		degreeReq(csReq, cs442, []),
		degreeReq(csReq, cs511, []),
		degreeReq(csReq, cs488, []),
		degreeReq(csReq, cs492, []),
		degreeReq(csReq, cs506, []),

		degreeReq(techElect, none, C),

		degreeReq(softwareDevElective, none, D),

		degreeReq(mathScienceElective, none, E),
		degreeReq(mathScienceElective, none, F),

		degreeReq(freeElective, none, G),
		degreeReq(freeElective, none, H),

		degreeReq(humGroupA, none, I),

		degreeReq(humGroupB, none, J),

		degreeReq(humRequiredClass, hss371, []),

		degreeReq(hum300400, none, K),
		degreeReq(hum300400, none, L),
		degreeReq(hum300400, none, M)
		], 
		[]),
	writeln("Sem1":Sem1),
	writeln("Sem2":Sem2),
	writeln("Sem3":Sem3),
	writeln("Sem4":Sem4),
	writeln("Sem5":Sem5),
	writeln("Sem6":Sem6),
	writeln("Sem7":Sem7),
	writeln("Sem8":Sem8).

%Sem3 : [degreeReq(csReq, cs284), degreeReq(csReq, cs334), degreeReq(csReq, cs383), degreeReq(csReq, cs506), degreeReq(techElect, none)]
%Sem4 : [degreeReq(math, ma222), degreeReq(csReq, cs385), degreeReq(csReq, cs347), degreeReq(csReq, cs496), degreeReq(softwareDevElective, none)]
%Sem5 : [degreeReq(math, ma331), degreeReq(csReq, cs392), degreeReq(csReq, cs442), degreeReq(csReq, cs511), degreeReq(csReq, cs423), degreeReq(mathScienceElective, none)]
%Sem6 : [degreeReq(csReq, cs488), degreeReq(csReq, cs492), degreeReq(csReq, cs424), degreeReq(mathScienceElective, none), degreeReq(freeElective, none)]
%Sem7 : [degreeReq(freeElective, none), degreeReq(humGroupA, none), degreeReq(humGroupB, none), degreeReq(humRequiredClass, hss371)]
%Sem8 : [degreeReq(humGroupB, none), degreeReq(hum300400, none), degreeReq(hum300400, none), degreeReq(hum300400, none)]	
testAlreadyTookCourses:-
	okDegree([
		semesterNew(fall, 2010, 12, 18, Sem3, []),
		semesterNew(spring, 2011, 12, 18, Sem4, []),
		semesterNew(fall, 2011, 12, 18, Sem5, []),
		semesterNew(spring, 2012, 12, 18, Sem6, []),
		semesterNew(fall, 2012, 12, 18, Sem7, []),
		semesterNew(spring, 2013, 12, 18, Sem8, [])
		],[
		degreeReq(math, ma222, []),
		degreeReq(math, ma331, []),
		degreeReq(csReq, cs284, []),
		degreeReq(csReq, cs334, []),
		degreeReq(csReq, cs383, []),
		degreeReq(csReq, cs385, []),
		degreeReq(csReq, cs347, []),
		degreeReq(csReq, cs392, []),
		degreeReq(csReq, cs496, []),
		degreeReq(csReq, cs442, []),
		degreeReq(csReq, cs511, []),
		degreeReq(csReq, cs488, []),
		degreeReq(csReq, cs492, []),
		degreeReq(csReq, cs506, []),
		degreeReq(csReq, cs423, []),
		degreeReq(csReq, cs424, []),
		degreeReq(techElect, none, A),
		degreeReq(softwareDevElective, none, B),
		degreeReq(mathScienceElective, none, C),
		degreeReq(mathScienceElective, none, D),
		degreeReq(freeElective, none, E),
		degreeReq(freeElective, none, F),
		degreeReq(humGroupA, none, G),
		degreeReq(humGroupB, none, H),
		degreeReq(humGroupB, none, I),
		degreeReq(humRequiredClass, hss371, []),
		degreeReq(hum300400, none, J),
		degreeReq(hum300400, none, K),
		degreeReq(hum300400, none, L)], 
		[ch115, ch281, ma115, ma116, mgt111, cs105, cs115, hli111, cs146, cs135]),
	writeln("Sem3":Sem3),
	writeln("Sem4":Sem4),
	writeln("Sem5":Sem5),
	writeln("Sem6":Sem6),
	writeln("Sem7":Sem7),
	writeln("Sem8":Sem8).

