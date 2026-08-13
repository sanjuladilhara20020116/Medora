const textValue = (value) =>
    value === null ||
    value === undefined ||
    value === ''
        ? '—'
        : String(value);


export const initialiseDoctorPages =
async (apiRequest, user) => {

    await initialiseDoctorIndex(
        apiRequest,
        user
    );

    await initialiseDoctorForm(
        apiRequest
    );

    await initialiseDoctorShow(
        apiRequest,
        user
    );
};


const initialiseDoctorIndex =
async (apiRequest, user) => {

    const root =
        document.getElementById(
            'doctorsIndexPage'
        );

    if (!root) {
        return;
    }


    const table =
        document.getElementById(
            'doctorsTableBody'
        );

    const search =
        document.getElementById(
            'doctorSearch'
        );

    const departmentFilter =
        document.getElementById(
            'doctorDepartmentFilter'
        );

    const statusFilter =
        document.getElementById(
            'doctorStatusFilter'
        );

    const createLink =
        document.getElementById(
            'createDoctorLink'
        );


    if (
        user.role?.slug !== 'ADMIN'
    ) {
        createLink?.classList.add(
            'hidden'
        );
    }


    const departmentResponse =
        await apiRequest(
            '/departments?status=active&per_page=100'
        );


    departmentResponse.data.forEach(
        (department) => {

            const option =
                document.createElement(
                    'option'
                );

            option.value =
                department.id;

            option.textContent =
                department.name;

            departmentFilter
                .appendChild(option);
        }
    );


    const loadDoctors = async () => {

        const params =
            new URLSearchParams();


        if (search.value.trim()) {
            params.set(
                'search',
                search.value.trim()
            );
        }


        if (departmentFilter.value) {
            params.set(
                'department_id',
                departmentFilter.value
            );
        }


        if (statusFilter.value) {
            params.set(
                'status',
                statusFilter.value
            );
        }


        const response =
            await apiRequest(
                `/doctors?${params}`
            );


        table.replaceChildren();


        response.data.forEach(
            (doctor) => {

                const row =
                    document.createElement(
                        'tr'
                    );

                row.className =
                    'text-sm';


                const departments =
                    doctor.departments
                        ?.map(
                            (department) =>
                                department.name
                        )
                        .join(', ')
                    || '—';


                row.innerHTML = `
                    <td class="px-5 py-4">
                        <p class="font-semibold text-slate-950">
                            ${doctor.user?.name ?? '—'}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            ${doctor.doctor_code ?? '—'}
                        </p>
                    </td>

                    <td class="px-5 py-4">
                        ${doctor.specialization ?? '—'}
                    </td>

                    <td class="px-5 py-4">
                        ${departments}
                    </td>

                    <td class="px-5 py-4">
                        ${doctor.room_number ?? '—'}
                    </td>

                    <td class="px-5 py-4">
                        ${doctor.is_active
                            ? 'Active'
                            : 'Inactive'}
                    </td>

                    <td class="px-5 py-4">
                        <a
                            href="/doctors/${doctor.id}"
                            class="font-semibold text-cyan-700"
                        >
                            View
                        </a>
                    </td>
                `;


                table.appendChild(row);
            }
        );
    };


    [
        search,
        departmentFilter,
        statusFilter,
    ].forEach((element) => {

        element.addEventListener(
            'input',
            loadDoctors
        );

        element.addEventListener(
            'change',
            loadDoctors
        );
    });


    await loadDoctors();
};


const initialiseDoctorForm =
async (apiRequest) => {

    const root =
        document.getElementById(
            'doctorFormPage'
        );

    if (!root) {
        return;
    }


    const doctorId =
        root.dataset.doctorId;

    const form =
        document.getElementById(
            'doctorForm'
        );

    const departmentContainer =
        document.getElementById(
            'doctorDepartmentCheckboxes'
        );

    const primarySelect =
        document.getElementById(
            'primaryDepartment'
        );

    const errorBox =
        document.getElementById(
            'doctorFormError'
        );


    const departments =
        await apiRequest(
            '/departments?status=active&per_page=100'
        );


    departmentContainer
        .replaceChildren();


    departments.data.forEach(
        (department) => {

            const label =
                document.createElement(
                    'label'
                );

            label.className =
                'flex items-center gap-3 rounded-xl border border-slate-200 p-3';


            label.innerHTML = `
                <input
                    type="checkbox"
                    value="${department.id}"
                    data-department
                >

                <span>${department.name}</span>
            `;


            departmentContainer
                .appendChild(label);


            const option =
                document.createElement(
                    'option'
                );

            option.value =
                department.id;

            option.textContent =
                department.name;

            primarySelect
                .appendChild(option);
        }
    );


    if (doctorId) {

        const response =
            await apiRequest(
                `/doctors/${doctorId}`
            );


        const doctor =
            response.data;


        const values = {
            name:
                doctor.user?.name,

            username:
                doctor.user?.username,

            email:
                doctor.user?.email,

            phone:
                doctor.user?.phone,

            registration_number:
                doctor.registration_number,

            designation:
                doctor.designation,

            specialization:
                doctor.specialization,

            qualifications:
                doctor.qualifications,

            experience_years:
                doctor.experience_years,

            consultation_fee:
                doctor.consultation_fee,

            room_number:
                doctor.room_number,

            biography:
                doctor.biography,

            joined_at:
                doctor.joined_at,
        };


        Object.entries(values)
            .forEach(
                ([name, value]) => {

                    const field =
                        form.elements
                            .namedItem(name);

                    if (field) {
                        field.value =
                            value ?? '';
                    }
                }
            );


        doctor.departments.forEach(
            (department) => {

                const checkbox =
                    departmentContainer
                        .querySelector(
                            `[data-department][value="${department.id}"]`
                        );

                if (checkbox) {
                    checkbox.checked =
                        true;
                }


                if (
                    department.is_primary
                ) {
                    primarySelect.value =
                        department.id;
                }
            }
        );
    }


    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            const formData =
                new FormData(form);


            const payload =
                Object.fromEntries(
                    formData.entries()
                );


            const selectedDepartments =
                [
                    ...departmentContainer
                        .querySelectorAll(
                            '[data-department]:checked'
                        ),
                ]
                .map(
                    (checkbox) =>
                        Number(
                            checkbox.value
                        )
                );


            payload.department_ids =
                selectedDepartments;

            payload.primary_department_id =
                Number(
                    primarySelect.value
                );


            Object.keys(payload)
                .forEach((key) => {

                    if (
                        payload[key] === ''
                    ) {
                        payload[key] = null;
                    }
                });


            try {

                const response =
                    await apiRequest(
                        doctorId
                            ? `/doctors/${doctorId}`
                            : '/doctors',
                        {
                            method:
                                doctorId
                                    ? 'PUT'
                                    : 'POST',

                            body:
                                JSON.stringify(
                                    payload
                                ),
                        }
                    );


                window.location.href =
                    `/doctors/${response.data.id}`;

            } catch (error) {

                errorBox.textContent =
                    error.message;

                errorBox.classList.remove(
                    'hidden'
                );
            }
        }
    );
};


const initialiseDoctorShow =
async (apiRequest, user) => {

    const root =
        document.getElementById(
            'doctorShowPage'
        );

    if (!root) {
        return;
    }


    const doctorId =
        root.dataset.doctorId;

    const errorBox =
        document.getElementById(
            'doctorShowError'
        );

    const scheduleForm =
        document.getElementById(
            'doctorScheduleForm'
        );

    const scheduleDepartment =
        document.getElementById(
            'scheduleDepartment'
        );

    const schedulesContainer =
        document.getElementById(
            'doctorSchedules'
        );


    const setText = (
        id,
        value
    ) => {

        const element =
            document.getElementById(id);

        if (element) {
            element.textContent =
                textValue(value);
        }
    };


    const loadDoctor = async () => {

        try {

            const response =
                await apiRequest(
                    `/doctors/${doctorId}`
                );


            const doctor =
                response.data;


            setText(
                'doctorCode',
                doctor.doctor_code
            );

            setText(
                'doctorName',
                doctor.user?.name
            );

            setText(
                'doctorSpecialization',
                doctor.specialization
            );

            setText(
                'doctorRegistration',
                doctor.registration_number
            );

            setText(
                'doctorPhone',
                doctor.user?.phone
            );

            setText(
                'doctorRoom',
                doctor.room_number
            );


            setText(
                'doctorDepartments',
                doctor.departments
                    .map(
                        (department) =>
                            department.name
                    )
                    .join(', ')
            );


            const editLink =
                document.getElementById(
                    'doctorEditLink'
                );


            if (
                user.role?.slug ===
                'ADMIN'
            ) {

                editLink.href =
                    `/doctors/${doctor.id}/edit`;

            } else {

                editLink.classList.add(
                    'hidden'
                );

                scheduleForm.classList.add(
                    'hidden'
                );
            }


            scheduleDepartment
                .replaceChildren();


            doctor.departments.forEach(
                (department) => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        department.id;

                    option.textContent =
                        department.name;

                    scheduleDepartment
                        .appendChild(option);
                }
            );


            schedulesContainer
                .replaceChildren();


            doctor.schedules.forEach(
                (schedule) => {

                    const row =
                        document.createElement(
                            'div'
                        );

                    row.className =
                        'flex items-center justify-between gap-4 py-4';


                    const text =
                        document.createElement(
                            'div'
                        );


                    text.innerHTML = `
                        <p class="font-semibold">
                            ${schedule.day_of_week}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            ${schedule.start_time}
                            -
                            ${schedule.end_time}
                            ·
                            ${schedule.department}
                        </p>
                    `;


                    row.appendChild(text);


                    if (
                        user.role?.slug ===
                        'ADMIN'
                    ) {

                        const button =
                            document.createElement(
                                'button'
                            );

                        button.textContent =
                            'Remove';

                        button.className =
                            'text-sm font-semibold text-red-600';


                        button.addEventListener(
                            'click',
                            async () => {

                                if (
                                    !window.confirm(
                                        'Remove this schedule?'
                                    )
                                ) {
                                    return;
                                }


                                await apiRequest(
                                    `/doctors/${doctorId}/schedules/${schedule.id}`,
                                    {
                                        method:
                                            'DELETE',
                                    }
                                );


                                await loadDoctor();
                            }
                        );


                        row.appendChild(
                            button
                        );
                    }


                    schedulesContainer
                        .appendChild(row);
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


    scheduleForm.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            const payload =
                Object.fromEntries(
                    new FormData(
                        scheduleForm
                    ).entries()
                );


            payload.department_id =
                Number(
                    payload.department_id
                );

            payload.slot_duration_minutes =
                Number(
                    payload.slot_duration_minutes
                );


            if (
                payload.max_appointments
            ) {
                payload.max_appointments =
                    Number(
                        payload.max_appointments
                    );
            }


            try {

                await apiRequest(
                    `/doctors/${doctorId}/schedules`,
                    {
                        method: 'POST',

                        body:
                            JSON.stringify(
                                payload
                            ),
                    }
                );


                scheduleForm.reset();

                await loadDoctor();

            } catch (error) {

                errorBox.textContent =
                    error.message;

                errorBox.classList.remove(
                    'hidden'
                );
            }
        }
    );


    await loadDoctor();
};