% load coursegroup information (includes course info)
:- ensure_loaded(courseGroup).

%Determines if a list of semesters is a valid degree given the degree requirements
okDegree([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], DegreeReq):-
	okDegree([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], DegreeReq, []).	
okDegree([], [], _).
okDegree([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], DegreeReq, CoursesTaken):-	
	validateInput([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests]),
	getDefiniteClasses(DegreeReq, Classes1),
	getAllCoursePreferences([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], AllCoursePreferences),
	flatten([Classes1|AllCoursePreferences],AllCoursesBeingTaken), 
	constructSchedule([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], DegreeReq, CoursesTaken, AllCoursesBeingTaken,[]).

getAllCoursePreferences([semester(_, _, _, _, _, CoursePreferences)|RestOfSemests], AllCoursePreferences):-
	getAllCoursePreferences([semester(_, _, _, _, _, CoursePreferences)|RestOfSemests], AllCoursePreferences, []).
getAllCoursePreferences([], AllCoursePreferences, AllCoursePreferences).
getAllCoursePreferences([semester(_, _, _, _, _, CoursePreferences)|RestOfSemests], AllCoursePreferences, Accum):-
	getDefiniteClasses(CoursePreferences, Courses),
	flatten([Courses|Accum], RunningCourseList),
	getAllCoursePreferences(RestOfSemests, AllCoursePreferences, RunningCourseList).
	
%Validates that courses specified for a specific semester are offered during that term
%Should be valid already via website, but double checks	
validateInput([]).
validateInput([semester(Term, _, _, _, _, CoursePreferences)|RestOfSemests]):-
	checkTerm(CoursePreferences, Term, []),
	validateInput(RestOfSemests).

%** Remove writeln
%Function that attempts build subsequent semesters	
constructSchedule([], [], _, _,_).
constructSchedule([semester(Term, Year, Min, Max, CorCG, CoursePreferences)|RestOfSemests], DegreeReq, CoursesTaken, AllCoursesBeingTaken, DegreeReqsTaken):-	
	possible_semester(Term, Year, Min, Max, CorCG, CoursePreferences, DegreeReq, CoursesTaken),
	append(CorCG, CoursePreferences, CorCG_FullList),
	getDefiniteClasses(CorCG_FullList, ClassesBeingTaken),
	check_preReqs_for_courseList2(ClassesBeingTaken, CoursesTaken),
	append(ClassesBeingTaken, CoursesTaken, AllCoReqPossibilities),
	check_coReqs_for_semester(ClassesBeingTaken, AllCoReqPossibilities),
	append(AllCoursesBeingTaken, CoursesTaken, UnavailableCourses),
	determinePossibleClassesForCourseGroup(CorCG, Term, UnavailableCourses),
	subtract_custom(DegreeReq, CorCG, CourseGroupsLeft),
	flatten([ClassesBeingTaken|CoursesTaken], Taken),
	flatten([CorCG|DegreeReqsTaken], DegreeReqsBeingTaken),
	writeln("Degree Reqs": DegreeReqsBeingTaken),
	verifySameCGHaveEnoughCourses(CorCG, DegreeReqsBeingTaken),
	constructSchedule(RestOfSemests, CourseGroupsLeft, Taken, AllCoursesBeingTaken, DegreeReqsBeingTaken).	

determinePossibleClassesForCourseGroup([degreeRequirement(A, none, Courses)|Rest], Term, AllCoursesBeingTaken):-
	courseGroup(A, AllClasses),
	subtract(AllClasses, AllCoursesBeingTaken, ClassesSub1),
	list_of_classes_in_term(ClassesSub1, Term, Courses),
	!,
	determinePossibleClassesForCourseGroup(Rest, Term, AllCoursesBeingTaken).
determinePossibleClassesForCourseGroup([degreeRequirement(_,_,_)|Rest], Term, AllCoursesBeingTaken):-	
	determinePossibleClassesForCourseGroup(Rest, Term, AllCoursesBeingTaken).
determinePossibleClassesForCourseGroup([], _, _).	

verifySameCGHaveEnoughCourses([],_).
verifySameCGHaveEnoughCourses([degreeRequirement(A, none, _)|Rest], AllDegreeReqs):-
	findall(degreeRequirement(A, none, X), member(degreeRequirement(A, none, X), AllDegreeReqs), MultipleDegreeReqList),
	findall(X, member(degreeRequirement(A, none, X), MultipleDegreeReqList), CoursesForCourseGroupWDups),
	!,
	flatten(CoursesForCourseGroupWDups, FlattenedCourseList),
	remove_duplicates(FlattenedCourseList,CoursesForCourseGroup), 
	length(MultipleDegreeReqList, NumberOfSameDegreeReqs),
	length(CoursesForCourseGroup, NumberOfAvailableCourses),
	NumberOfSameDegreeReqs =< NumberOfAvailableCourses,
	verifySameCGHaveEnoughCourses(Rest, AllDegreeReqs).	
verifySameCGHaveEnoughCourses([degreeRequirement(_,_,_)|Rest], AllDegreeReqs):-
	verifySameCGHaveEnoughCourses(Rest, AllDegreeReqs).

	
%Determines if a semester made up of courses & course groups is valid	
possible_semester(Term, _, Min, Max, RestOfCorCG_List, CoursePreferences, DegreeReq, CoursesTaken):-
	checkTerm(CoursePreferences, Term, []),
	!,
	getDegreeReqsInTerm(DegreeReq, Term, DegreeReqInTerm),
	getDegreeReqsWithPreReqs(DegreeReqInTerm, CoursesTaken, PossibleDegreeReq),	
	subset(RestOfCorCG_List, PossibleDegreeReq),
	append(RestOfCorCG_List, CoursePreferences, CorCG_List), 
	getDefiniteClasses(CorCG_List, ClassesInSemester),
	get_credits(CorCG_List, Max, Term, Credits, ClassesInSemester),
	Min =< Credits,
	Max >= Credits.	

%Returns the list of classes specified from a list of degree requirements 
getDefiniteClasses(CorCG_List, Classes):-
	getDefiniteClasses(CorCG_List, Classes, []).
getDefiniteClasses([degreeRequirement(_,none,_)|End], Classes, RunningList):-
	getDefiniteClasses(End, Classes, RunningList),
	!.
getDefiniteClasses([degreeRequirement(_,B,_)|End], Classes, RunningList):-
	getDefiniteClasses(End, Classes, [B|RunningList]),
	!.
getDefiniteClasses([], Classes, Classes).	

%Checks that every course inputed has taken all of its pre-reqs
check_preReqs_for_courseList2([A|B], CoursesTaken):-
	course(A, Prereqs, _, _, _),
	eval2(Prereqs, CoursesTaken),
	check_preReqs_for_courseList2(B, CoursesTaken).	
check_preReqs_for_courseList2([], _).	


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
get_credits([degreeRequirement(A,X, _)|B], Max, Term, Total, ClassesInSemester):-
	get_credits([degreeRequirement(A,X, _)|B], Max, Term, 0 , Total, ClassesInSemester). 	
get_credits([degreeRequirement(A,none, _)|B], Max, Term, Accum, Total, ClassesInSemester):-
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
	get_credits(B, Max, Term, New, Total, ClassesInSemester).
get_credits([degreeRequirement(_,X, _)|B], Max, Term, Accum, Total, ClassesInSemester):-	
	!,
	Accum =< Max,
	course(X,_,_,_,Credits),
	New is Accum+Credits,
	get_credits(B, Max, Term, New, Total, ClassesInSemester).
get_credits([], _, _, Total, Total, _).	

%gets the number of credits for a list of courses
get_course_credits(CourseList, Total):-
	get_course_credits(CourseList, 0 , Total).	
get_course_credits([A|B],Accum, Total):-
	course(A,_,_,_,Credits),
	New is Accum+Credits,
	get_course_credits(B, New, Total).
get_course_credits([], Total, Total).

%Returns a list of all degree requirements that are able to be taken for a specific term
getDegreeReqsInTerm([], _, []).
getDegreeReqsInTerm([degreeRequirement(A,X, _)|B],Term,Output):-
	getDegreeReqsInTerm([degreeRequirement(A,X, _)|B],Term,Output, []).
getDegreeReqsInTerm([degreeRequirement(A,none, _)|B],Term,Output, RunningList):-
	courseGroup(A, AllClasses),
	check1ClassInTerm(AllClasses, Term),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeRequirement(A, none, _)|RunningList]).
getDegreeReqsInTerm([degreeRequirement(A,X, _)|B],Term,Output, RunningList):-
	course(X,_,_,TermList,_),
	member(Term, TermList),
	!,
	getDegreeReqsInTerm(B, Term, Output, [degreeRequirement(A,X, _)|RunningList]).
getDegreeReqsInTerm([degreeRequirement(_,_,_)|B],Term, Output, RunningList):-
	getDegreeReqsInTerm(B, Term, Output, RunningList).
getDegreeReqsInTerm([], _, Output, Output).	

%Given a list of degree req 
%returns a list of all degree req that have pre-reqs ignoring course groups
getDegreeReqsWithPreReqs([], _, []).
getDegreeReqsWithPreReqs([degreeRequirement(A,X,_)|B], CoursesTaken, Output):-
	getDegreeReqsWithPreReqs([degreeRequirement(A,X,_)|B], CoursesTaken, Output, []).
%NOT CHECKIN PREREQS FOR COURSE GROUPS YET
getDegreeReqsWithPreReqs([degreeRequirement(A,none,_)|B], CoursesTaken, Output, RunningList):-
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeRequirement(A,none,_)|RunningList]).
getDegreeReqsWithPreReqs([degreeRequirement(A,X,_)|B], CoursesTaken, Output, RunningList):-
	check_preReqs_for_courseList2([X], CoursesTaken),
	!,
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, [degreeRequirement(A,X,_)|RunningList]).
getDegreeReqsWithPreReqs([degreeRequirement(_,_,_)|B], CoursesTaken, Output, RunningList):-	
	getDegreeReqsWithPreReqs(B, CoursesTaken, Output, RunningList).
getDegreeReqsWithPreReqs([], _, Output, Output).

%Checks that all courses & course groups are offers during a specific term
checkTerm([degreeRequirement(A,none,_)|B],Term, ClassesInSemester):-
	courseGroup(A, AllClasses),
	subtract(AllClasses, ClassesInSemester, PossibleCGClasses),
	check1ClassInTerm(PossibleCGClasses, Term),	 
	checkTerm(B,Term, ClassesInSemester),
	!.	
checkTerm([degreeRequirement(_,X,_)|B],Term, ClassesInSemester):-
	course(X,_,_,TermList,_),
	!,
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
list_of_classes_in_term([_|B], Term, Output, RunningList):-
	list_of_classes_in_term(B, Term, Output, RunningList).
list_of_classes_in_term([], _, Output, Output).		

%Determines if 1 class in a list is available during a specific term
check1ClassInTerm([A|_], Term):-
	course(A,_,_,TermList,_),
	member(Term, TermList),
	true,
	!.
check1ClassInTerm([_|B], Term):-
	check1ClassInTerm(B, Term).
check1ClassInTerm([], _):-
	fail.
	
okPrereq([], _).
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
eval2(or(P,_), Taken):-
	eval2(P, Taken),
	!.
eval2(or(_,Q),Taken):-
	eval2(Q, Taken),
	!.
eval2(C, Taken):-
	member(C, Taken).
eval2(none,_).	
	
check_coReqs_for_semester([], _).
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
	

%Sem1 : [degreeRequirement(sci, ch115), degreeRequirement(math, ma115), degreeRequirement(mngt, mgt111), degreeRequirement(csReq, cs115), degreeRequirement(csReq, cs146)]
%Sem2 : [degreeRequirement(sci, ch281), degreeRequirement(math, ma116), degreeRequirement(csReq, cs135), degreeRequirement(csReq, cs284), degreeRequirement(techElect, cs105)]
%Sem3 : [degreeRequirement(csReq, cs334), degreeRequirement(csReq, cs383), degreeRequirement(csReq, cs385), degreeRequirement(csReq, cs506), degreeRequirement(techElect, none)]
%Sem4 : [degreeRequirement(math, ma222), degreeRequirement(csReq, cs347), degreeRequirement(csReq, cs392), degreeRequirement(csReq, cs496), degreeRequirement(csReq, cs492), degreeRequirement(softwareDevElective, none)]
%Sem5 : [degreeRequirement(math, ma331), degreeRequirement(csReq, cs442), degreeRequirement(csReq, cs511), degreeRequirement(csReq, cs423), degreeRequirement(mathScienceElective, none), degreeRequirement(mathScienceElective, none)]
%Sem6 : [degreeRequirement(csReq, cs488), degreeRequirement(csReq, cs424), degreeRequirement(freeElective, none), degreeRequirement(freeElective, none)]
%Sem7 : [degreeRequirement(humGroupA, none), degreeRequirement(humGroupA, none), degreeRequirement(humGroupB, none), degreeRequirement(humRequiredClass, hss371)]
%Sem8 : [degreeRequirement(humGroupB, none), degreeRequirement(hum300400, none), degreeRequirement(hum300400, none), degreeRequirement(hum300400, none)]
testBlankSchedule:-
	okDegree([
		semester(fall, 2009, 12, 18, Sem1, []),
		semester(spring, 2010, 12, 18, Sem2, []),
		semester(fall, 2010, 12, 18, Sem3, []),
		semester(spring, 2011, 12, 18, Sem4, []),
		semester(fall, 2011, 12, 18, Sem5, []),
		semester(spring, 2012, 12, 18, Sem6, []),
		semester(fall, 2012, 12, 18, Sem7, []),
		semester(spring, 2013, 12, 18, Sem8, [])
		],[
		degreeRequirement(sci, ch115,[]),
		degreeRequirement(sci, ch281, []),

		degreeRequirement(math, ma115, []),
		degreeRequirement(math, ma116, []),
		degreeRequirement(math, ma222, []),
		degreeRequirement(math, ma331, []),

		degreeRequirement(mngt, mgt111, []),

		degreeRequirement(csReq, cs115, []),
		degreeRequirement(csReq, cs146, []),
		degreeRequirement(csReq, cs135, []),
		degreeRequirement(csReq, cs284, []),
		degreeRequirement(csReq, cs334, []),
		degreeRequirement(csReq, cs383, []),
		degreeRequirement(csReq, cs385, []),
		degreeRequirement(csReq, cs347, []),
		degreeRequirement(csReq, cs392, []),
		degreeRequirement(csReq, cs496, []),
		degreeRequirement(csReq, cs442, []),
		degreeRequirement(csReq, cs511, []),
		degreeRequirement(csReq, cs488, []),
		degreeRequirement(csReq, cs492, []),
		degreeRequirement(csReq, cs506, []),
		degreeRequirement(csReq, cs423, []),
		degreeRequirement(csReq, cs424, []),

		degreeRequirement(techElect, cs105, []),
		degreeRequirement(techElect, none, A),

		degreeRequirement(softwareDevElective, none, B),

		degreeRequirement(mathScienceElective, none, C),
		degreeRequirement(mathScienceElective, none, D),

		degreeRequirement(freeElective, none, E),
		degreeRequirement(freeElective, none, F),

		degreeRequirement(humGroupA, none, G),
		degreeRequirement(humGroupA, none, H),

		degreeRequirement(humGroupB, none, I),
		degreeRequirement(humGroupB, none, J),

		degreeRequirement(humRequiredClass, hss371, []),

		degreeRequirement(hum300400, none, K),
		degreeRequirement(hum300400, none, L),
		degreeRequirement(hum300400, none, M)
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

%Sem1 : [degreeRequirement(sci, ch115), degreeRequirement(math, ma115), degreeRequirement(mngt, mgt111), degreeRequirement(csReq, cs146)]
%Sem2 : [degreeRequirement(sci, ch281), degreeRequirement(math, ma116), degreeRequirement(csReq, cs115), degreeRequirement(csReq, cs135)]
%Sem3 : [degreeRequirement(csReq, cs284), degreeRequirement(csReq, cs334), degreeRequirement(csReq, cs383), degreeRequirement(csReq, cs506), degreeRequirement(techElect, none)]
%Sem4 : [degreeRequirement(csReq, cs385), degreeRequirement(csReq, cs347), degreeRequirement(csReq, cs496), degreeRequirement(softwareDevElective, none)]
%Sem5 : [degreeRequirement(math, ma331), degreeRequirement(csReq, cs392), degreeRequirement(csReq, cs442), degreeRequirement(csReq, cs511), degreeRequirement(mathScienceElective, none), degreeRequirement(mathScienceElective, none)]
%Sem6 : [degreeRequirement(csReq, cs488), degreeRequirement(csReq, cs492), degreeRequirement(freeElective, none), degreeRequirement(freeElective, none)]
%Sem7 : [degreeRequirement(humGroupA, none), degreeRequirement(humGroupB, none), degreeRequirement(humRequiredClass, hss371)]
%Sem8 : [degreeRequirement(hum300400, none), degreeRequirement(hum300400, none), degreeRequirement(hum300400, none)]

testWithCoursePreference:-
	okDegree([
		semester(fall, 2009, 12, 18, Sem1, [degreeRequirement(humGroupA, none, A),degreeRequirement(techElect, cs105, [])]),
		semester(spring, 2010, 12, 18, Sem2, [degreeRequirement(humGroupB, none, B)]),
		semester(fall, 2010, 12, 18, Sem3, []),
		semester(spring, 2011, 12, 18, Sem4, [degreeRequirement(math, ma222, [])]),
		semester(fall, 2011, 12, 18, Sem5, []),
		semester(spring, 2012, 12, 18, Sem6, []),
		semester(fall, 2012, 12, 18, Sem7, [degreeRequirement(csReq, cs423, [])]),
		semester(spring, 2013, 12, 18, Sem8, [degreeRequirement(csReq, cs424, [])])
		],[
		degreeRequirement(sci, ch115, []),
		degreeRequirement(sci, ch281, []),

		degreeRequirement(math, ma115, []),
		degreeRequirement(math, ma116, []),

		degreeRequirement(math, ma331, []),

		degreeRequirement(mngt, mgt111, []),

		degreeRequirement(csReq, cs115, []),
		degreeRequirement(csReq, cs146, []),
		degreeRequirement(csReq, cs135, []),
		degreeRequirement(csReq, cs284, []),
		degreeRequirement(csReq, cs334, []),
		degreeRequirement(csReq, cs383, []),
		degreeRequirement(csReq, cs385, []),
		degreeRequirement(csReq, cs347, []),
		degreeRequirement(csReq, cs392, []),
		degreeRequirement(csReq, cs496, []),
		degreeRequirement(csReq, cs442, []),
		degreeRequirement(csReq, cs511, []),
		degreeRequirement(csReq, cs488, []),
		degreeRequirement(csReq, cs492, []),
		degreeRequirement(csReq, cs506, []),

		degreeRequirement(techElect, none, C),

		degreeRequirement(softwareDevElective, none, D),

		degreeRequirement(mathScienceElective, none, E),
		degreeRequirement(mathScienceElective, none, F),

		degreeRequirement(freeElective, none, G),
		degreeRequirement(freeElective, none, H),

		degreeRequirement(humGroupA, none, I),

		degreeRequirement(humGroupB, none, J),

		degreeRequirement(humRequiredClass, hss371, []),

		degreeRequirement(hum300400, none, K),
		degreeRequirement(hum300400, none, L),
		degreeRequirement(hum300400, none, M)
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

%Sem3 : [degreeRequirement(csReq, cs284), degreeRequirement(csReq, cs334), degreeRequirement(csReq, cs383), degreeRequirement(csReq, cs506), degreeRequirement(techElect, none)]
%Sem4 : [degreeRequirement(math, ma222), degreeRequirement(csReq, cs385), degreeRequirement(csReq, cs347), degreeRequirement(csReq, cs496), degreeRequirement(softwareDevElective, none)]
%Sem5 : [degreeRequirement(math, ma331), degreeRequirement(csReq, cs392), degreeRequirement(csReq, cs442), degreeRequirement(csReq, cs511), degreeRequirement(csReq, cs423), degreeRequirement(mathScienceElective, none)]
%Sem6 : [degreeRequirement(csReq, cs488), degreeRequirement(csReq, cs492), degreeRequirement(csReq, cs424), degreeRequirement(mathScienceElective, none), degreeRequirement(freeElective, none)]
%Sem7 : [degreeRequirement(freeElective, none), degreeRequirement(humGroupA, none), degreeRequirement(humGroupB, none), degreeRequirement(humRequiredClass, hss371)]
%Sem8 : [degreeRequirement(humGroupB, none), degreeRequirement(hum300400, none), degreeRequirement(hum300400, none), degreeRequirement(hum300400, none)]	
testAlreadyTookCourses:-
	okDegree([
		semester(fall, 2010, 12, 18, Sem3, []),
		semester(spring, 2011, 12, 18, Sem4, []),
		semester(fall, 2011, 12, 18, Sem5, []),
		semester(spring, 2012, 12, 18, Sem6, []),
		semester(fall, 2012, 12, 18, Sem7, []),
		semester(spring, 2013, 12, 18, Sem8, [])
		],[
		degreeRequirement(math, ma222, []),
		degreeRequirement(math, ma331, []),
		degreeRequirement(csReq, cs284, []),
		degreeRequirement(csReq, cs334, []),
		degreeRequirement(csReq, cs383, []),
		degreeRequirement(csReq, cs385, []),
		degreeRequirement(csReq, cs347, []),
		degreeRequirement(csReq, cs392, []),
		degreeRequirement(csReq, cs496, []),
		degreeRequirement(csReq, cs442, []),
		degreeRequirement(csReq, cs511, []),
		degreeRequirement(csReq, cs488, []),
		degreeRequirement(csReq, cs492, []),
		degreeRequirement(csReq, cs506, []),
		degreeRequirement(csReq, cs423, []),
		degreeRequirement(csReq, cs424, []),
		degreeRequirement(techElect, none, A),
		degreeRequirement(softwareDevElective, none, B),
		degreeRequirement(mathScienceElective, none, C),
		degreeRequirement(mathScienceElective, none, D),
		degreeRequirement(freeElective, none, E),
		degreeRequirement(freeElective, none, F),
		degreeRequirement(humGroupA, none, G),
		degreeRequirement(humGroupB, none, H),
		degreeRequirement(humGroupB, none, I),
		degreeRequirement(humRequiredClass, hss371, []),
		degreeRequirement(hum300400, none, J),
		degreeRequirement(hum300400, none, K),
		degreeRequirement(hum300400, none, L)], 
		[ch115, ch281, ma115, ma116, mgt111, cs105, cs115, hli111, cs146, cs135]),
	writeln("Sem3":Sem3),
	writeln("Sem4":Sem4),
	writeln("Sem5":Sem5),
	writeln("Sem6":Sem6),
	writeln("Sem7":Sem7),
	writeln("Sem8":Sem8).
	
