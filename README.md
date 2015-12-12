# arcanum
Arcanum is a password tool written in PHP with HTML frontend. It uses PEAR DB Dataobjects as DBAL and MCrypt as encryption technology.

Arcanum's main goal is secureness and simplicity.

Features for users:
- Almost all userdata is encrypted by a masterkey which only exists in session (its generated from various things such username and password)
- Store credentials in categories and see the password history
- Additional to the credentials you can specify a link or the contents of a destinated HTML form field to log into a another website - per click.
- Register function for new users; this could be switched to the 'invitation only' mode
- Store files
- Memo function
- Password hint
- Patternlock

Features for admins:
- Easy install (Arcanum creates necessary files)
- Widely configurable through config file
- It is entirely written in a MVC way, so it is simple to add new modules
- Other Languages (than english and german) could be easily added
