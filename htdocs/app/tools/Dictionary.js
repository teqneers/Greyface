Ext.define("Greyface.tools.Dictionary",{
    extend: "Ext.data.Store",
    singleton:true,
    fields: ["key", "en", "de"],
    data:[
        // a
        {key:"add", en:"Add", de:"Hinzufügen"},
        {key:"addAlias", en:"Add alias", de:"Alias hinzufügen"},
        {key:"addDomain", en:"Add Domain", de:"Domäne hinzufügen"},
        {key:"addEmail", en:"Add Email", de:"Email hinzufügen"},
        {key:"addEmailToAutoWhitelist", en:"Add Email to Auto Whitelist", de:"Email zur Auto Whitelist hinzufügen"},
        {key:"addUser", en:"Add user", de:"Benutzer hinzufügen"},
        {key:"alias", en:"Alias"},
        {key:"aliasManagement", en:"Alias Management", de:"Aliasmanagement"},
        {key:"autoWhitelist", en:"Auto Whitelist"},

        // b
        {key:"blacklist", en:"Blacklist"},

        // c
        {key:"createRandomPasswort", en:"Create Random Password", de:"Erzeuge zufülliges Passwort"},
        {key:"createUser", en:"Create new user", de:"Neuen Benutzer anlegen"},

        // d
        {key:"delete", en:"Delete", de:"Löschen"},
        {key:"deleteEntriesByTime", en:"Delete entries by time", de:"Lösche Einträge nach Zeit"},
        {key:"deleteEntriesByTimeDescription", en:"All entrys from the past to the selected date will be deleted!", de:"Alle Einträge der Vergangenheit bis zum ausgewähltem Datum werden gelöscht!"},
        {key:"domain", en:"Domain", de:"Domäne"},
        {key:"domains", en:"Domains", de:"Domänen"},
        {key:"domainList", en:"Domain List", de:"Domain Liste"},

        // e
        {key:"email", en:"Email"},
        {key:"emails", en:"Emails"},
        {key:"emailList", en:"Email List", de:"Email Liste"},

        // f
        {key:"firstSeen", en:"First Seen", de:"Erstmals Gesichtet"},
        {key:"filterBy", en:"Filter by:", en:"Filter nach:"},
        {key:"fulltextSearch", en:"Fulltext search:", de:"Volltextsuche:"},

        // g
        {key:"greylist", en:"Greylist"},

        // h
        {key:"hasToBeValidIp4/6", en:"Has to be a valid IPv4/6 address", de:"Muss eine valide IPv4/v6 Adresse sein"},

        // l
        {key:"language", en:"Language", de:"Sprache"},
        {key:"lastSeen", en:"Last Seen", de:"Zuletzt Gesichtet"},
        {key:"login", en:"Login", de:"Login"},
        {key:"logout", en:"Logout", de:"Abmelden"},

        // m
        {key:"moveToAutoWhitelist", en:"Move to Auto Whitelist", de:"In die Auto Whitelist verschieben"},

        // p
        {key:"password", en:"Password", de:"Passwort"},

        // r
        {key:"recipient", en:"Recipient", de:"Empfänger"},
        {key:"retypePassword", en:"Retype password", de:"Passwort wiederholen"},
        {key:"retypePasswordError", en:"The retyped password differs to the password", de:"Die beiden Passwörter stimen nicht überein"},
        {key:"rememberMe", en:"Remember me", de:"Angemeldet bleiben"},

        // s
        {key:"sender", en:"Sender", de:"Sender"},
        {key:"set", en:"Set", de:"Zuweisen"},
        {key:"searchDomain", en:"Search for domain:", de:"Domäne suchen:"},
        {key:"searchMail", en:"Search for email:", de:"Email suchen:"},
        {key:"searchUser", en:"Search for user:", de:"Benutzer suchen:"},
        {key:"searchValue", en:"search value...", de:"Suchwert..."},
        {key:"sendEmail", en:"Send email", de:"Sende Email"},
        {key:"setNewUserPassword", en:"Set new user password", de:"Setze neues Benutzer-Passwort"},
        {key:"source", en:"Source", de:"Quelle"},
        {key:"statusAdmin", en:"Admin", de:"Administrator"},
        {key:"statusUser", en:"User", de:"Benutzer"},

        // t
        {key:"to:", en:"to:", de:"Bis:"},

        // u
        {key:"user", en:"User", de:"Benutzer"},
        {key:"userStatus", en:"User Status", de:"Benutzerstatus"},
        {key:"userManagement", en:"User Management", de:"Benutzermanagement"},
        {key:"username", en:"Username", de:"Benutzername"},

        // w
        {key:"whitelist", en:"Whitelist"}
    ],
    language: "de",
    defaultLaguage:"en",
    supportedLang: ("en", "de"),
    exists: function($key) {
        var result = this.get($key);
        if (result == null) {
            return false;
        } else {
            return true;
        }
    },
    setLanguage: function($lang) {
        if (this.supportedLang.indexOf($lang) != -1) {
            this.language = $lang;
        }
    },
    translate: function($key) {
        var row = this.findExact("key", $key);
        if (row == -1) {
            return null;
        } else {
            if( "" == this.getAt(row).get(this.language) ) {
                return this.getAt(row).get(this.defaultLaguage);
            } else {
                return this.getAt(row).get(this.language);
            }
        }
    }
});