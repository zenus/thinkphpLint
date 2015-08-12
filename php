#!/bin/bash
__DIR__="$(dirname "$0")"
/opt/php/bin/php "-c$__DIR__/stdlib" "$@"
