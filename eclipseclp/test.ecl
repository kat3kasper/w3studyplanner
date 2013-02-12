% base constraints -- clients should send additional constraints such as degree requirements with which to validate against. 

% TODO: add a way to override base constraints when necessary

:- lib(ic).
% define course data structure
:- local struct(course(code, name, credits, prereqs, coreqs)).

getPrereqs(Course, Var) :-
  arg(prereqs of course, Course, Var).

hasPrereqs(Course) :-
  getPrereqs(Course, Prereqs),
  not(length(Prereqs,0)).

% not sure if member or memberchk should be used
courseTaken(Transcript, Course) :-
  once memberchk(Course, Transcript).

canTake(Transcript, Course) :-
  % writeln('Evaluating course: ' + Course),
  not(courseTaken(Transcript, Course)),
  (hasPrereqs(Course) ->
    getPrereqs(Course, Prereqs),
    (foreach(Prereq, Prereqs), fromto(PrereqsTaken, Out, In, []), param(Transcript) do courseTaken(Transcript, Prereq) -> Out=[Prereq|In] ; Out=In),
    length(Prereqs, P),
    (length(PrereqsTaken,P) ->
      !
      ;
      % TODO: write predicate that will list missing requirements for error reporting
      writeln('Cannot take course, missing requirements'),
      fail
    )
    ;
    !
  ).

% build list of courses available based on courses taken
buildAvailableCourseCatalog(CourseList, Transcript, Catalog) :-
  (foreach(Course, CourseList), fromto(Catalog, Out, In, []), param(Transcript) do canTake(Transcript, Course) -> Out=[Course|In] ; Out=In).

% pseudo unit tests
canTakeTest :-  
  CS105 = course{code:cs105,credits:3,prereqs:[]},
  CS200 = course{code:cs200,credits:3,prereqs:[CS105]},
  Transcript = [CS105],
  canTake(Transcript,CS200).

courseCatalogTest :-
  CS105 = course{code:cs105,credits:3,prereqs:[]},
  CS200 = course{code:cs200,credits:3,prereqs:[CS105]},
  CS400 = course{code:cs400,credits:4,prereqs:[CS105,CS200]},
  CS300 = course{code:cs300,credits:3,prereqs:[]},
  CS490 = course{code:cs490,credits:3,prereqs:[CS300]},
  CS500 = course{code:cs500,credits:4,prereqs:[CS490,CS400]},
  HUM101 = course{code:hum101,credits:3,prereqs:[]},
  HUM201 = course{code:hum201,credits:3,prereqs:[HUM101]},
  HUM300 = course{code:hum300,credits:4,prereqs:[HUM201,CS300]},
  Transcript = [CS105,CS200,CS300,HUM101,HUM201],
  buildAvailableCourseCatalog([CS105,CS200,CS400,CS300,CS490,CS500,HUM101,HUM201,HUM300], Transcript, Catalog),
  writeln(Catalog).


% server functionality

listen :-
        new_socket_server(Socket, localhost/9000, 50), accept_loop(Socket).

% socket server accept loop: ... -> accept -> process -> close -> ...
accept_loop(Socket) :-
        accept(Socket, _/_, ConSocket),
        read_exdr(ConSocket, Goal), Goal, write_exdr(ConSocket, Goal),
        close(ConSocket),
        accept_loop(Socket).