/*
|--------------------------------------------------------------------------
| Patient Helpers
|--------------------------------------------------------------------------
*/

const escapeHtml = (value = '') => {
    const element = document.createElement('div');

    element.textContent = value ?? '';

    return element.innerHTML;
};


const displayValue = (value) => {
    if (
        value === null ||
        value === undefined ||
        String(value).trim() === ''
    ) {
        return '—';
    }

    return String(value);
};


/*
|--------------------------------------------------------------------------
| Patient List
|--------------------------------------------------------------------------
*/

const initialisePatientIndex = async (apiRequest) => {
    const root =
        document.getElementById('patientsIndexPage');

    if (!root) {
        return;
    }


    const tbody =
        document.getElementById('patientsTableBody');

    const errorBox =
        document.getElementById('patientsError');

    const info =
        document.getElementById('patientsPaginationInfo');

    const previous =
        document.getElementById('patientsPrevPage');

    const next =
        document.getElementById('patientsNextPage');

    const filtersForm =
        document.getElementById('patientFilters');

    const searchInput =
        document.getElementById('patientSearch');

    const statusFilter =
        document.getElementById('patientStatusFilter');

    const genderFilter =
        document.getElementById('patientGenderFilter');


    let currentPage = 1;
    let lastPage = 1;


    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message ?? 'Unable to load patients.';

        errorBox.classList.remove('hidden');
    };


    const hideError = () => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';

        errorBox.classList.add('hidden');
    };


    const renderEmptyState = () => {
        if (!tbody) {
            return;
        }

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="px-5 py-12 text-center text-sm text-slate-400"
                >
                    No patients found.
                </td>
            </tr>
        `;
    };


    const renderPatients = (patients = []) => {
        if (!tbody) {
            return;
        }

        if (patients.length === 0) {
            renderEmptyState();
            return;
        }


        tbody.innerHTML =
            patients.map((patient) => {

                const statusClass =
                    patient.is_active
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-red-50 text-red-700';

                const statusText =
                    patient.is_active
                        ? 'Active'
                        : 'Inactive';


                return `
                    <tr class="text-sm">

                        <td class="px-5 py-4">

                            <a
                                href="/patients/${patient.id}"
                                class="font-semibold text-slate-950 hover:text-cyan-700"
                            >
                                ${escapeHtml(
                                    displayValue(
                                        patient.full_name
                                    )
                                )}
                            </a>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                ${escapeHtml(
                                    displayValue(
                                        patient.patient_code
                                    )
                                )}
                            </p>

                        </td>


                        <td class="px-5 py-4">

                            ${escapeHtml(
                                displayValue(
                                    patient.phone
                                )
                            )}

                        </td>


                        <td class="px-5 py-4">

                            ${escapeHtml(
                                displayValue(
                                    patient.date_of_birth
                                )
                            )}

                        </td>


                        <td class="px-5 py-4">

                            ${escapeHtml(
                                displayValue(
                                    patient.gender
                                )
                            )}

                        </td>


                        <td class="px-5 py-4">

                            ${escapeHtml(
                                displayValue(
                                    patient.blood_group
                                )
                            )}

                        </td>


                        <td class="px-5 py-4">

                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold ${statusClass}"
                            >
                                ${statusText}
                            </span>

                        </td>


                        <td class="px-5 py-4">

                            <a
                                href="/patients/${patient.id}"
                                class="font-semibold text-cyan-700 hover:text-cyan-800"
                            >
                                View
                            </a>

                        </td>

                    </tr>
                `;
            }).join('');
    };


    const loadPatients = async (page = 1) => {
        try {

            hideError();


            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td
                            colspan="7"
                            class="px-5 py-10 text-center text-sm text-slate-400"
                        >
                            Loading patients...
                        </td>
                    </tr>
                `;
            }


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


            const search =
                searchInput?.value.trim() ?? '';

            const status =
                statusFilter?.value ?? '';

            const gender =
                genderFilter?.value ?? '';


            if (search) {
                params.set(
                    'search',
                    search
                );
            }


            if (status) {
                params.set(
                    'status',
                    status
                );
            }


            if (gender) {
                params.set(
                    'gender',
                    gender
                );
            }


            const response =
                await apiRequest(
                    `/patients?${params.toString()}`
                );


            currentPage =
                Number(
                    response.meta?.current_page ?? 1
                );

            lastPage =
                Number(
                    response.meta?.last_page ?? 1
                );


            renderPatients(
                response.data ?? []
            );


            if (info) {

                const total =
                    response.meta?.total ?? 0;

                info.textContent =
                    `Page ${currentPage} of ${lastPage} • ${total} patients`;
            }


            if (previous) {
                previous.disabled =
                    currentPage <= 1;
            }


            if (next) {
                next.disabled =
                    currentPage >= lastPage;
            }

        } catch (error) {

            renderEmptyState();

            showError(
                error.message ??
                'Unable to load patients.'
            );
        }
    };


    filtersForm?.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();

            await loadPatients(1);
        }
    );


    previous?.addEventListener(
        'click',
        async () => {

            if (currentPage > 1) {

                await loadPatients(
                    currentPage - 1
                );
            }
        }
    );


    next?.addEventListener(
        'click',
        async () => {

            if (currentPage < lastPage) {

                await loadPatients(
                    currentPage + 1
                );
            }
        }
    );


    await loadPatients(1);
};


/*
|--------------------------------------------------------------------------
| Patient Create / Edit Form
|--------------------------------------------------------------------------
*/

const initialisePatientForm = async (
    apiRequest
) => {

    const root =
        document.getElementById('patientFormPage');

    if (!root) {
        return;
    }


    const form =
        document.getElementById('patientForm');

    const errorBox =
        document.getElementById(
            'patientFormError'
        );

    const button =
        document.getElementById(
            'patientSaveButton'
        );

    const patientId =
        root.dataset.patientId?.trim();


    if (!form) {
        return;
    }


    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message ??
            'Unable to save patient.';

        errorBox.classList.remove(
            'hidden'
        );

        errorBox.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    };


    const hideError = () => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';

        errorBox.classList.add(
            'hidden'
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Load Existing Patient for Edit
    |--------------------------------------------------------------------------
    */

    if (patientId) {

        try {

            const response =
                await apiRequest(
                    `/patients/${patientId}`
                );

            const patient =
                response.data;


            Object.entries(patient)
                .forEach(
                    ([key, value]) => {

                        const field =
                            form.elements.namedItem(
                                key
                            );


                        if (!field) {
                            return;
                        }


                        if (
                            typeof value ===
                            'object'
                        ) {
                            return;
                        }


                        if (
                            value === null ||
                            value === undefined
                        ) {
                            field.value = '';
                            return;
                        }


                        field.value =
                            String(value);
                    }
                );

        } catch (error) {

            showError(
                error.message ??
                'Patient information could not be loaded.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save Patient
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();

            hideError();


            if (button) {
                button.disabled = true;

                button.textContent =
                    patientId
                        ? 'Updating...'
                        : 'Saving...';
            }


            const formData =
                new FormData(form);


            const payload =
                Object.fromEntries(
                    formData.entries()
                );


            Object.keys(payload)
                .forEach((key) => {

                    if (
                        typeof payload[key] ===
                        'string'
                    ) {

                        payload[key] =
                            payload[key].trim();
                    }


                    if (payload[key] === '') {

                        payload[key] =
                            null;
                    }
                });


            try {

                const response =
                    await apiRequest(
                        patientId
                            ? `/patients/${patientId}`
                            : '/patients',
                        {
                            method:
                                patientId
                                    ? 'PUT'
                                    : 'POST',

                            /*
                             * Keep JSON.stringify here.
                             *
                             * app.js does NOT stringify
                             * this body again.
                             */
                            body:
                                JSON.stringify(
                                    payload
                                ),
                        }
                    );


                const savedPatientId =
                    response.data?.id;


                if (!savedPatientId) {

                    throw new Error(
                        'Patient was saved, but no patient ID was returned.'
                    );
                }


                window.location.href =
                    `/patients/${savedPatientId}`;

            } catch (error) {

                showError(
                    error.message ??
                    'Patient could not be saved.'
                );

            } finally {

                if (button) {

                    button.disabled = false;

                    button.textContent =
                        patientId
                            ? 'Update Patient'
                            : 'Save Patient';
                }
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Patient Details
|--------------------------------------------------------------------------
*/

const initialisePatientShow = async (
    apiRequest,
    user
) => {

    const root =
        document.getElementById(
            'patientShowPage'
        );

    if (!root) {
        return;
    }


    const patientId =
        root.dataset.patientId?.trim();

    if (!patientId) {
        return;
    }


    const errorBox =
        document.getElementById(
            'patientShowError'
        );

    const archiveButton =
        document.getElementById(
            'archivePatientButton'
        );

    const documentForm =
        document.getElementById(
            'patientDocumentForm'
        );

    const documentsContainer =
        document.getElementById(
            'patientDocuments'
        );


    const showError = (message) => {

        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message ??
            'Something went wrong.';

        errorBox.classList.remove(
            'hidden'
        );
    };


    const hideError = () => {

        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';

        errorBox.classList.add(
            'hidden'
        );
    };


    const setText = (
        id,
        value
    ) => {

        const element =
            document.getElementById(id);

        if (element) {

            element.textContent =
                displayValue(value);
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Render Documents
    |--------------------------------------------------------------------------
    */

    const renderDocuments = (
        patientDocuments = []
    ) => {

        if (!documentsContainer) {
            return;
        }


        documentsContainer.replaceChildren();


        if (patientDocuments.length === 0) {

            const empty =
                document.createElement('p');

            empty.className =
                'py-4 text-sm text-slate-400';

            empty.textContent =
                'No documents uploaded.';

            documentsContainer.appendChild(
                empty
            );

            return;
        }


        patientDocuments.forEach(
            (patientDocument) => {

                /*
                 * IMPORTANT BUG FIX:
                 *
                 * We use "patientDocument"
                 * instead of "document".
                 *
                 * This keeps the browser's
                 * global document object available.
                 */

                const row =
                    document.createElement(
                        'div'
                    );

                row.className =
                    'flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between';


                const details =
                    document.createElement(
                        'div'
                    );


                const title =
                    document.createElement(
                        'a'
                    );

                title.href =
                    patientDocument.file_url;

                title.target =
                    '_blank';

                title.rel =
                    'noopener noreferrer';

                title.className =
                    'text-sm font-semibold text-cyan-700 hover:text-cyan-800';

                title.textContent =
                    displayValue(
                        patientDocument.title
                    );


                const type =
                    document.createElement(
                        'p'
                    );

                type.className =
                    'mt-1 text-xs text-slate-400';

                type.textContent =
                    displayValue(
                        patientDocument.document_type
                    );


                const fileName =
                    document.createElement(
                        'p'
                    );

                fileName.className =
                    'mt-1 text-xs text-slate-400';

                fileName.textContent =
                    displayValue(
                        patientDocument.file_name
                    );


                details.append(
                    title,
                    type,
                    fileName
                );


                row.appendChild(
                    details
                );


                /*
                |--------------------------------------------------------------------------
                | ADMIN Document Delete Button
                |--------------------------------------------------------------------------
                */

                if (
                    user.role?.slug ===
                    'ADMIN'
                ) {

                    const deleteButton =
                        document.createElement(
                            'button'
                        );

                    deleteButton.type =
                        'button';

                    deleteButton.className =
                        'self-start rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 sm:self-auto';

                    deleteButton.textContent =
                        'Delete';


                    deleteButton.addEventListener(
                        'click',
                        async () => {

                            const confirmed =
                                window.confirm(
                                    'Delete this patient document?'
                                );


                            if (!confirmed) {
                                return;
                            }


                            deleteButton.disabled =
                                true;

                            deleteButton.textContent =
                                'Deleting...';


                            try {

                                hideError();


                                await apiRequest(
                                    `/patients/${patientId}/documents/${patientDocument.id}`,
                                    {
                                        method:
                                            'DELETE',
                                    }
                                );


                                await loadPatient();

                            } catch (error) {

                                showError(
                                    error.message ??
                                    'Document could not be deleted.'
                                );

                                deleteButton.disabled =
                                    false;

                                deleteButton.textContent =
                                    'Delete';
                            }
                        }
                    );


                    row.appendChild(
                        deleteButton
                    );
                }


                documentsContainer.appendChild(
                    row
                );
            }
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Load Patient
    |--------------------------------------------------------------------------
    */

    const loadPatient = async () => {

        try {

            hideError();


            const response =
                await apiRequest(
                    `/patients/${patientId}`
                );


            const patient =
                response.data;


            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            setText(
                'patientCode',
                patient.patient_code
            );


            setText(
                'patientName',
                patient.full_name
            );


            setText(
                'patientStatus',
                patient.is_active
                    ? 'Active Patient'
                    : 'Inactive Patient'
            );


            setText(
                'patientPhone',
                patient.phone
            );


            setText(
                'patientEmail',
                patient.email
            );


            setText(
                'patientDOB',
                patient.date_of_birth
            );


            setText(
                'patientGender',
                patient.gender
            );


            setText(
                'patientBlood',
                patient.blood_group
            );


            setText(
                'patientNIC',
                patient.nic_passport
            );


            setText(
                'patientCity',
                patient.city
            );


            /*
            |--------------------------------------------------------------------------
            | Emergency Contact
            |--------------------------------------------------------------------------
            */

            const emergencyName =
                patient.emergency_contact_name;

            const emergencyPhone =
                patient.emergency_contact_phone;


            let emergencyText = '—';


            if (
                emergencyName &&
                emergencyPhone
            ) {

                emergencyText =
                    `${emergencyName} - ${emergencyPhone}`;

            } else if (emergencyName) {

                emergencyText =
                    emergencyName;

            } else if (emergencyPhone) {

                emergencyText =
                    emergencyPhone;
            }


            setText(
                'patientEmergency',
                emergencyText
            );


            /*
            |--------------------------------------------------------------------------
            | Medical Information
            |--------------------------------------------------------------------------
            */

            setText(
                'patientAllergies',
                patient.allergies
            );


            setText(
                'patientConditions',
                patient.chronic_conditions
            );


            setText(
                'patientNotes',
                patient.notes
            );


            /*
            |--------------------------------------------------------------------------
            | Edit Permission
            |--------------------------------------------------------------------------
            */

            const editLink =
                document.getElementById(
                    'patientEditLink'
                );


            if (editLink) {

                if (
                    [
                        'ADMIN',
                        'RECEPTIONIST',
                    ].includes(
                        user.role?.slug
                    )
                ) {

                    editLink.href =
                        `/patients/${patient.id}/edit`;

                    editLink.classList.remove(
                        'hidden'
                    );

                } else {

                    editLink.classList.add(
                        'hidden'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Archive Permission
            |--------------------------------------------------------------------------
            */

            if (archiveButton) {

                if (
                    user.role?.slug ===
                    'ADMIN'
                ) {

                    archiveButton.classList.remove(
                        'hidden'
                    );

                } else {

                    archiveButton.classList.add(
                        'hidden'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Patient Documents
            |--------------------------------------------------------------------------
            */

            renderDocuments(
                patient.documents ?? []
            );

        } catch (error) {

            showError(
                error.message ??
                'Patient information could not be loaded.'
            );
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Archive Patient
    |--------------------------------------------------------------------------
    */

    archiveButton?.addEventListener(
        'click',
        async () => {

            const confirmed =
                window.confirm(
                    'Archive this patient?'
                );


            if (!confirmed) {
                return;
            }


            archiveButton.disabled = true;

            archiveButton.textContent =
                'Archiving...';


            try {

                hideError();


                await apiRequest(
                    `/patients/${patientId}`,
                    {
                        method:
                            'DELETE',
                    }
                );


                window.location.href =
                    '/patients';

            } catch (error) {

                showError(
                    error.message ??
                    'Patient could not be archived.'
                );


                archiveButton.disabled =
                    false;

                archiveButton.textContent =
                    'Archive';
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Upload Patient Document
    |--------------------------------------------------------------------------
    */

    documentForm?.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();

            hideError();


            const submitButton =
                documentForm.querySelector(
                    'button[type="submit"]'
                );


            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.textContent =
                    'Uploading...';
            }


            const formData =
                new FormData(
                    documentForm
                );


            try {

                /*
                 * FormData is passed directly.
                 *
                 * app.js correctly avoids setting
                 * application/json for FormData.
                 */

                await apiRequest(
                    `/patients/${patientId}/documents`,
                    {
                        method:
                            'POST',

                        body:
                            formData,
                    }
                );


                documentForm.reset();


                await loadPatient();

            } catch (error) {

                showError(
                    error.message ??
                    'Document could not be uploaded.'
                );

            } finally {

                if (submitButton) {

                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        'Upload';
                }
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */

    await loadPatient();
};


/*
|--------------------------------------------------------------------------
| Initialise Patient Pages
|--------------------------------------------------------------------------
*/

export const initialisePatientPages =
    async (
        apiRequest,
        user
    ) => {

        await initialisePatientIndex(
            apiRequest
        );


        await initialisePatientForm(
            apiRequest
        );


        await initialisePatientShow(
            apiRequest,
            user
        );
    };