<ul id="liveEvents" class="text-sm"></ul>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('liveEvents');

    if (!list) {
        console.error('Cannot find element with id "liveEvents"');
        return;
    }

    const source = new EventSource('/events');
    
    source.onmessage = (event) => {
        const data = JSON.parse(event.data);
        console.log(data);
        
        list.innerHTML = '';
        data.events.forEach(ev => {
            const li = document.createElement('li');
            li.textContent = `${ev.title} — ${ev.event_date} (${ev.status})`;
            list.appendChild(li);
        });
        console.log('Updated at:', data.timestamp);
    };

    source.onerror = (err) => {
    };

});
</script>
