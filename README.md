oauth-client
============

run `./vendor/bin/oauth-client` to get list of actions.

* `request-token` — fetches Request Token [[6.1](http://oauth.net/core/1.0a/#auth_step1)]
* `authorize-token` — provides URL for token authorization [[6.2](http://oauth.net/core/1.0a/#auth_step2)]
* `access-token [PIN]` — exchanges authorized Request Token for Access Token (if PIN is not given on command-line it will be explicitly requested) [[6.3](http://oauth.net/core/1.0a/#auth_step3)]
* `webapp` — starts a web-app on http://127.0.0.1:8081/ which can be used for live-test of OAuth
