# CSP User Interface Plugin

Plugin to ustomize Vue.js templates and components for the interface of the journal Cadernos de Saúde Pública - Open Journal System (OJS).

**OJS version: 3.4.0, 3.5.0 (partial — see below)**

**3.5 status:** the public-supplementary-material access (`CspArticleHandler`),
backend CSS injection, and galley-download-link rewrite are ported and
active. The Vue dashboard-list-item override (showing the CSP submission
code instead of the OJS numeric ID) has no sustainable extension point in
3.5's Vue 3 dashboard as of image `3_5_0-5` and is parked — the `npm run
build` step below is not needed for the 3.5 port; `js/build.js` is not
loaded, and the Vue 2 source that fed it was removed (still recoverable
from git history if the parked feature is picked up again).

## Installation

**OJS 3.5 (ensp-csp-ojs-3.5 stack):** this repo is bind-mounted into the
`app` container at `/var/www/html/plugins/generic/cspUI` via
`docker-compose.yml` in the `ensp-csp-ojs-3.5` repo, checked out to the
`ojs-3.5` branch. Enable it in _Website > Plugins_ after the container
picks up the mount. No `npm install`/`npm run build` needed — see the 3.5
status note above.

**Standalone OJS 3.4 install:**

1) Clone this repo inside the directory ``ojs/plugins/generic/`` :

   ``git clone https://github.com/FiocruzLivre/ojs-csp-ui.git cspUI``

    > The plugin must be inside a _cspUI_ named folder
2)  Go to the plugin folder and run ``npm instal`` and ``npm run build`` to generate de js/build.js file

3) In the system, enable the plugin in _Website > Plugins_ area
