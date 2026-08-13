const safeText = (value) => {
    return value === null ||
        value === undefined ||
        String(value).trim() === ''
        ? '—'
        : String(value);
};


const escapeHtml = (value = '') => {
    const element =
        document.createElement('div');

    element.textContent =
        value ?? '';

    return element.innerHTML;
};


/*
|--------------------------------------------------------------------------
| Appointment Index
|--------------------------------------------------------------------------
*/

const initialiseAppointmentIndex =
async (apiRequest, user) => {

    const root =
        document.getElementById(
            'appointmentsIndexPage'
        );

    if (!root) {
        return;
    }


    const createLink =
        document.getElementById(
            'createAppointmentLink'
        );

    if (
        ![
            'ADMIN',
            'RECEPTIONIST',
        ].includes(
            user.role?.slug
        )
    ) {
        createLink?.classList.add(
            'hidden'
        );
    }


    const table =
        document.getElementById(
            'appointmentsTableBody'
        );

    const info =
        document.getElementById(
            'appointmentsPaginationInfo'
        );

    const previous =
        document.getElementById(
            'appointmentsPrevPage'
        );

    const next =
        document.getElementById(
            'appointmentsNextPage'
        );

    const errorBox =
        document.getElementById(
            'appointmentsError'
        );

    const filters =
        document.getElementById(
            'appointmentFilters'
        );

    const search =
        document.getElementById(
            'appointmentSearch'
        );

    const status =
        document.getElementById(
            'appointmentStatusFilter'
        );

    const date =
        document.getElementById(
            'appointmentDateFilter'
        );

    const department =
        document.getElementById(
            'appointmentDepartmentFilter'
        );


    let currentPage = 1;
    let lastPage = 1;


    try {

        const departments =
            await apiRequest(
                '/departments?status=active&per_page=100'
            );


        departments.data.forEach(
            (item) => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    item.id;

                option.textContent =
                    item.name;

                department.appendChild(
                    option
                );
            }
        );

    } catch {
        // Main appointment request
        // will display errors if necessary.
    }


    const loadAppointments =
    async (page = 1) => {

        try {

            errorBox.classList.add(
                'hidden'
            );


            const params =
                new URLSearchParams();

            params.set(
                'page',
                String(page)
            );

            params.set(
                'per_page',
                '10'
            );


            if (search.value.trim()) {
                params.set(
                    'search',
                    search.value.trim()
                );
            }


            if (status.value) {
                params.set(
                    'status',
                    status.value
                );
            }


            if (date.value) {
                params.set(
                    'appointment_date',
                    date.value
                );
            }


            if (department.value) {
                params.set(
                    'department_id',
                    department.value
                );
            }


            const response =
                await apiRequest(
                    `/appointments?${params}`
                );


            currentPage =
                Number(
                    response.meta.current_page
                );

            lastPage =
                Number(
                    response.meta.last_page
                );


            table.replaceChildren();


            if (
                response.data.length === 0
            ) {

                const row =
                    document.createElement(
                        'tr'
                    );

                row.innerHTML = `
                    <td
                        colspan="6"
                        class="px-5 py-12 text-center text-sm text-slate-400"
                    >
                        No appointments found.
                    </td>
                `;

                table.appendChild(row);

            } else {

                response.data.forEach(
                    (appointment) => {

                        const row =
                            document.createElement(
                                'tr'
                            );

                        row.className =
                            'text-sm';


                        row.innerHTML = `
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-950">
                                    ${escapeHtml(
                                        appointment.appointment_code
                                    )}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    ${escapeHtml(
                                        appointment.appointment_type
                                    )}
                                </p>
                            </td>


                            <td class="px-5 py-4">
                                ${escapeHtml(
                                    appointment.patient?.full_name
                                    ?? '—'
                                )}
                            </td>


                            <td class="px-5 py-4">
                                ${escapeHtml(
                                    appointment.doctor?.name
                                    ?? '—'
                                )}
                            </td>


                            <td class="px-5 py-4">
                                <p>
                                    ${escapeHtml(
                                        appointment.appointment_date
                                    )}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    ${escapeHtml(
                                        appointment.start_time
                                    )}
                                    -
                                    ${escapeHtml(
                                        appointment.end_time
                                    )}
                                </p>
                            </td>


                            <td class="px-5 py-4">
                                ${escapeHtml(
                                    appointment.status
                                )}
                            </td>


                            <td class="px-5 py-4">

                                <a
                                    href="/appointments/${appointment.id}"
                                    class="font-semibold text-cyan-700"
                                >
                                    View
                                </a>

                            </td>
                        `;


                        table.appendChild(
                            row
                        );
                    }
                );
            }


            info.textContent =
                `Page ${currentPage} of ${lastPage} • ${response.meta.total} appointments`;


            previous.disabled =
                currentPage <= 1;

            next.disabled =
                currentPage >= lastPage;

        } catch (error) {

            errorBox.textContent =
                error.message;

            errorBox.classList.remove(
                'hidden'
            );
        }
    };


    filters.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();

            await loadAppointments(1);
        }
    );


    previous.addEventListener(
        'click',
        async () => {

            if (currentPage > 1) {
                await loadAppointments(
                    currentPage - 1
                );
            }
        }
    );


    next.addEventListener(
        'click',
        async () => {

            if (
                currentPage < lastPage
            ) {
                await loadAppointments(
                    currentPage + 1
                );
            }
        }
    );


    await loadAppointments();
};


/*
|--------------------------------------------------------------------------
| Appointment Form
|--------------------------------------------------------------------------
*/

const initialiseAppointmentForm =
async (apiRequest) => {

    const root =
        document.getElementById(
            'appointmentFormPage'
        );

    if (!root) {
        return;
    }


    const form =
        document.getElementById(
            'appointmentForm'
        );

    const patientSelect =
        document.getElementById(
            'appointmentPatient'
        );

    const departmentSelect =
        document.getElementById(
            'appointmentDepartment'
        );

    const doctorSelect =
        document.getElementById(
            'appointmentDoctor'
        );

    const dateInput =
        document.getElementById(
            'appointmentDate'
        );

    const timeSelect =
        document.getElementById(
            'appointmentTime'
        );

    const button =
        document.getElementById(
            'appointmentSaveButton'
        );

    const errorBox =
        document.getElementById(
            'appointmentFormError'
        );

    const appointmentId =
        root.dataset
            .appointmentId
            ?.trim();


    let existingAppointment =
        null;


    const showError = (message) => {

        errorBox.textContent =
            message;

        errorBox.classList.remove(
            'hidden'
        );
    };


    const loadDoctors =
    async (selectedDoctorId = null) => {

        doctorSelect.replaceChildren();

        const placeholder =
            document.createElement(
                'option'
            );

        placeholder.value = '';

        placeholder.textContent =
            'Select doctor';

        doctorSelect.appendChild(
            placeholder
        );


        if (!departmentSelect.value) {

            doctorSelect.disabled =
                true;

            return;
        }


        const response =
            await apiRequest(
                `/doctors?status=active&department_id=${departmentSelect.value}&per_page=50`
            );


        response.data.forEach(
            (doctor) => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    doctor.id;

                option.textContent =
                    `${doctor.user?.name ?? 'Doctor'}${doctor.specialization
                        ? ` — ${doctor.specialization}`
                        : ''}`;


                doctorSelect.appendChild(
                    option
                );
            }
        );


        doctorSelect.disabled =
            false;


        if (selectedDoctorId) {
            doctorSelect.value =
                String(
                    selectedDoctorId
                );
        }
    };


    const loadSlots =
    async (selectedTime = null) => {

        timeSelect.replaceChildren();

        const placeholder =
            document.createElement(
                'option'
            );

        placeholder.value = '';

        placeholder.textContent =
            'Select available time';

        timeSelect.appendChild(
            placeholder
        );


        if (
            !doctorSelect.value
            || !departmentSelect.value
            || !dateInput.value
        ) {

            timeSelect.disabled =
                true;

            return;
        }


        try {

            const response =
                await apiRequest(
                    `/appointments/availability`
                    + `?doctor_id=${doctorSelect.value}`
                    + `&department_id=${departmentSelect.value}`
                    + `&date=${dateInput.value}`
                );


            response.data.forEach(
                (slot) => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        slot.start_time;

                    option.textContent =
                        slot.label;

                    timeSelect.appendChild(
                        option
                    );
                }
            );


            /*
             * During edit, the current
             * appointment slot may be absent
             * because it is already booked.
             */

            if (
                selectedTime
                && ![
                    ...timeSelect.options
                ].some(
                    (option) =>
                        option.value
                        === selectedTime
                )
            ) {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    selectedTime;

                option.textContent =
                    `${selectedTime} (Current)`;

                timeSelect.appendChild(
                    option
                );
            }


            timeSelect.disabled =
                false;


            if (selectedTime) {
                timeSelect.value =
                    selectedTime;
            }

        } catch (error) {

            showError(
                error.message
            );

            timeSelect.disabled =
                true;
        }
    };


    try {

        const [
            patients,
            departments,
        ] = await Promise.all([
            apiRequest(
                '/patients?status=active&per_page=50'
            ),

            apiRequest(
                '/departments?status=active&per_page=100'
            ),
        ]);


        patients.data.forEach(
            (patient) => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    patient.id;

                option.textContent =
                    `${patient.patient_code} — ${patient.full_name}`;

                patientSelect.appendChild(
                    option
                );
            }
        );


        departments.data.forEach(
            (department) => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    department.id;

                option.textContent =
                    department.name;

                departmentSelect.appendChild(
                    option
                );
            }
        );


        if (appointmentId) {

            const response =
                await apiRequest(
                    `/appointments/${appointmentId}`
                );


            existingAppointment =
                response.data;


            patientSelect.value =
                String(
                    existingAppointment
                        .patient.id
                );


            departmentSelect.value =
                String(
                    existingAppointment
                        .department.id
                );


            dateInput.value =
                existingAppointment
                    .appointment_date;


            form.elements
                .appointment_type
                .value =
                existingAppointment
                    .appointment_type;


            form.elements
                .priority
                .value =
                existingAppointment
                    .priority;


            form.elements
                .reason
                .value =
                existingAppointment
                    .reason ?? '';


            form.elements
                .notes
                .value =
                existingAppointment
                    .notes ?? '';


            await loadDoctors(
                existingAppointment
                    .doctor.id
            );


            await loadSlots(
                existingAppointment
                    .start_time
            );
        }

    } catch (error) {

        showError(
            error.message
        );
    }


    departmentSelect.addEventListener(
        'change',
        async () => {

            await loadDoctors();

            await loadSlots();
        }
    );


    doctorSelect.addEventListener(
        'change',
        () => loadSlots()
    );


    dateInput.addEventListener(
        'change',
        () => loadSlots()
    );


    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            errorBox.classList.add(
                'hidden'
            );


            button.disabled =
                true;

            button.textContent =
                appointmentId
                    ? 'Updating...'
                    : 'Booking...';


            const payload =
                Object.fromEntries(
                    new FormData(
                        form
                    ).entries()
                );


            payload.patient_id =
                Number(
                    payload.patient_id
                );

            payload.doctor_id =
                Number(
                    payload.doctor_id
                );

            payload.department_id =
                Number(
                    payload.department_id
                );


            Object.keys(payload)
                .forEach((key) => {

                    if (
                        payload[key] === ''
                    ) {
                        payload[key] =
                            null;
                    }
                });


            try {

                const response =
                    await apiRequest(
                        appointmentId
                            ? `/appointments/${appointmentId}`
                            : '/appointments',
                        {
                            method:
                                appointmentId
                                    ? 'PUT'
                                    : 'POST',

                            body:
                                JSON.stringify(
                                    payload
                                ),
                        }
                    );


                window.location.href =
                    `/appointments/${response.data.id}`;

            } catch (error) {

                showError(
                    error.message
                );

            } finally {

                button.disabled =
                    false;

                button.textContent =
                    appointmentId
                        ? 'Update Appointment'
                        : 'Save Appointment';
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Appointment Details / Status
|--------------------------------------------------------------------------
*/

const initialiseAppointmentShow =
async (apiRequest, user) => {

    const root =
        document.getElementById(
            'appointmentShowPage'
        );

    if (!root) {
        return;
    }


    const appointmentId =
        root.dataset.appointmentId;

    const errorBox =
        document.getElementById(
            'appointmentShowError'
        );

    const actionContainer =
        document.getElementById(
            'appointmentActions'
        );

    const editLink =
        document.getElementById(
            'appointmentEditLink'
        );


    const setText = (
        id,
        value
    ) => {

        const element =
            document.getElementById(id);

        if (element) {
            element.textContent =
                safeText(value);
        }
    };


    const roleActions = {
        ADMIN: [
            'CHECKED_IN',
            'IN_PROGRESS',
            'COMPLETED',
            'CANCELLED',
            'NO_SHOW',
        ],

        RECEPTIONIST: [
            'CHECKED_IN',
            'CANCELLED',
            'NO_SHOW',
        ],

        NURSE: [
            'CHECKED_IN',
        ],

        DOCTOR: [
            'IN_PROGRESS',
            'COMPLETED',
            'CANCELLED',
        ],
    };


    const transitions = {
        SCHEDULED: [
            'CHECKED_IN',
            'CANCELLED',
            'NO_SHOW',
        ],

        CHECKED_IN: [
            'IN_PROGRESS',
            'CANCELLED',
        ],

        IN_PROGRESS: [
            'COMPLETED',
            'CANCELLED',
        ],

        COMPLETED: [],

        CANCELLED: [],

        NO_SHOW: [],
    };


    const labels = {
        CHECKED_IN:
            'Check In',

        IN_PROGRESS:
            'Start Consultation',

        COMPLETED:
            'Complete',

        CANCELLED:
            'Cancel Appointment',

        NO_SHOW:
            'Mark No Show',
    };


    const loadAppointment =
    async () => {

        try {

            errorBox.classList.add(
                'hidden'
            );


            const response =
                await apiRequest(
                    `/appointments/${appointmentId}`
                );


            const appointment =
                response.data;


            setText(
                'appointmentCode',
                appointment.appointment_code
            );

            setText(
                'appointmentPatientName',
                appointment.patient?.full_name
            );

            setText(
                'appointmentStatus',
                appointment.status
            );

            setText(
                'appointmentDoctorName',
                appointment.doctor?.name
            );

            setText(
                'appointmentDepartmentName',
                appointment.department?.name
            );

            setText(
                'appointmentDateValue',
                appointment.appointment_date
            );

            setText(
                'appointmentTimeValue',
                `${appointment.start_time} - ${appointment.end_time}`
            );

            setText(
                'appointmentTypeValue',
                appointment.appointment_type
            );

            setText(
                'appointmentPriorityValue',
                appointment.priority
            );

            setText(
                'appointmentPatientPhone',
                appointment.patient?.phone
            );

            setText(
                'appointmentCreatedBy',
                appointment.created_by?.name
            );

            setText(
                'appointmentReason',
                appointment.reason
            );

            setText(
                'appointmentNotes',
                appointment.notes
            );


            if (
                [
                    'ADMIN',
                    'RECEPTIONIST',
                ].includes(
                    user.role?.slug
                )
                &&
                appointment.status
                === 'SCHEDULED'
            ) {

                editLink.href =
                    `/appointments/${appointment.id}/edit`;

                editLink.classList.remove(
                    'hidden'
                );

            } else {

                editLink.classList.add(
                    'hidden'
                );
            }


            actionContainer
                .replaceChildren();


            const allowedForRole =
                roleActions[
                    user.role?.slug
                ] ?? [];


            const allowedTransitions =
                transitions[
                    appointment.status
                ] ?? [];


            const actions =
                allowedTransitions
                    .filter(
                        (status) =>
                            allowedForRole
                                .includes(
                                    status
                                )
                    );


            if (actions.length === 0) {

                const text =
                    document.createElement(
                        'p'
                    );

                text.className =
                    'text-sm text-slate-400';

                text.textContent =
                    'No workflow actions are currently available.';

                actionContainer
                    .appendChild(text);

                return;
            }


            actions.forEach(
                (newStatus) => {

                    const button =
                        document.createElement(
                            'button'
                        );

                    button.type =
                        'button';

                    button.textContent =
                        labels[newStatus];

                    button.className =
                        newStatus
                        === 'CANCELLED'
                            ? 'rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50'
                            : 'rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800';


                    button.addEventListener(
                        'click',
                        async () => {

                            let cancellationReason =
                                null;


                            if (
                                newStatus
                                === 'CANCELLED'
                            ) {

                                cancellationReason =
                                    window.prompt(
                                        'Enter cancellation reason:'
                                    );


                                if (
                                    !cancellationReason
                                    || !cancellationReason
                                        .trim()
                                ) {
                                    return;
                                }
                            }


                            if (
                                !window.confirm(
                                    `Change appointment status to ${labels[newStatus]}?`
                                )
                            ) {
                                return;
                            }


                            try {

                                button.disabled =
                                    true;


                                await apiRequest(
                                    `/appointments/${appointmentId}/status`,
                                    {
                                        method:
                                            'PATCH',

                                        body:
                                            JSON.stringify({
                                                status:
                                                    newStatus,

                                                cancellation_reason:
                                                    cancellationReason,
                                            }),
                                    }
                                );


                                await loadAppointment();

                            } catch (error) {

                                errorBox.textContent =
                                    error.message;

                                errorBox.classList.remove(
                                    'hidden'
                                );

                                button.disabled =
                                    false;
                            }
                        }
                    );


                    actionContainer
                        .appendChild(button);
                }
            );

        } catch (error) {

            errorBox.textContent =
                error.message;

            errorBox.classList.remove(
                'hidden'
            );
        }
    };


    await loadAppointment();
};


/*
|--------------------------------------------------------------------------
| Public Initialiser
|--------------------------------------------------------------------------
*/

export const initialiseAppointmentPages =
async (
    apiRequest,
    user
) => {

    await initialiseAppointmentIndex(
        apiRequest,
        user
    );


    await initialiseAppointmentForm(
        apiRequest
    );


    await initialiseAppointmentShow(
        apiRequest,
        user
    );
};