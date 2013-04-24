%%%%%%%%%%%%%%%%%%%%%%%%
%%%     COURSES    %%%%%
%%%%%%%%%%%%%%%%%%%%%%%%

course(ch115, none, none, [fall, spring], 3).
course(ch281, ch115, none, [spring], 3).

course(ma115, none, none, [fall, spring], 3).
course(ma116, ma115, none, [fall,spring], 4).
course(ma222, ma116, none, [spring], 3).
course(ma331, ma222, none, [fall], 3).

course(mgt111, none, none, [fall], 3).

course(cs115, none, none, [fall, spring], 4).
course(cs146, none, none, [fall], 3).
course(cs135, none, none, [spring, fall], 3).
course(cs284, cs115, cs135, [spring, fall], 4).
course(cs334, and(cs115, cs135), none, [fall], 3). 
course(cs383 , cs115, cs284, [fall], 3).
course(cs385 , cs284, none, [fall, spring], 4).
course(cs347 , and(cs135, cs284), none, [spring], 3).
course(cs392 , cs385, none, [fall,spring], 3).
course(cs496 , cs334, cs385, [spring], 3).
course(cs442 , cs385, none, [fall], 3).
course(cs511 , cs385, none, [fall], 3).
course(cs488 , and(ma222, cs383), none, [spring], 3).
course(cs492 , and(cs383, cs385), none, [spring], 3).
course(cs506 , none, none, [fall], 3).
course(cs423 , and(cs347, cs385), none, [fall], 3).
course(cs424 , cs423, none, [spring], 3).

course(cs105 , none, none, [fall, spring], 3).

%Tech Electives Shortened
course(cs503 , cs135, none, [fall], 3).
course(cs513 , ma331, none, [fall], 3).
course(cs519 , none, none, [fall], 3).
course(cs524 , none, none, [spring], 3).
course(cs545 , cs385, none, [fall], 3).

%software dev electives
course(cs516 , cs385, none, [fall], 3).
course(cs521 , cs492, none, [spring], 3).
course(cs522 , cs385, none, [spring], 3).
course(cs526 , cs385, none, [fall], 3).
course(cs537 , cs385, none, [fall, spring], 3).
course(cs541 , cs385, none, [fall], 3).
course(cs546 , and(cs442, cs146), none, [fall,spring], 3).
course(cs549 , cs385, none, [fall], 3).
course(cs558 , and(cs385, ma232), none, [spring], 3).

%Science math
course(ch116 , ch115, none, [spring], 3).
course(pep111 , none, none, [spring, fall], 3).
course(pep112 , pep111, none, [fall, spring], 3).
course(ma221 , ma116, none, [fall,spring], 3).
course(ma232 , none, none, [fall], 3).
course(ma227 , ma221, none, [fall,spring], 3).

course(hss371 , none, none, [fall], 3).

%Hum A
course(hpl111 , none, none, [fall], 3).
course(hum103 , none, none, [spring], 3).
course(hpl112 , none, none, [fall], 3).
course(hli105 , none, none, [spring], 3).
course(hli113 , none, none, [fall], 3).
course(hli114 , none, none, [spring], 3).

%Hum B
course(hss121 , none, none, [fall], 3).
course(hss123 , none, none, [fall], 3).
course(hss124 , none, none, [fall], 3).
course(hss126 , none, none, [spring], 3).
course(hum107 , none, none, [spring], 3).
course(hum108 , none, none, [spring], 3).

%Upper Level Hum
course(hpl350 , none, none, [spring], 3).
course(hpl444 , none, none, [spring], 3).
course(hpl455 , none, none, [spring], 3).
course(hss331 , none, none, [spring, fall], 3).
course(hss376 , none, none, [fall], 3).
course(hss324 , none, none, [fall], 3).
course(hpl340 , hpl111, none, [fall], 3).
course(hpl348 , hpl111, none, [fall], 3).
course(hpl450 , none, none, [fall], 3).
course(hli335 , none, none, [fall], 3).

%Free Elective
course(free101 , none, none, [fall], 3).
course(free102 , none, none, [spring], 3).
course(free103 , none, none, [fall,spring], 3).
course(free104 , none, none, [fall,spring], 3).