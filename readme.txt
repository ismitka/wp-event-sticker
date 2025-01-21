To insert evnet banner write shorcode to article:

[event-banner [show=true] [collapsible=1280] [collapsed=640]]

show=false:    disable widget (default: true)
collapsed=px   widget will be collapsed for a screen width less than the given number
collapsible=px widget will be collapsible for a screen width less than the given number

event values to be visible:
category image as expanded logo
category slug (Název v URL) as collapsed logo (/wp-content/uploads/category/{slug}.png) height: 45px
event start
event location name

event attribute configuration:
#_ATT{EventBanner}{Ne|Ano}
#_ATT{EventBannerCountdown}{Ne|Ano}

EventBanner=Ano           use event for event banner
EventBannerCountdown=Ano  show countdown
URL_Turnaj=URL    banner link URL
