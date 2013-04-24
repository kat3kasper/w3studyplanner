% include base constraints and class relations 
:- ensure_loaded(degreeChecker_redesign).

%%%%%%% server functionality %%%%%%%

% start server and listen for clients
listen :-
        new_socket_server(Socket, localhost/9000, 4096), accept_loop(Socket).

% accept loop
accept_loop(Socket) :-
        accept(Socket, _/_, ConSocket),
        % read Goal, evaluate it, and write the unified result
        % Goal is OR'd with true so that it never aborts if there is no solution
        read_exdr(ConSocket, Goal), (Goal;true), write_exdr(ConSocket, Goal),
        close(ConSocket),
        accept_loop(Socket).
