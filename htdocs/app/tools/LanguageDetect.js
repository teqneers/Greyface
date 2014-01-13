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
    };
}
LanguageDetect.detectLanguage();