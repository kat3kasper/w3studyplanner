% include base constraints and class relations 
:- lib(timeout).
:- ensure_loaded(degreeChecker_final).

%%%%%%% server functionality %%%%%%%

% start server and listen for clients
listen :-
        new_socket_server(Socket, localhost/9000, 4096), accept_loop(Socket).

% accept loop
accept_loop(Socket) :-
        accept(Socket, _/_, ConSocket),
        % read Goal, evaluate it with a timeout, and write the unified result
        ensure_loaded('/opt/apache/htdocs/studyplanner/lib/degreeChecker_final.pl'), read_exdr(ConSocket, Goal), write(Goal), nl, timeout(Goal, 60, Goal = Goal), write_exdr(ConSocket, Goal), 
        close(ConSocket),
        accept_loop(Socket).
