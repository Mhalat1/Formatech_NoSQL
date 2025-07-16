// assets/js/calendar.js
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const eventsUrl = calendarEl.dataset.eventsUrl;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: frLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: eventsUrl,
            method: 'GET',
            failure: () => {
                alert('Erreur de chargement des événements');
            }
        },
        editable: true,
        eventClick: function(info) {
            alert('Événement: ' + info.event.title);
        }
    });

    calendar.render();
});