<?php

/**
 * Rewritten by .github/workflows/release.yml at build time from the git tag.
 * A working copy always reads "dev"; only a released archive or container image
 * carries a real version.
 */

define('PRODUCT_VERSION', 'dev');
define('PRODUCT_BUILD', 'source');
