% load course information
:- ensure_loaded(courses).


%%%%%%%%%%%%%%%%%%%%%%%%
%% COURSE GROUPS %%%%%
%%%%%%%%%%%%%%%%%%%%%%%%

courseGroup(sci, [ch115, ch281]).
courseGroup(math, [ma115, ma116, ma222, ma331]).
courseGroup(mngt, [mgt111]).
courseGroup(csReq, [cs115, cs146, cs135, cs284, cs334, cs383, cs385, cs347, cs392, cs496, cs442, cs511, cs488, cs492, cs506, cs423, cs424]).
courseGroup(techElect, [cs503, cs513, cs519, cs524, cs545]).
courseGroup(softwareDevElective, [cs516, cs521, cs522, cs526, cs537, cs541, cs546, cs549, cs558]).
courseGroup(mathScienceElective, [ch116, pep111, pep112,ma221, ma232, ma227]).
courseGroup(freeElective, [free101, free102, free103, free104]).
courseGroup(humGroupA, [hpl111, hum103, hpl112, hli105, hli113, hli114]).
courseGroup(humGroupB, [hss121, hss123, hss124, hss126, hum107, hum108]).
courseGroup(humRequiredClass, [hss371]).
courseGroup(hum300400, [hpl350, hpl444, hpl455, hss331, hss376, hss324, hpl340, hpl348, hpl450, hli335]).
