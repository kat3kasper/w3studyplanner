%semester(ID,Term, Year, Min, Max, Courses).
semester(s1, fall, 2013, 12, 18, [cs105, hum101, pep111, pe100, ma115]).
semester(s2, spring, 2013, 12, 18, [cs115, hum102, pep112, ma116]).
semester(s3, fall, 2014, 0, 15, []).

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

course(cs115, [cs105], [], [fall,spring],3).
course(hum102, [], [], [spring], 3).
course(pep112, [pep111], [], [spring], 3).
course(ma116, [ma115], [], [fall, spring],4).

classes_in_semester(semester(_,_,_,_,[A|B]), ClassList):-
		classes_in_semester(semester(_,_,_,_,[A|B]),ClassList, []).
		
classes_in_semester(semester(_,_,_,_,[A|B]), ClassList, List):-
	course(A,_,_,_,_),
	classes_in_semester(semester(_,_,_,_,B),ClassList, [A|List]).	

classes_in_semester(semester(_,_,_,_,[]),ClassList, ClassList):-
	writeln("Classes in semester":ClassList).	
	
credits_in_semester(semester(_,_,_,_,[A|B]), ClassList, Credits):-
	course(A,_,_,_,_),
	credits_in_semester(semester(_,_,_,_,B),[A|ClassList], Credits).

credits_in_semester(semester(_,_,_,_,[]),ClassList, Credits):-
	writeln("Classes in semester":ClassList),
	get_cumul_credits(ClassList, 0, Credits),
	writeln("Number of credits":Credits).

get_courseList_credits(CourseList, Total):-
	get_cumul_credits(CourseList, 0 , Total). 
	
get_cumul_credits([A|B],Accum, Total):-
	course(A,_,_,_,Credits),
	writeln(Credits),
	New is Accum+Credits,
	get_cumul_credits(B, New, Total).

get_cumul_credits([], Total, Total).

correct_term([A|B], Term):-
	course(A,_,_,TermList,_),
	member(Term, TermList),
	correct_term(B, Term).

correct_term([],Term).

possible_semester(semester(Term, Year, Min, Max, Courses)):-
	%credits_in_semester(semester(Term, Year, Min, Max, Courses), [], Credits),
	classes_in_semester(semester(_,_,_,_,Courses), ClassList),
	get_courseList_credits(ClassList, Credits),
	writeln("CLASS LIST":ClassList),
	writeln("CREDDITS":Credits),
	Min < Credits,
	Max > Credits,
	correct_term(ClassList, Term).
	
possible_semester2(semester(Id, Term, Year, Min, Max, Courses)):-
	get_courseList_credits(Courses, Credits),
	writeln("CREDDITS":Credits),
	Min =< Credits,
	Max >= Credits,
	correct_term(Courses, Term).		
	
check_hardcoded_semesters:-
	findall(Id, semesters(Id), SemesterIds),
	writeln("Semester IDS":SemesterIds),
	allowed_semester_list(SemesterIds).
	
allowed_semester_list(SemesterIds):-
	allowed_semester_list(SemesterIds, []).
	
allowed_semester_list([A|B], CoursesTaken):-
	semester(A,Term,Year,Min,Max,Courses),
	possible_semester2(semester(A,Term, Year, Min, Max, Courses)),
	check_preReqs_for_courseList(Courses, CoursesTaken),
	flatten([Courses|CoursesTaken], Taken),
	allowed_semester_list(B, Taken).
	
allowed_semester_list([], CoursesTaken).	
	
check_preReqs_for_courseList([A|B], CoursesTaken):-
	course(A, Prereqs, _, _, _),
	prereqs_taken(Prereqs, CoursesTaken),
	check_preReqs_for_courseList(B, CoursesTaken).
check_preReqs_for_courseList([], CoursesTaken).	
	
prereqs_taken(Prereqs, CoursesTaken):-
	subtract(Prereqs, CoursesTaken, CoursesNotTaken),
	length(CoursesNotTaken,0).

semesters(Id):-
	semester(Id,_,_,_,_,_).
	
%updated for the updated form of semesters but isn't used	
classes_in_semester2(semester(_,_,_,_,_,[A|B]), ClassList):-
		classes_in_semester2(semester(_,_,_,_,_,[A|B]),ClassList, []).
		
classes_in_semester2(semester(_,_,_,_,_,[A|B]), ClassList, List):-
	course(A,_,_,_,_),
	classes_in_semester2(semester(_,_,_,_,_,B),ClassList, [A|List]).	

classes_in_semester2(semester(_,_,_,_,_,[]),ClassList, ClassList):-
	writeln("Classes in semester":ClassList).	
	


	
