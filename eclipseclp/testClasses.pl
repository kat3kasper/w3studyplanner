top:-
	get_all_classes,
	get_classes_with_no_preReqs,
	get_classes_with_preReqs.

newtryFor5:-
	findall(Number,get_all_classes(Number),List),
	writeln(List),
	Count is 5, 
	tryFor5(List, Count, TakeList),
	writeln(TakeList).
	

tryFor5(List, Count, TakeList):-
	tryFor5(List, Count, TakeList, []).

tryFor5([H|T], Count, TakeList, A):-
		Count>0,
		tryFor5(T, Count-1, TakeList, [H|A]).

tryFor5([H|T], Count, A, A).	


tryReduceList:-
	findall(Number,classes_with_no_preReqs(Number),FirstLevelList),
	findall(Number,classes_with_preReqs(Number), HavePreReqList),
	Curr is 5,
	Total is 10,
	reduce_List(FirstLevelList, HavePreReqList, Total, Curr, Level1, Level2),
	writeln(Level1),
	writeln(Level2).

reduce_List(FirstLevelList,HavePreReqList, Total,Curr, Level1, Level2):-
	reduce_List(FirstLevelList, HavePreReqList, Total, Curr, Level1, Level2,[],[]). 	

reduce_List([H|T], HavePreReqList, Total, Curr, Level1, Level2, A , B):-
	Curr >0,
	Total >0,
	reduce_List(T, HavePreReqList, Total-1, Curr-1, Level1, Level2, [H|A], B).

reduce_List([H|T], HavePreReqList, Total, Curr, Level1, Level2, A, B):-
	reduce_List(T, HavePreReqList, Total-1, Curr-1, Level1, Level2, A, [H|B]).	
	
reduce_List([], [H|T], Total, Curr, Level1, Level2, A, B):-
	reduce_List([], T, Total-1, Curr-1, Level1, Level2, A, [H|B]).
	
reduce_List([], [], Total, Curr, A, B, A, B).	
	


get_classes_with_no_preReqs:-
	findall(Number,classes_with_no_preReqs(Number),List),
	writeln("Classes in first level":List).
	
classes_with_no_preReqs(Number):-
	preReq(Number,PreReqs),		
	length(PreReqs, N),
	N == 0.
	
	
get_classes_with_preReqs:-
	findall(Number,classes_with_preReqs(Number),List),
	writeln("Classes that have prerequists":List).
	
classes_with_preReqs(Number):-
	preReq(Number,PreReqs),	
	length(PreReqs, N),
	N >0.

get_all_classes:-
	findall(Number, all_classes(Number), List),
	writeln("All Classes": List).
	
all_classes(Number):-
	class(Number).
	
	
class(cs105).
class(cs115).
class(ma115).
class(ma116).
class(peA100).
class(cs135).
class(ch101).
class(humA100).
class(humB100).
class(peB100).

preReq(cs105,[]).
preReq(cs115,[cs105]).
preReq(ma115,[]).
preReq(ma116,[ma115]).
preReq(peA100,[]).
preReq(cs135,[]).
preReq(ch101,[]).
preReq(humA100,[]).
preReq(humB100,[]).
preReq(peB100,[]).


classesPerSemester("Tom", 5).

