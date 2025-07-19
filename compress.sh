#!/bin/bash
sass src/event-sticker.scss static/event-sticker.css
VERSION=$(git tag -l)
echo "${VERSION}"
sed -i -E "s/^ \* Version\: .*$/ * Version: ${VERSION}/g" wp-event-sticker.php
(cd ../ && zip -r wp-event-sticker/${VERSION}.zip \
  wp-event-sticker/index.html \
  wp-event-sticker/LICENSE \
  wp-event-sticker/README.md \
  wp-event-sticker/wp-event-sticker.php \
  wp-event-sticker/dist \
  wp-event-sticker/static)