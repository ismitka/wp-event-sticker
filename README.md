# wp-event-sticker
Event Stickers - WordPress Plugin

Adds Event Stickers to page
Events Based on Events Manager Plugin https://cs.wordpress.org/plugins/events-manager/
Events are grouped by priority (EventSticker_Priority property)
Events are valid for display 14 days before Start. Days interval can be configured via EventSticker_DaysBefore property
Event Sticker displays Event Category Image, Event Name and Event Start
Link depends on URL_Turnaj property

Build CSS 
```bash
sass src/event-sticker.scss:static/event-sticker.css
```

Update dependencies
```bash
npm install
```

Compile JS
```bash
pnpm run build
```

Create plugin archive
```bash
./compress.sh
```