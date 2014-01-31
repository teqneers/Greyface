GREYFACE
========

Greyface is an open source AJAX based web interface to SQLGrey, a greylisting policy daemon for the Postfix MTA.
View and manipulate live greylisting data through an easy-to-use interface or submit new greylisting data.

Greyface is an open source web application developed by TEQneers, which interacts with SQLGrey and Postfix and
out-of-box. It builds on the greylisting approach and helps users and system administrators in managing their e-mails.

The old Greyface version 1 can still be found here: http://sourceforge.net/projects/greyface/
The new version is a complete refactoring of the old version and uses now ExtJs 4.2 instead 3.0.


THE APPROACH
============

Greylisting is a method to SPAM-protect e-mails. By using greylisting on a mail server,
currently around 95% of potential spam can be blocked. The mail server compares the incoming e -mail with a database.
If the combination of the sender's e- mail address, recipient's e -mail address and the client IP address is not
yet stored in the database, the e-mail is set in a wait state. If this 3-match-combination is detected by the mail
server again. in a period of time, the e -mail will be forwarded to the recipient. It is set to the auto-whitelist.

If this combination of 3 is not detected in a given period , it is removed from the database.


Sometimes, however, an e-mail in the queue remains there without the receivers will.
Especially after a customer discussion you do not want to wait for the customer e- mail for a long time.
The other way would be perhaps the incoming e -mails will be permanently placed on the whitelist.
But each time addressing the system administrator would be time and cost intensive.

This administration takes over the Gray Facebook application!



FULL CONTROL FOR USERS AND SYSTEM ADMINISTRATORS
================================================

The user management of Greyface provides two user roles: system administrators and users.
This does not only guarantee highest privacy but also easy editing their emails.
System administrators have full access to the system. These include the following points:

    -WHITELIST: The whitelist determines which emails shall be forwarded without
                permission of the recipient.

    -BLACKLIST: The blacklist defines which e-mail addresses will be permanently
                blocked from the system.

    -GREYLIST: The greylist includes all e-mails that are in the queue.

    -USER MANAGEMENT: New users can be added or edited by the user management functions.
                      E-mail addresses and aliases can be managed.

Created users in the system have access to their greylist and have the opportunity to put emails directly to the
whitelist.



TECHNICAL REALIZATION
=====================
Greyface is written in PHP 5, offering a connection to the supplied database of SQLGrey.
The use of the latest web technologies using the ExtJs framework 4.2, increases usability and makes it fit for
the future.



INTERESTED?
===========
The latest version of Greyface can be found on https://github.com/teqneers/Greyface
For more information about installing GREYFACE on your system, read the install.txt