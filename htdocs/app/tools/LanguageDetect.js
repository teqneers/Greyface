var LanguageDetect = new function() {
    this.language = "";
    this.defaultLanguage = "en";
    this.getLanguage = function () {
        if (this.language == "") {
            return this.defaultLanguage;
        } else {
            return this.language;
        }
    };
    this.getDefaultLanguage = function () {
        return this.defaultLanguage;
    };
    this.detectLanguage = function() {
        scope = this;
        var urlString = location.search
        var params = urlString.split("?");
        params.forEach(function(value){
            var param = value.split("=");
            if (param[0] == "language") {
                scope.language = param[1];
            }
        });
        if (scope.language == "") {
            var allCookies = document.cookie.split(';');
            for(var i=0; i < allCookies.length; i++) {
                var cookie = allCookies[i];
                while (cookie.charAt(0)==' ') {
                    cookie = cookie.substring(1);
                }
                if (cookie.split()[0].indexOf('preferredLanguage') != -1) {
                    var preferredLanguageKey = cookie.substring('preferredLanguageKey='.length, cookie.length);
                    this.language = preferredLanguageKey;
                }
            }
        }
    };
}
LanguageDetect.detectLanguage();