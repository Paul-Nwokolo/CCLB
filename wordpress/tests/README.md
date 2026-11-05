# WordPress theme checks

Run against a local WordPress that has the CCLB Waterline theme and CCLB Parish plugin active (and pages give, harvest, join).

    npm i playwright axe-core
    BASE=http://localhost:8080 node a11y.js
    BASE=http://localhost:8080 node menu-keyboard.js

`a11y.js` must report `violations=0` on every line. One colour-contrast report on the header's Visit link was seen once while the header was mid colour-transition and did not reproduce in six reruns.
