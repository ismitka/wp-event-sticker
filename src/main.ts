/**
 The MIT License

 Copyright 2024 Ivan Smitka <ivan at stimulus dot cz>.

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */

import $ from "jquery";

export namespace WpEventSticker {

    type Settings = {
        suspended?: number[]
    }

    const settings: Settings = {
        suspended: []
    }

    export const init = () => {
        const el = document.querySelector<HTMLElement>(".EventSticker");
        if (el) {
            const wrapper = $(el);
            wrapper.removeAttr("style");
            setTimeout(() => {
                settings.suspended = JSON.parse(sessionStorage.getItem("WpEventStickerSuspended") ?? "[]");
                if (settings.suspended) {
                    settings.suspended.forEach((id) => {
                        $(".Event[data-id=" + id + "]", wrapper).addClass("suspended");
                    });
                }

                $(".Event .close", wrapper).click((e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const event = $(e.currentTarget).parent();
                    event.fadeOut(500, () => {
                        event.addClass("suspended");
                    });
                    settings.suspended?.push(event.data("id"));
                    sessionStorage.setItem("WpEventStickerSuspended", JSON.stringify(settings.suspended));
                });
                $(".Event", wrapper).click((e) => {
                    const event = $(e.currentTarget);
                    event.toggleClass("expanded");
                }).addClass("open");
                if (window.innerWidth > 1280) {
                    $(".Event", wrapper).addClass("expanded");
                }
            }, 2000);
        }
    }
}

if (document.readyState !== 'loading') {
    WpEventSticker.init();
} else {
    document.addEventListener('DOMContentLoaded', () => {
        WpEventSticker.init();
    });
}


