import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import idLocale from "@fullcalendar/core/locales/id";

// Warna per tipe event — konsisten dengan token warna di resources/css/app.css
const EVENT_COLORS = {
    start: "#3B82F6", // info — barang mulai dipinjam
    due: "#F59E0B", // warning — jatuh tempo hari ini/nanti
    overdue: "#DC2626", // danger — sudah lewat jatuh tempo
};

document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("sirkulasi-calendar");
    if (!el) return;

    const events = JSON.parse(el.dataset.events || "[]");

    // Kelompokkan per tanggal supaya modal detail hari bisa menampilkan
    // semua event (mulai + jatuh tempo + terlambat) yang jatuh di hari itu.
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
        dayMaxEvents: 3,
        headerToolbar: {
            left: "prev",
            center: "title",
            right: "today next",
        },
        events: events.map((e) => ({
            title: `${e.type === "start" ? "▸" : "◂"} ${e.item_name}`,
            date: e.date,
            color: EVENT_COLORS[e.type] || "#64748b",
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
