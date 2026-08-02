import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import idLocale from "@fullcalendar/core/locales/id";

document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("sirkulasi-calendar");
    if (!el) return;

    const events = JSON.parse(el.dataset.events || "[]");
    const eventsByDate = {};
    events.forEach((e) => {
        if (!eventsByDate[e.date]) eventsByDate[e.date] = [];
        eventsByDate[e.date].push(e);
    });

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: "dayGridMonth",
        locale: idLocale,
        firstDay: 1,
        height: "auto",
        headerToolbar: {
            left: "prev",
            center: "title",
            right: "next",
        },
        events: events.map((e) => ({
            title: e.item_name,
            date: e.date,
            color: "#F59E0B",
            textColor: "#ffffff",
        })),
        dateClick: (info) => {
            const dayEvents = eventsByDate[info.dateStr];
            if (!dayEvents || dayEvents.length === 0) return;
            window.dispatchEvent(
                new CustomEvent("open-day-detail", {
                    detail: { date: info.dateStr, items: dayEvents },
                }),
            );
        },
        eventClick: (info) => {
            const dayEvents = eventsByDate[info.event.startStr];
            window.dispatchEvent(
                new CustomEvent("open-day-detail", {
                    detail: {
                        date: info.event.startStr,
                        items: dayEvents || [],
                    },
                }),
            );
        },
    });

    calendar.render();
});
