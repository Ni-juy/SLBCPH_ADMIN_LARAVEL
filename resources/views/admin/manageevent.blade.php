@extends('layouts.admin')

@section('title', 'Manage Events')
@section('header', 'Manage Events')

@section('content')

    <div class="flex flex-col gap-6">

        <!-- 📅 CALENDAR SECTION -->
        <div class="w-full bg-white p-6 rounded-lg shadow-lg border">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl lg:text-3xl font-semibold">Pick a Date</h2>
                <div id="calendarHeader" class="text-lg font-medium text-gray-600"></div>
            </div>

            <!-- Legend -->
            <div class="flex gap-4 mb-4 text-md lg:text-lg text-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span>Finished</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                    <span>ongoing</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span>Upcoming</span>
                </div>
            </div>

            <div class="w-full overflow-x-auto text-md lg:text-lg">
                <div id="fullCalendar" class="min-w-[600px]"></div>
            </div>
        </div>

        <!-- 📁 TOOLBAR ACTIONS -->
        <div class="flex justify-end items-center px-2 sm:px-4 -mt-2">
            <div class="flex flex-col sm:flex-row gap-2">
                <button onclick="document.getElementById('excelUploadModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold text-md lg:text-lg px-4 py-2 rounded-lg shadow transition">
                    📁 Upload Excel
                </button>
            </div>
        </div>



        <!-- 📋 EVENT LIST -->
        <div class="w-full bg-gray-50 p-6 rounded-lg shadow-md">

            <div class="flex justify-between mb-4">
                <h2 class="text-xl lg:text-2xl font-semibold mb-4">Event List</h2>
                <!-- <button id="trashBtn"
                    class="trash-btn bg-red-600 text-white px-4 py-2 rounded transition-all duration-300 flex items-center justify-center cursor-pointer hover:bg-red-700"
                    title="Delete Selected" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                </button> -->

                <style>
                    .trash-btn:hover:not(:disabled) {
                        transform: scale(1.05);
                        background-color: #ef4444;
                    }
                </style>

            </div>
            <div class="overflow-x-auto">
                <table id="eventsTable" class="w-full border-collapse border bg-white shadow-md rounded-lg">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700 text-center text-md lg:text-xl">
                            <!-- <th class="border p-3"><input type="checkbox" id="selectAll"></th> -->
                            <th class="border p-3">Event Name</th>
                            <th class="border p-3">Date</th>
                            <th class="border p-3">Time</th>
                            <th class="border p-3">Location</th>
                            <th class="border p-3">Status</th>
                            <th class="border p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr class="text-center bg-gray-50 hover:bg-gray-100 transition text-md lg:text-lg">
                                <!-- <td class="border p-3">
                                    <input type="checkbox" class="event-checkbox" value="{{ $event->id }}">
                                </td> -->
                                <td class="border p-3">
                                    <button class=" hover:underline event-title" data-title="{{ $event->title }}">
                                        {{ \Illuminate\Support\Str::limit($event->title, 15) }}
                                    </button>
                                </td>
                                <td class="border p-3">{{ $event->event_date }}</td>
                                <td class="border py-10">
                                    {{ $event->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $event->start_time)->format('h:i A') : '' }}
                                    -
                                    {{ $event->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $event->end_time)->format('h:i A') : '' }}
                                </td>
                                <td class="border p-3">
                                    <button class=" hover:underline event-title" data-title="{{ $event->location }}">
                                        {{ \Illuminate\Support\Str::limit($event->location, 15) }}
                                    </button>
                                </td>

                                <td class="border p-3">
                                    @if ($event->status === 'finished')
                                        <span
                                            class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">Finished</span>
                                    @elseif ($event->status === 'ongoing')
                                        <span
                                            class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-semibold">Ongoing</span>
                                    @else($event->status === 'upcoming')
                                        <span
                                            class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">Upcoming</span>
                                    @endif
                                </td>
                                <td class="border p-3">
                                    @if ($event->status === 'finished' || $event->status === 'ongoing')
                                        <button
                                            class="view-event-btn bg-blue-500 text-white px-4 py-2 rounded transition-all duration-300 cursor-pointer"
                                            data-event='@json($event)'>
                                            View
                                        </button>

                                        <style>
                                            .view-event-btn:hover {
                                                transform: scale(1.05);
                                                /* 5% zoom */
                                                background-color: #3b82f6;
                                                /* Tailwind blue-500 hover */
                                            }
                                        </style>

                                    @else
                                        <button class="bg-yellow-500 text-white px-4 py-2 rounded edit-event"
                                            data-id="{{ $event->id }}">
                                            Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <script>
                            @if (session('event_created'))
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: "{{ session('event_created') }}",
                                });
                            @elseif (session('success'))
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: "{{ session('success') }}",
                                });
                            @elseif ($errors->any())
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    html: `{!! implode('<br>', $errors->all()) !!}`,
                                });
                            @elseif (session('errors') && is_array(session('errors')))
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Some Events Skipped',
                                    html: `{!! implode('<br>', session('errors')) !!}`,
                                });
                            @endif
                        </script>

                    </tbody>
                </table>

            </div>
            <!-- Event Title Modal -->
            <div id="eventTitleModal"
                class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-200">


                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md text-center">
                    <h2 class="text-lg font-semibold mb-4">Full Event Name</h2>
                    <p id="fullEventTitle" class="text-gray-700 break-words"></p>
                    <div class="mt-6">
                        <button id="closeEventTitleModal"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-center items-center space-x-2" id="paginationContainer"></div>

        </div>
    </div>

    <!-- 📌 FORM MODAL -->
    <div id="formModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-bold mb-6" id="formTitle">Add Event</h2>

            <form id="eventForm" action="{{ route('events.store') }}" method="POST">
                @csrf
                <input type="hidden" id="eventId" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">
                <input type="hidden" name="status" value="upcoming">

                <div class="mb-4">
                    <label class="block font-medium text-gray-700">Event Name</label>
                    <input type="text" id="title" name="title" class="w-full p-3 border rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700">Date</label>
                    <input type="date" id="event_date" name="event_date" class="w-full p-3 border rounded" required>
                </div>


                <div class="mb-4 flex gap-2">
                    <div class="w-1/2">
                        <label class="block font-medium text-gray-700">Start Time</label>
                        <input type="time" id="start_time" name="start_time" class="w-full p-3 border rounded" required>
                    </div>
                    <div class="w-1/2">
                        <label class="block font-medium text-gray-700">End Time</label>
                        <input type="time" id="end_time" name="end_time" class="w-full p-3 border rounded" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700">Location</label>
                    <input type="text" id="location" name="location" class="w-full p-3 border rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="w-full p-3 border rounded"></textarea>
                </div>

                @if(auth()->user()->branch->branch_type === 'Main')
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="is_global" name="is_global" value="1"
                                class="form-checkbox text-blue-600">
                            <span class="ml-2 text-gray-700">Visible to all branches</span>
                        </label>
                    </div>
                @endif

                <!-- Add this inside your form, after the time fields and before the error message -->
                <div id="takenTimes" class="mb-2 text-sm text-gray-700 max-w-full overflow-x-auto whitespace-nowrap"></div>


                <!-- Add this inside your form, before the submit button -->
                <div id="timeError"
                    class="hidden mb-4 text-red-600 bg-red-100 border border-red-300 rounded p-2 text-center"></div>

                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700 transition">Save
                    Event</button>
                <button type="button" onclick="closeFormModal()"
                    class="mt-4 w-full bg-red-600 text-white p-3 rounded hover:bg-red-700 transition">Cancel</button>
            </form>
        </div>
    </div>


    <!-- 📌 VIEW MODAL -->
    <div id="viewModal" class="hidden fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4">

        <!-- Modal Box -->
        <div
            class="bg-white w-full max-w-lg md:max-w-xl lg:max-w-2xl p-4 md:p-6 rounded-lg shadow-lg text-sm md:text-base lg:text-lg flex flex-col max-h-[90vh]">

            <!-- Title -->
            <h2 class="text-xl md:text-2xl lg:text-3xl font-bold mb-4">Event Details</h2>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto flex-1 pr-2">
                <p><strong>Title:</strong> <span id="viewTitle"></span></p>
                <p><strong>Date:</strong> <span id="viewDate"></span></p>
                <p><strong>Time:</strong> <span id="viewTime"></span></p>
                <p><strong>Location:</strong> <span id="viewLocation"></span></p>
                <p><strong>Description:</strong> <span id="viewDescription"></span></p>
            </div>

            <!-- QR Code Section (fixed below) -->
            <div class="mt-4 flex flex-col items-center text-center flex-shrink-0">
                <div id="eventQrCode" class="max-w-[200px] md:max-w-[250px] lg:max-w-[300px]"></div>

                <!-- Hidden large QR code for download -->
                <div id="eventQrCodeLarge" class="hidden"></div>

                <button id="downloadQrBtn"
                    class="mt-2 bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded hover:bg-blue-700 transition text-sm md:text-base">
                    Download QR
                </button>

                <div class="text-xs md:text-sm text-gray-500 mt-2">
                    Scan this QR code to mark your attendance.
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-right flex-shrink-0">
                <button onclick="closeModal()"
                    class="bg-red-600 text-white px-4 py-2 md:px-6 md:py-2 rounded hover:bg-red-700 transition text-sm md:text-base">
                    Close
                </button>
            </div>
        </div>
    </div>



    <!-- 📁 EXCEL UPLOAD MODAL -->
    <div id="excelUploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg relative">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">📁 Batch Upload Events</h2>
            <p class="text-sm text-gray-600 mb-4">Upload your Excel file using the template provided. This will help
                standardize event creation.</p>

            <form method="POST" action="{{ route('events.batchUpload') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File (.xlsx)</label>
                    <input type="file" name="excel_file" accept=".xlsx"
                        class="w-full border p-2 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <!-- Upload Button -->
                    <button type="submit"
                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                        📤 Upload Excel
                    </button>

                    <!-- Template Download -->
                    <a href="{{ route('events.template') }}"
                        class="w-full sm:w-auto text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg border border-gray-300 shadow transition">
                        📥 Download Excel Template
                    </a>
                </div>
            </form>

            <!-- Close Button -->
            <button type="button" onclick="document.getElementById('excelUploadModal').classList.add('hidden')"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>



    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('eventTitleModal');
            const fullTitle = document.getElementById('fullEventTitle');
            const closeBtn = document.getElementById('closeEventTitleModal');

            document.querySelectorAll('.event-title').forEach(btn => {
                btn.addEventListener('click', () => {
                    const title = btn.getAttribute('data-title');
                    fullTitle.textContent = title;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            // Optional: close modal by clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });




        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('fullCalendar');
            var events = @json($allEvents->values());


            // Convert Laravel events to FullCalendar format if needed
            var fcEvents = events.map(function (event) {
                let bgColor = '#3b82f6'; // Default blue for upcoming
                if (event.status === 'finished') {
                    bgColor = '#22c55e'; // Green for finished
                }
                return {
                    id: event.id,
                    title: event.title,
                    start: event.event_date + (event.start_time ? 'T' + event.start_time : ''),
                    end: event.event_date + (event.end_time ? 'T' + event.end_time : ''),
                    backgroundColor: bgColor,
                    borderColor: bgColor,
                    textColor: '#fff',
                    allDay: false,
                    display: 'block', // <--- Add this line
                    extendedProps: {
                        id: event.id,
                        start_time: event.start_time,
                        end_time: event.end_time,
                        location: event.location,
                        description: event.description,
                        status: event.status
                    }
                };
            });




            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                selectable: true,
                validRange: {
                    start: new Date().toISOString().split('T')[0] // disables all dates before today
                },
                eventTimeFormat: { // Add this block to fix AM/PM display
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short',
                    hour12: true
                },
                eventContent: function (arg) {
                    // Custom event content to stack time and title vertically on mobile
                    var timeText = arg.timeText;
                    var titleText = arg.event.title;
                    var container = document.createElement('div');
                    container.classList.add('fc-event-custom-content');

                    var timeEl = document.createElement('div');
                    timeEl.classList.add('fc-event-custom-time');
                    timeEl.textContent = timeText;

                    var titleEl = document.createElement('div');
                    titleEl.classList.add('fc-event-custom-title');
                    titleEl.textContent = titleText;

                    container.appendChild(timeEl);
                    container.appendChild(titleEl);

                    return { domNodes: [container] };
                },
                dateClick: async function (info) {
                    document.getElementById('eventForm').reset();
                    document.getElementById('eventId').value = '';
                    document.getElementById('eventForm').action = "{{ route('events.store') }}";
                    document.getElementById('formMethod').value = "POST";
                    document.getElementById('event_date').value = info.dateStr;
                    document.getElementById('event_date').readOnly = true;
                    document.getElementById('formTitle').innerText = "Add Event";
                    document.getElementById('formModal').classList.remove('hidden');
                    await blockTakenTimes(info.dateStr);

                    const takenDiv = document.getElementById('takenTimes');
                    if (takenRanges.length > 0) {
                        takenDiv.innerHTML = `<span class="font-semibold text-black">Taken Times:</span><br>` +
                            takenRanges.map(range => {
                                return `<span class="inline-block bg-gray-200 text-black px-2 py-1 rounded mr-1 mb-1" style="min-width:120px;text-align:center;">
                                            ${formatTimeDisplay(range.start)} - ${formatTimeDisplay(range.end)}
                                        </span>`;
                            }).join('');
                    } else {
                        takenDiv.innerHTML = `<span class="text-green-600">No times taken for this date.</span>`;
                    }
                    setTimeout(() => {
                        document.getElementById('start_time').focus();
                    }, 200);
                },



                events: fcEvents,
                eventClick: function (info) {
                    const event = info.event;
                    const props = event.extendedProps;

                    document.getElementById('viewTitle').textContent = event.title;
                    document.getElementById('viewDate').textContent = event.start.toLocaleDateString();
                    document.getElementById('viewTime').textContent =
                        (props.start_time && props.end_time)
                            ? props.start_time + ' - ' + props.end_time
                            : formatTime(event.start);
                    document.getElementById('viewLocation').textContent = props.location || 'N/A';
                    document.getElementById('viewDescription').textContent = props.description || 'N/A';

                    // --- QR CODE GENERATION ---
                    const qrContainer = document.getElementById('eventQrCode');
                    const qrContainerLarge = document.getElementById('eventQrCodeLarge');
                    qrContainer.innerHTML = ""; // Clear previous QR
                    qrContainerLarge.innerHTML = ""; // Clear previous large QR
                    const qrUrl = `${props.id || event.id}`;

                    // Render normal size for display
                    new QRCode(qrContainer, {
                        text: qrUrl,
                        width: 128,
                        height: 128
                    });

                    // Render large size for download (e.g., 512x512)
                    new QRCode(qrContainerLarge, {
                        text: qrUrl,
                        width: 512,
                        height: 512
                    });

                    // Enable QR download
                    setTimeout(() => {
                        const downloadBtn = document.getElementById('downloadQrBtn');
                        const qrCanvasLarge = qrContainerLarge.querySelector('canvas');
                        if (downloadBtn && qrCanvasLarge) {
                            downloadBtn.onclick = function () {
                                // Sanitize event title for filename
                                const eventTitle = (event.title || 'event').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                                const link = document.createElement('a');
                                link.href = qrCanvasLarge.toDataURL('image/png');
                                link.download = `${eventTitle}_qr.png`;
                                link.click();
                            };
                        }
                    }, 300);

                    document.getElementById('viewModal').classList.remove('hidden');
                }



            });

            calendar.render();

            function bindTableButtons() {
                document.querySelectorAll('.view-event-btn').forEach(btn => {
                    btn.onclick = () => {

                        const ev = JSON.parse(btn.dataset.event);

                        document.getElementById('viewTitle').textContent = ev.title;
                        document.getElementById('viewDate').textContent = ev.event_date;
                        document.getElementById('viewTime').textContent =
                            (ev.start_time && ev.end_time)
                                ? ev.start_time + ' - ' + ev.end_time
                                : '';
                        document.getElementById('viewLocation').textContent = ev.location || '';
                        document.getElementById('viewDescription').textContent = ev.description || '';


                        const qrContainer = document.getElementById('eventQrCode');
                        const qrContainerLarge = document.getElementById('eventQrCodeLarge');
                        qrContainer.innerHTML = "";
                        qrContainerLarge.innerHTML = "";
                        const qrUrl = `${ev.id}`;

                        new QRCode(qrContainer, {
                            text: qrUrl,
                            width: 128,
                            height: 128
                        });

                        new QRCode(qrContainerLarge, {
                            text: qrUrl,
                            width: 512,
                            height: 512
                        });
                        document.getElementById('viewModal').classList.remove('hidden');
                    };
                });

                document.querySelectorAll('.edit-event').forEach(btn => {
                    btn.onclick = async () => {
                        const eventId = btn.dataset.id;

                        try {
                            const res = await fetch(`/admin/events/${eventId}/edit`);
                            const ev = await res.json();

                            // Reset form
                            const form = document.getElementById('eventForm');
                            form.reset();
                            document.getElementById('eventId').value = ev.id;
                            form.action = `/admin/events/${ev.id}`;
                            document.getElementById('formMethod').value = "PUT";
                            document.getElementById('title').value = ev.title;
                            document.getElementById('event_date').value = ev.event_date;
                            document.getElementById('event_date').readOnly = false;
                            document.getElementById('start_time').value = ev.start_time;
                            document.getElementById('end_time').value = ev.end_time;
                            document.getElementById('location').value = ev.location;
                            document.getElementById('description').value = ev.description || '';
                            document.getElementById('formTitle').innerText = "Edit Event";
                            document.getElementById('formModal').classList.remove('hidden');

                            await blockTakenTimes(ev.event_date);
                            const eventRow = allEventsData.find(ev2 => ev2.title === ev.title);

                            let filteredRanges = takenRanges;
                            if (eventRow) {
                                filteredRanges = takenRanges.filter(range =>
                                    !(range.start === eventRow.start_time && range.end === eventRow.end_time && range.date === eventRow.event_date)
                                );
                            }

                            console.log(filteredRanges);
                            const takenDiv = document.getElementById('takenTimes');
                            if (filteredRanges.length > 0) {
                                takenDiv.innerHTML = `<span class="font-semibold text-black">Taken Times:</span><br>` +
                                    filteredRanges.map(range => {
                                        return `<span class="inline-block bg-gray-200 text-black px-2 py-1 rounded mr-1 mb-1" style="min-width:120px;text-align:center;">
                                                    ${formatTimeDisplay(range.start)} - ${formatTimeDisplay(range.end)}
                                                </span>`;
                                    }).join('');
                            } else {
                                takenDiv.innerHTML = `<span class="text-green-600">No times taken for this date.</span>`;
                            }

                        } catch (err) {
                            console.error('Error fetching event:', err);
                        }
                    };
                });


                document.querySelectorAll('.event-title').forEach(btn => {
                    const modal = document.getElementById('eventTitleModal');
                    const fullTitle = document.getElementById('fullEventTitle');
                    btn.onclick = () => {
                        const title = btn.getAttribute('data-title');
                        fullTitle.textContent = title;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    };
                });

                document.querySelectorAll('.delete-event').forEach(btn => {
                    btn.onclick = (e) => {
                        const eventId = e.currentTarget.dataset.id;

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action cannot be undone!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(`/admin/events/${eventId}/delete`)
                                    .then(response => response.json())
                                    .then(event => {
                                        Swal.fire(
                                            'Deleted!',
                                            'The event has been deleted.',
                                            'success'
                                        ).then(() => {
                                            window.location.reload();
                                        });
                                    });
                            }
                        });
                    };
                });



                closeBtn.onclick = () => {
                    const modal = document.getElementById('eventTitleModal');
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                };



            }

            function renderTablePage(page = 1) {
                tableBody.innerHTML = '';
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const pageItems = eventsData.slice(start, end);

                pageItems.sort((a, b) => {
                    const statusPriority = { ongoing: 1, upcoming: 2, finished: 3 };

                    if (statusPriority[a.status] !== statusPriority[b.status]) {
                        return statusPriority[a.status] - statusPriority[b.status];
                    }

                    const dateA = new Date(a.event_date);
                    const dateB = new Date(b.event_date);
                    return dateA - dateB;
                });

                pageItems.forEach(ev => {
                    let statusHtml = '';
                    if (ev.status === 'finished') statusHtml = `<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">Finished</span>`;
                    else if (ev.status === 'ongoing') statusHtml = `<span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-semibold">Ongoing</span>`;
                    else statusHtml = `<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">Upcoming</span>`;

                    let actionBtn = '';
                    if (ev.status === 'finished' || ev.status === 'ongoing') {
                        actionBtn = `<button class="mr-3 view-event-btn bg-blue-500 text-white px-2 py-1 rounded transition-all duration-300 cursor-pointer" data-event='${JSON.stringify(ev)}'><i class="fas fa-eye"></i></button>`;
                    } else {
                        actionBtn = `<button class="bg-yellow-500 text-white px-2 py-1 rounded edit-event" data-id="${ev.id}"><i class="fas fa-edit"></i></button>`;
                    }

                    const tr = document.createElement('tr');
                    tr.className = 'text-center bg-gray-50 hover:bg-gray-100 transition text-md lg:text-lg';
                    tr.innerHTML = `
                                <td class="border p-3"><button class="hover:underline event-title" data-title="${ev.title}">${ev.title.length > 15 ? ev.title.substring(0, 15) + '...' : ev.title}</button></td>
                                <td class="border p-3">${ev.event_date}</td>
                                <td class="border py-10">${ev.start_time ? new Date('1970-01-01T' + ev.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''} - ${ev.end_time ? new Date('1970-01-01T' + ev.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}</td>
                                <td class="border p-3"><button class="hover:underline event-title" data-title="${ev.location}">${ev.location.length > 15 ? ev.location.substring(0, 15) + '...' : ev.location}</button></td>
                                <td class="border p-3">${statusHtml}</td>
                                <td class="border p-3">
                                    ${actionBtn}
                                    <button class="bg-red-500 text-white px-2 py-1 rounded delete-event" data-id="${ev.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            `;
                    tableBody.appendChild(tr);
                });


                bindTableButtons();
                renderPagination();
            }

            function renderPagination() {
                paginationContainer.innerHTML = '';
                const totalPages = Math.ceil(eventsData.length / itemsPerPage);

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = `px-3 py-1 rounded ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'}`;
                    btn.onclick = () => {
                        currentPage = i;
                        renderTablePage(currentPage);
                    };
                    paginationContainer.appendChild(btn);
                }
            }

            const tableBody = document.querySelector('#eventsTable tbody');
            // const fullTitle = document.getElementById('fullEventTitle');
            // const modal = document.getElementById('eventTitleModal');
            // const closeBtn = document.getElementById('closeEventTitleModal');

            const source = new EventSource('/events');

            let eventsData = [];
            let currentPage = 1;
            const itemsPerPage = 5;

            source.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);

                    data.events.forEach(ev => {
                        const index = eventsData.findIndex(e => e.id === ev.id);
                        if (index > -1) {
                            eventsData[index] = ev;
                            allEventsData[index] = ev;
                        } else {
                            eventsData.push(ev);
                            allEventsData.push(ev);
                        }
                    });

                    renderTablePage(currentPage);

                    document.querySelectorAll('.view-event-btn').forEach(btn => {
                        btn.onclick = () => {
                            const ev = JSON.parse(btn.dataset.event);
                        };
                    });
                    document.querySelectorAll('.edit-event').forEach(btn => {
                        btn.onclick = () => {
                            const eventId = btn.dataset.id;
                        };
                    });
                } catch (err) {
                    console.error('Error parsing SSE data:', err);
                }
            };

            source.onerror = (err) => { };

            function formatTime(dateObj) {
                if (!dateObj) return '';
                const hour = dateObj.getHours();
                const minute = dateObj.getMinutes().toString().padStart(2, '0');
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minute} ${ampm}`;
            }

            document.querySelectorAll('.view-event-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const event = JSON.parse(this.dataset.event);

                    document.getElementById('viewTitle').textContent = event.title;
                    document.getElementById('viewDate').textContent = event.event_date;
                    document.getElementById('viewTime').textContent =
                        (event.start_time && event.end_time)
                            ? event.start_time + ' - ' + event.end_time
                            : '';
                    document.getElementById('viewLocation').textContent = event.location || '';
                    document.getElementById('viewDescription').textContent = event.description || '';

                    document.getElementById('viewModal').classList.remove('hidden');
                });
            });

            document.querySelectorAll('.edit-event').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const eventId = this.dataset.id;
                    fetch(`/admin/events/${eventId}/edit`)
                        .then(response => response.json())
                        .then(event => {
                            document.getElementById('eventForm').reset();
                            document.getElementById('eventId').value = event.id;
                            document.getElementById('eventForm').action = `/admin/events/${event.id}`;
                            document.getElementById('formMethod').value = "PUT";
                            document.getElementById('title').value = event.title;
                            document.getElementById('event_date').value = event.event_date;
                            document.getElementById('event_date').readOnly = false;
                            document.getElementById('start_time').value = event.start_time;
                            document.getElementById('end_time').value = event.end_time;
                            document.getElementById('location').value = event.location;
                            document.getElementById('description').value = event.description || '';
                            document.getElementById('formTitle').innerText = "Edit Event";
                            document.getElementById('formModal').classList.remove('hidden');
                            blockTakenTimes(event.event_date);
                        });
                });
            });


        });


        function closeModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function closeFormModal() {
            document.getElementById('formModal').classList.add('hidden');
        }

        let takenRanges = [];

        async function blockTakenTimes(date) {
            document.getElementById('start_time').disabled = true;
            document.getElementById('end_time').disabled = true;

            await fetch(`/admin/events/taken-times?date=${date}`)
                .then(response => response.json())
                .then(events => {
                    takenRanges = events.map(e => ({
                        start: e.start_time,
                        end: e.end_time,
                        date
                    }));
                    document.getElementById('start_time').disabled = false;
                    document.getElementById('end_time').disabled = false;
                });
        }

        // Helper to format time as h:mm AM/PM
        function formatTimeDisplay(timeStr) {
            if (!timeStr) return '';
            const [h, m] = timeStr.split(':');
            let hour = parseInt(h, 10);
            const minute = m;
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;
            return `${hour}:${minute} ${ampm}`;
        }

        // document.getElementById('event_date').addEventListener('change', function () {
        //     blockTakenTimes(this.value);
        // });

        // // Also call blockTakenTimes when the modal opens with the default date
        // document.addEventListener('DOMContentLoaded', function () {
        //     const dateInput = document.getElementById('event_date');
        //     if (dateInput.value) {
        //         blockTakenTimes(dateInput.value);
        //     }
        // });

        document.getElementById('start_time').addEventListener('change', validateTime);
        document.getElementById('end_time').addEventListener('change', validateTime);


        function showTimeError(message) {
            const errorDiv = document.getElementById('timeError');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function hideTimeError() {
            const errorDiv = document.getElementById('timeError');
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');
        }

        let allEventsData = [];

        function validateTime() {
            const title = document.getElementById("title").value;
            const start = document.getElementById('start_time').value;
            const end = document.getElementById('end_time').value;
            const event_date = document.getElementById("event_date").value; // YYYY-MM-DD

            if (!start || !end) {
                hideTimeError();
                return;
            }

            const eventRow = allEventsData.find(ev => ev.title === title && ev.event_date === event_date);

            let filteredRanges = takenRanges;
            if (eventRow) {
                filteredRanges = takenRanges.filter(range =>
                    !(range.start === eventRow.start_time && range.end === eventRow.end_time && range.date === event_date)
                );
            }

            const overlap = filteredRanges.some(range =>
                start < range.end && end > range.start
            );



            if (overlap) {
                showTimeError('The selected time slot is already taken. Please choose a different time.');
                document.getElementById('start_time').value = '';
                document.getElementById('end_time').value = '';
            } else {
                hideTimeError();
            }
        }



        // // Prevent form submission if overlap is detected
        // document.getElementById('eventForm').addEventListener('submit', function (e) {
        //     const start = document.getElementById('start_time').value;
        //     const end = document.getElementById('end_time').value;
        //     const overlap = takenRanges.some(range =>
        //         (start < range.end && end > range.start)
        //     );
        //     if (overlap) {
        //         showTimeError('The selected time slot is already taken. Please choose a different time.');
        //         e.preventDefault();
        //     }
        // });


        document.getElementById('selectAll').addEventListener('change', function () {
            document.querySelectorAll('.event-checkbox').forEach(cb => cb.checked = this.checked);
            toggleTrashBtn();
        });

        document.querySelectorAll('.event-checkbox').forEach(cb => {
            cb.addEventListener('change', toggleTrashBtn);
        });

        function toggleTrashBtn() {
            const anyChecked = Array.from(document.querySelectorAll('.event-checkbox')).some(cb => cb.checked);
            document.getElementById('trashBtn').disabled = !anyChecked;
        }

        document.getElementById('trashBtn').addEventListener('click', function () {
            const checked = Array.from(document.querySelectorAll('.event-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete the selected events?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('events.bulkDelete') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: checked })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Selected events have been deleted.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: 'Failed to delete events.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                }
            });
        });

        @if(session('success') || session('errors'))

            document.addEventListener('DOMContentLoaded', function () {
                let skipped = @json(session('errors') ?? []);
                let success = "{{ session('success') }}";

                let message = success;
                if (skipped.length > 0) {
                    message += "<br><br><strong>Skipped Rows:</strong><ul style='text-align:left'>";
                    skipped.forEach(function (item) {
                        message += "<li>" + item + "</li>";
                    });
                    message += "</ul>";
                }

                Swal.fire({
                    title: 'Event Created',
                    html: message,
                    icon: skipped.length > 0 ? 'warning' : 'success',
                    confirmButtonText: 'OK',
                    width: 600,
                    customClass: {
                        popup: 'rounded-xl'
                    }
                });

                // Show success alert for event creation or update
                @if(session('event_created'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Event Created!',
                        text: '{{ session('event_created') }}',
                        confirmButtonText: 'OK'
                    });
                @endif

                @if(session('event_updated'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Event Updated!',
                        text: '{{ session('event_updated') }}',
                        confirmButtonText: 'OK'
                    });
                @endif
                                            });

        @endif


        @if(session('event_updated'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Event Updated!',
                        text: 'Your event has been successfully updated.',
                        confirmButtonText: 'OK'
                    });
                                            });

        @endif

            @if(session('event_deleted'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Event Deleted!',
                            text: 'The event has been deleted.',
                            confirmButtonText: 'OK'
                        });
                                            });





            @endif

    </script>

    <style>
        /* Customize the calendar header */
        .fc-toolbar-title {
            font-size: 1.8rem;
            /* Slightly larger title */
            font-weight: bold;
            color: #333;
        }

        .fc-button {
            background-color: #007bff !important;
            border: none !important;
            color: #fff !important;
            border-radius: 5px !important;
            padding: 5px 10px !important;
        }

        .fc-button:hover {
            background-color: #0056b3 !important;
        }

        /* Adjust the size of the boxes */
        #calendar .fc-daygrid-day-frame {
            height: 300px !important;
            /* Larger height */
            padding: 20px !important;
            /* More padding */
            background-color: #f8f9fa !important;
            /* Light gray background */
        }

        /* Highlight events with softer colors */
        .fc-event,
        #calendar .fc-event {
            /* Do NOT set background-color here! */
            color: #fff !important;
            /* Ensure text is visible on green/blue */
            border: none !important;
            border-radius: 5px !important;
            padding: 10px !important;
            font-size: 1.2rem !important;
            text-align: center !important;
            font-weight: bold;
        }

        /* Customize the time slots */
        .fc-timegrid-slot {
            background-color: #f8f9fa;
        }

        /* Adjust the size of the boxes */
        /* Adjust the font size and spacing for events */
        .fc-event-title {
            font-size: 1rem;
            font-weight: 500;
            white-space: normal;
            /* Allow text to wrap */
            word-wrap: break-word;
            /* Break long words */
        }

        .fc-event-time {
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Customize the modal */
        #viewModal,
        #formModal {
            z-index: 1050;
            /* Ensure modals appear above the calendar */
        }

        #fullCalendar {
            min-width: 900px;
            /* Adjust as needed for your layout */
            width: 100%;
            overflow-x: auto;
        }

        #calendar-scroll-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .fc-scroller,
        .fc-scrollgrid {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .fc-scroller::-webkit-scrollbar,
        .fc-scrollgrid::-webkit-scrollbar {
            display: none;
        }

        .fc-daygrid-day {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #fff;
        }



        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .fc-button {
            background-color: #ff9800 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 5px !important;
            padding: 5px 10px !important;
        }

        .fc-button:hover {
            background-color: #e68900 !important;
        }


        #timeError {
            transition: opacity 0.3s;
            opacity: 1;
        }

        #timeError.hidden {
            opacity: 0;
        }

        #takenTimes {
            max-width: 100%;
            overflow-x: auto;
            white-space: nowrap;
        }

        #formModal .bg-white {
            max-height: 90vh;
            overflow-y: auto;
        }

        @media (max-width: 600px) {
            #fullCalendar {
                min-width: 700px !important;
                /* Make the calendar wider on mobile for horizontal scroll */
                width: 100%;
                overflow-x: auto;
            }

            .fc-daygrid-day-frame {
                height: 120px !important;
                /* Make day cells taller on mobile */
                padding: 10px !important;
            }

            .fc-daygrid-event {
                font-size: 1.1rem !important;
                /* Larger event text */
                padding: 6px 4px !important;
            }

            .fc-toolbar-title {
                font-size: 1.3rem !important;
                /* Bigger title */
            }

            .fc-daygrid-day-number {
                font-size: 1.1rem !important;
                /* Bigger day numbers */
            }
        }

        @media (max-width: 600px) {
            #formModal .bg-white {
                width: 98vw !important;
                min-width: 0 !important;
                padding: 1rem !important;
                max-height: 95vh;
            }
        }

        @media (max-width: 500px) {
            #formModal .bg-white {
                width: 98vw !important;
                min-width: 0 !important;
                padding: 1rem !important;
            }

            #takenTimes {
                font-size: 0.95rem;
            }
        }

        .fc-daygrid-day-frame {
            display: flex;
            flex-direction: column;
            height: 140px !important;
            /* Adjust as needed */
            padding: 8px !important;
            background: #fff;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
        }

        .fc-daygrid-event-harness {
            max-height: none !important;
            overflow-y: visible !important;
            margin-bottom: 2px;
        }

        .fc-daygrid-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            display: block;
            margin-bottom: 4px;
        }

        .fc-daygrid-day-events {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
            width: 100%;

        }

        .fc-day-disabled {
            background: #f8fafc !important;
            color: #b0b0b0 !important;
            cursor: not-allowed !important;
            position: relative;
            opacity: 1 !important;
            filter: none !important;
        }

        .fc-day-disabled .fc-daygrid-day-number {
            color: #888 !important;
            opacity: 1 !important;
        }

        /* Make disabled day numbers always visible and above the tooltip */
        .fc-day-disabled .fc-daygrid-day-number {
            color: #444 !important;
            opacity: 1 !important;
            position: relative;
            z-index: 2;
            background: transparent !important;
            pointer-events: none;
        }

        .fc-day-disabled:hover .fc-daygrid-day-number {
            z-index: 20;
        }

        .fc-day-disabled:hover::after {
            z-index: 10;
        }

        .fc-day-disabled:hover::after {
            content: "Blocked";
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #e53e3e;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            z-index: 10;
            pointer-events: none;
        }

        /* Responsive adjustments for FullCalendar day cells and events */
        @media (max-width: 900px) {

            #calendar .fc-daygrid-day-frame,
            .fc-daygrid-day-frame {
                height: 100px !important;
                padding: 6px !important;
            }

            .fc-daygrid-event {
                font-size: 0.95rem !important;
                padding: 4px !important;
            }
        }

        @media (max-width: 600px) {

            #calendar .fc-daygrid-day-frame,
            .fc-daygrid-day-frame {
                height: 70px !important;
                padding: 3px !important;
            }

            .fc-daygrid-event {
                font-size: 0.85rem !important;
                padding: 2px 3px !important;
            }

            .fc-toolbar-title {
                font-size: 1.1rem !important;
            }

            .fc-daygrid-day-number {
                font-size: 0.95rem !important;
            }



            #fullCalendar {
                min-width: 350px;
                overflow-x: auto;
            }

            w
        }

        /* Make calendar container scrollable horizontally on small screens */
        #fullCalendar {
            width: 100%;
            overflow-x: auto;
        }
    </style>
@endsection